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
 * Plugin strings are defined here.
 *
 * @package     local_customnotifications
 * @category    string
 * @copyright   2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Custom reminder';
$string['manage'] = 'Manage Custom reminder';
$string['event_remindersent'] = 'Reminder sent';
$string['configuration'] = 'General plugin settings';
$string['configuration_help'] = 'General local Custom reminder configuration';
$string['footer'] = 'Message footer';
$string['footer_help'] = 'Message footer for signature or unsubscribe mail';

$string['courtdate_reminder'] = 'Court Date reminder';
$string['courtdate_reminder_help'] = 'Manage Court date reminder notifications';
$string['courtdate_profilefield'] = 'Select custom user profile field for court date';
$string['courtdate_profilefield_help'] = 'Select custom user profile field for court date - field must be date type';
$string['courtdate_fbefore'] = 'Send first message before of court date';
$string['courtdate_fbefore_help'] = 'Send first message before of court dat';
$string['courtdate_fmessage'] = 'Court date first notification message';
$string['courtdate_fmessage_help'] = 'Court date first notification message';
$string['courtdate_sbefore'] = 'Send second message before of court date';
$string['courtdate_sbefore_help'] = 'Send first message before of court date';
$string['courtdate_smessage'] = 'Court date second notification message';
$string['courtdate_smessage_help'] = 'Court date second notification message';
$string['courtdate_fsubject'] = 'First message subject';
$string['courtdate_fsubject_help'] = 'First message subject';
$string['courtdate_ssubject'] = 'Second message subject';
$string['courtdate_ssubject_help'] = 'Second message subject';
$string['courtdate_recipient'] = 'Send copy of courdate messages to';
$string['courtdate_recipient_help'] = 'Notify admin user about courtdate users';

$string['enroll_reminder'] = 'Enroll reminder';
$string['enroll_reminder_help'] = 'Manage Enroll reminder notifications';
$string['enroll_subject'] = 'Enroll reminder message subject';
$string['enroll_subject_help'] = 'Enroll reminder message subject';
$string['enroll_delayduration'] = 'Delay duration after account confirmation';
$string['enroll_delayduration_help'] = 'Delay duration for notification after user enrolment to course';
$string['enroll_message'] = 'Enrollment reminder message';
$string['enroll_message_help'] = 'Enrollment\payment reminder notification message';
$string['enroll_recipient'] = 'Send copy of enrollment reminder message to';
$string['enroll_recipient_help'] = 'Notify admin user about unenroled users';
$string['enroll_courses'] = 'Select course';
$string['enroll_courses_help'] = 'Select course for enrollment link in message';
$string['enroll_proceed'] = 'Proceed to enrol in to the course {$a}';

$string['confirmation_reminder'] = 'Confirmation reminder';
$string['confirmation_reminder_help'] = 'Manage Confirmation reminder notifications';
$string['confirmation_delayduration'] = 'Delay duration after account confirmation';
$string['confirmation_delayduration_help'] = 'Delay duration after account confirmation';
$string['confirmation_recipient'] = 'Notify admin user about uncorfimed account';
$string['confirmation_recipient_help'] = 'Notify admin user about uncorfimed account';
$string['confirmation_subject'] = 'User {$a->fullname} ({$a->email}) is not confirmed.';
$string['confirmation_message'] = 'The confirmation email was resended to user {$a->fullname}, {$a->email}, 
                                   which has not yet confirmed his account on moodle.';