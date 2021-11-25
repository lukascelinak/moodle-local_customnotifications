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
 * The Custom notifications Enroll reminder task class.
 *
 * @package    local_customnotifications
 * @category   admin
 * @copyright  Lukas Celinak, Edumood s.r.o., Slovakia
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
class enroll_reminder extends \core\task\scheduled_task
{
    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('enroll_reminder', 'local_customnotifications');
    }

    /**
     * Execute the task.
     */
    public function execute()
    {
        global $DB, $CFG;
        // First notification after N minutes
        $settings=get_config('local_customnotifications');
        $sql = "SELECT u.* FROM {user} u 
                    LEFT JOIN {user_enrolments} ue ON u.id = ue.userid 
                    LEFT JOIN {logstore_standard_log} l ON l.relateduserid = u.id 
                               AND l.component = \"local_customnotifications\" 
                               AND l.other LIKE \"%enroll_reminder%\" 
                    WHERE u.auth LIKE 'email' 
                          AND u.suspended = 0 
                          AND u.deleted = 0 
                          AND u.confirmed = 1 
                          AND ue.id IS NULL
                          AND l.id IS NULL 
                          AND UNIX_TIMESTAMP() > u.timemodified+:enroll_delayduration ";

        $params = ['enroll_delayduration' => $settings->enroll_delayduration];
        $users = $DB->get_records_sql($sql, $params);

        foreach ($users as $user) {
            $user->fullname=fullname($user);
            if ($settings->enroll_recipient>1) {
                $message= new message_template($user,
                    new \lang_string('enroll_subject','local_customnotifications',$user),
                    new \lang_string('enroll_subject','local_customnotifications',$user));

                $message->set_button(new \moodle_url('/user/profile.php',array('id'=>$user->id)),fullname($user));
                $htmlmail=$message->out();
                $plainmail= strip_tags($htmlmail);
                $copyuser = $DB->get_record('user', array('id' => $settings->confirmation_recipient));
                email_to_user($copyuser, null,
                    new \lang_string('enroll_subject','local_customnotifications',$user),
                    $plainmail, $htmlmail);
            }
            $message= new message_template($user,
                new \lang_string('enroll_reminder','local_customnotifications',$user),
                $settings->courtdate_smessage, $settings->footer);
            $course = $DB->get_record('course',array('id'=>$settings->enroll_courses));

            $message->set_button(new \moodle_url('/course/view.php',array('id'=>$course->id)),get_string('enroll_proceed','local_customnotifications',$course->fullname));
            $htmlmail=$message->out();
            $plainmail= strip_tags($htmlmail);

            email_to_user($user, null, $message->get_subject(), $plainmail, $htmlmail);

            $event = reminder_sent::create(array('objectid' => $user->id,
                'context' => \context_user::instance($user->id), 'relateduserid' => $user->id, 'other' => "enroll_reminder"));
            $event->trigger();
        }

    }


}

