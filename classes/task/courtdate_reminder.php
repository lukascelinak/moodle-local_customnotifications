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
 * @category   task
 * @copyright  Lukas Celinak, Edumood, Slovakia
 * @auther     2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_customnotifications\task;

defined('MOODLE_INTERNAL') || die();

use local_customnotifications\event\reminder_sent;
use local_customnotifications\message_template;
require_once("$CFG->dirroot/user/profile/lib.php");

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
        $supportuser = \core_user::get_support_user();
        foreach ($courses as $course) {
            $context= \context_course::instance($course->id);
            list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

            // First notification courtdate_fbefore days before court date
            $sqlfirst = "SELECT u.* FROM {user} u 
                    LEFT JOIN {user_enrolments} ue ON ue.userid = u.id 
                    LEFT JOIN {enrol} e ON e.id = ue.enrolid 
                    LEFT JOIN {course_completions} cc ON cc.userid = u.id AND cc.course=:courseid2
                    LEFT JOIN {logstore_standard_log} l ON l.relateduserid = ue.userid 
                               AND l.other LIKE CONCAT('\"',\"courtdate_reminder_first_\",(SELECT uid.data 
                       FROM {user_info_data} uid 
                       WHERE uid.fieldid=:fieldid4 AND uid.userid=u.id),'\"')
                     JOIN (
                            SELECT DISTINCT ra.userid
                            FROM {role_assignments} ra
                            WHERE ra.roleid IN ($CFG->gradebookroles)
                            AND ra.contextid {$relatedctxsql}
                       ) rainner ON rainner.userid = u.id 

                    WHERE ue.id IS NOT NULL 
                      AND e.courseid=:courseid 
                      AND ue.status = 0 
                      AND l.id IS NULL 
                      AND cc.timecompleted IS NULL 
                      AND FROM_UNIXTIME(UNIX_TIMESTAMP(),\"%Y-%m-%d\") = 
                      FROM_UNIXTIME(UNIX_TIMESTAMP(STR_TO_DATE(
                    (SELECT uid.data 
                       FROM {user_info_data} uid 
                       WHERE uid.fieldid=:fieldid1 AND uid.userid=u.id),\"%m-%d-%Y\"
                          ))-:timebefore,\"%Y-%m-%d\") ";

            $firstparams = ['courseid' => $course->id,
                'courseid2' => $course->id,
                'fieldid1'=>$settings->fieldid,
                'fieldid2'=>$settings->fieldid,
                'fieldid3'=>$settings->fieldid,
                'fieldid4'=>$settings->fieldid,
                'timebefore'=>$settings->courtdate_fbefore];
            $paramsfirst = array_merge($firstparams, $relatedctxparams);
            $usersfirst = $DB->get_records_sql($sqlfirst, $paramsfirst);
            foreach ($usersfirst as $user) {
                $courtdate=$DB->get_field('user_info_data','data',array('fieldid'=>$settings->fieldid,'userid'=>$user->id));
                $user->courtdate=is_number($courtdate)?userdate($courtdate,'%d.%m.%Y'):$courtdate;
                $user->fullname=fullname($user);
                if ($settings->courtdate_recipient>1) {
                    $messageadm= new message_template($user,
                        new \lang_string('courtdate_first_subject','local_customnotifications',$user),
                        new \lang_string('courtdate_first_message','local_customnotifications',$user), '',$settings->footer);

                    $messageadm->set_button(new \moodle_url('/user/profile.php',array('id'=>$user->id)),fullname($user));
                    $htmlmail=$messageadm->out();
                    $plainmail= strip_tags($htmlmail);
                    $copyuser = $DB->get_record('user', array('id' => $settings->courtdate_recipient));
                    email_to_user($copyuser, $supportuser,
                        $messageadm->get_subject(),
                        $plainmail, $htmlmail);
                }

                $message= new message_template($user,
                    $settings->courtdate_fsubject,
                    $settings->courtdate_fmessage, $settings->footer);

                $message->set_button(new \moodle_url('/course/view.php',array('id'=>$course->id)),$course->fullname);
                $htmlmail=$message->out();
                $plainmail= strip_tags($htmlmail);
                email_to_user($user, $supportuser, $message->get_subject(), $plainmail, $htmlmail);
                $event = reminder_sent::create(array('objectid' => $user->id,
                    'context' => \context_user::instance($user->id),
                    'relateduserid' => $user->id,
                    'other' => "courtdate_reminder_first_".$courtdate));
                $event->trigger();
            }

            // Second notification courtdate_fbefore days before court date
            $sqlsecond = "SELECT u.* FROM {user} u 
                    LEFT JOIN {user_enrolments} ue ON ue.userid = u.id 
                    LEFT JOIN {enrol} e ON e.id = ue.enrolid 
                    LEFT JOIN {course_completions} cc ON cc.userid = u.id AND cc.course=:courseid2
                    LEFT JOIN {logstore_standard_log} l ON l.relateduserid = ue.userid 
                               AND l.other LIKE CONCAT('\"',\"courtdate_reminder_second_\",
                                (SELECT uid.data 
                                 FROM {user_info_data} uid 
                                 WHERE uid.fieldid=:fieldid4 AND uid.userid=u.id),'\"')
                     JOIN (
                            SELECT DISTINCT ra.userid
                            FROM {role_assignments} ra
                            WHERE ra.roleid IN ($CFG->gradebookroles)
                            AND ra.contextid {$relatedctxsql}
                       ) rainner ON rainner.userid = u.id 

                    WHERE e.courseid=:courseid 
                      AND ue.status = 0 
                      AND l.id IS NULL 
                      AND cc.timecompleted IS NULL
                      AND FROM_UNIXTIME(UNIX_TIMESTAMP(),\"%Y-%m-%d\") = 
                    FROM_UNIXTIME(UNIX_TIMESTAMP(STR_TO_DATE(
                    (SELECT uid.data 
                       FROM {user_info_data} uid 
                       WHERE uid.fieldid=:fieldid1 AND uid.userid=u.id),\"%m-%d-%Y\"
                          ))-:timebefore,\"%Y-%m-%d\") ";

            $secparams = ['courseid' => $course->id,
                'courseid2' => $course->id,
                'fieldid1'=>$settings->fieldid,
                'fieldid2'=>$settings->fieldid,
                'fieldid4'=>$settings->fieldid,
                'timebefore'=>$settings->courtdate_sbefore];
            $secparams = array_merge($secparams, $relatedctxparams);
            $userssecond = $DB->get_records_sql($sqlsecond, $secparams);
            foreach ($userssecond as $user) {
                $courtdate=$DB->get_field('user_info_data','data',array('fieldid'=>$settings->fieldid,'userid'=>$user->id));
                $user->courtdate=is_number($courtdate)?userdate($courtdate,'%d.%m.%Y'):$courtdate;
                $user->fullname=fullname($user);
                if ($settings->courtdate_recipient>1) {
                    $messageadm= new message_template($user,
                        new \lang_string('courtdate_second_subject','local_customnotifications',$user),
                        new \lang_string('courtdate_second_message','local_customnotifications',$user),
                        '',
                        $settings->footer);

                    $messageadm->set_button(new \moodle_url('/user/profile.php',array('id'=>$user->id)),fullname($user));
                    $htmlmail=$messageadm->out();
                    $plainmail= strip_tags($htmlmail);
                    $copyuser = $DB->get_record('user', array('id' => $settings->courtdate_recipient));
                    email_to_user($copyuser, $supportuser,
                        $messageadm->get_subject(),
                        $plainmail, $htmlmail);
                }

                $smessage= new message_template($user,
                    $settings->courtdate_ssubject,
                    $settings->courtdate_smessage,
                    '',
                    $settings->footer);

                $smessage->set_button(new \moodle_url('/course/view.php',array('id'=>$course->id)),$course->fullname);
                $htmlmail=$smessage->out();
                $plainmail= strip_tags($htmlmail);
                email_to_user($user, $supportuser, $smessage->get_subject(), $plainmail, $htmlmail);
                $event = reminder_sent::create(array('objectid' => $user->id,
                    'context' => \context_user::instance($user->id),
                    'relateduserid' => $user->id,
                    'other' => "courtdate_reminder_second_".$courtdate));
                $event->trigger();
            }
        }
    }

}

