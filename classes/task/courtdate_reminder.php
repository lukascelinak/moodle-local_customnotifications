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
 * The Custom notifications Courtdate reminder task class.
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
class courtdate_reminder extends \core\task\scheduled_task
{
    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('courtdate_reminder', 'local_customnotifications');
    }

    /**
     * Execute the task.
     */
    public function execute()
    {
        global $DB,$CFG;
        $settings=get_config('local_customnotifications');
        $courses=$DB->get_records('course',array('visible'=>1));
        foreach ($courses as $course) {
            $context= \context_course::instance($course->id);
            list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

            // First notification courtdate_fbefore days before court date
            $sqlfirst = "SELECT u.* FROM {user} u 
                    LEFT JOIN {user_enrolments} ue ON ue.userid = u.id 
                    LEFT JOIN {enrol} e ON e.id = ue.enrolid 
                    LEFT JOIN {logstore_standard_log} l ON l.relateduserid = ue.userid 
                               AND l.other LIKE \"%courtdate_reminder_first%\"
                     JOIN (
                            SELECT DISTINCT ra.userid
                            FROM {role_assignments} ra
                            WHERE ra.roleid IN ($CFG->gradebookroles)
                            AND ra.contextid {$relatedctxsql}
                       ) rainner ON rainner.userid = u.id 

                    WHERE e.courseid=:courseid AND ue.status = 0 AND l.id IS NULL 
                      AND UNIX_TIMESTAMP()  > 
                      (SELECT uid.data 
                       FROM {user_info_data} uid 
                       WHERE uid.fieldid=:fieldid AND uid.userid=u.id)-:timebefore ";

            $firstparams = ['courseid' => $course->id,'fieldid'=>$settings->fieldid,'timebefore'=>$settings->courtdate_fbefore];
            $paramsfirst = array_merge($firstparams, $relatedctxparams);
            $usersfirst = $DB->get_records_sql($sqlfirst, $paramsfirst);

            foreach ($usersfirst as $user) {
                $message= new message_template($user,
                    new \lang_string('courtdate_reminder','local_customnotifications',$user),
                    $settings->courtdate_fmessage, $settings->footer);

                $message->set_button(new \moodle_url('/course/view.php',array('id'=>$course->id)),$course->fullname);
                $htmlmail=$message->out();
                $plainmail= strip_tags($htmlmail);

                email_to_user($user, null, $message->get_subject(), $plainmail, $htmlmail);

                $event = reminder_sent::create(array('objectid' => $user->id,
                    'context' => \context_user::instance($user->id), 'relateduserid' => $user->id, 'other' => "courtdate_reminder_first"));
                $event->trigger();
            }

            // Second notification courtdate_fbefore days before court date
            $sqlfirst = "SELECT u.* FROM {user} u 
                    LEFT JOIN {user_enrolments} ue ON ue.userid = u.id 
                    LEFT JOIN {enrol} e ON e.id = ue.enrolid 
                    LEFT JOIN {logstore_standard_log} l ON l.relateduserid = ue.userid 
                               AND l.other LIKE \"%courtdate_reminder_first%\"
                     JOIN (
                            SELECT DISTINCT ra.userid
                            FROM {role_assignments} ra
                            WHERE ra.roleid IN ($CFG->gradebookroles)
                            AND ra.contextid {$relatedctxsql}
                       ) rainner ON rainner.userid = u.id 

                    WHERE e.courseid=:courseid AND ue.status = 0 AND l.id IS NULL 
                      AND UNIX_TIMESTAMP() > 
                          (SELECT uid.data 
                          FROM {user_info_data} uid 
                          WHERE uid.fieldid=:fieldid AND uid.userid=u.id)-:timebefore ";

            $secparams = ['courseid' => $course->id,'fieldid'=>$settings->fieldid,'timebefore'=>$settings->courtdate_sbefore];
            $secparams = array_merge($secparams, $relatedctxparams);
            $userssecond = $DB->get_records_sql($sqlfirst, $secparams);

            foreach ($userssecond as $user) {
                $smessage= new message_template($user,
                    new \lang_string('courtdate_reminder','local_customnotifications',$user),
                    $settings->courtdate_smessage, $settings->footer);

                $smessage->set_button(new \moodle_url('/course/view.php',array('id'=>$course->id)),$course->fullname);
                $htmlmail=$smessage->out();
                $plainmail= strip_tags($htmlmail);

                email_to_user($user, null, $smessage->get_subject(), $plainmail, $htmlmail);

                $event = reminder_sent::create(array('objectid' => $user->id,
                    'context' => \context_user::instance($user->id), 'relateduserid' => $user->id, 'other' => "courtdate_reminder_second"));
                $event->trigger();
            }
        }
    }

}

