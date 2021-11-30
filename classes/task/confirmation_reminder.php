<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The Custom notifications Confirmation reminder task class.
 *
 * @package    local_customnotifications
 * @category   task
 * @copyright  Lukas Celinak, Edumood, Slovakia
 * @auther     2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_customnotifications\task;

defined('MOODLE_INTERNAL') || die();

use local_customnotifications\event\reminder_sent;
use local_customnotifications\message_template;
/**
 * An example of a scheduled task.
 */
class confirmation_reminder extends \core\task\scheduled_task
{
    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('confirmation_reminder', 'local_customnotifications');
    }

    /**
     * Execute the task.
     */
    public function execute()
    {
        global $DB, $CFG;
        // First notification after N minutes
        $settings=get_config('local_customnotifications');
        $supportuser = \core_user::get_support_user();
        $sql = "SELECT u.* FROM {user} u 
                    LEFT JOIN {logstore_standard_log} l ON l.relateduserid = u.id 
                               AND l.component = \"local_customnotifications\" 
                               AND l.other LIKE \"%confirmation_reminder%\" 
                    WHERE u.auth LIKE 'email' AND u.suspended = 0 AND u.deleted = 0 AND u.confirmed = 0
                          AND l.id IS NULL AND UNIX_TIMESTAMP() > u.timecreated+:confirmation_delayduration ";

        $params = ['confirmation_delayduration' => $settings->confirmation_delayduration];
        $users = $DB->get_records_sql($sql, $params);

        foreach ($users as $user) {
            $user->fullname=fullname($user);
            if ($settings->confirmation_recipient>1) {
                $message= new message_template($user,
                    new \lang_string('confirmation_subject','local_customnotifications',$user),
                    new \lang_string('confirmation_message','local_customnotifications',$user),
                    '',$settings->footer);

                $message->set_button(new \moodle_url('/user/profile.php',array('id'=>$user->id)),fullname($user));
                $htmlmail=$message->out();
                $plainmail= strip_tags($htmlmail);
                $copyuser = $DB->get_record('user', array('id' => $settings->confirmation_recipient));
                email_to_user($copyuser, $supportuser,
                    $message->get_subject(),
                    $plainmail, $htmlmail);
            }
            $user = get_complete_user_data('email', $user->email, null, true);
            send_confirmation_email($user);

            $event = reminder_sent::create(array('objectid' => $user->id,
                'context' => \context_user::instance($user->id), 'relateduserid' => $user->id, 'other' => "confirmation_reminder"));
            $event->trigger();
        }
    }
}

