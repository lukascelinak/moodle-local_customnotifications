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
 * Plugin administration pages are defined here.
 *
 * @package    local_customnotifications
 * @category   admin
 * @copyright  Lukas Celinak, Edumood, Slovakia
 * @auther     2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_customnotifications_settings',
        new lang_string('pluginname', 'local_customnotifications')));
    $settingspage = new admin_settingpage('managelocalcustomnotifications',
        new lang_string('manage', 'local_customnotifications'));

    if ($ADMIN->fulltree) {
        /**
         *  Select arrays preparation
         */
        $users = $DB->get_records('user');
        $userselect = [];
        foreach ($users as $user) {
            $userselect[$user->id] = fullname($user);
        }
        
        $extrafields = $DB->get_records('user_info_field');
        $extrafieldsarray = [];
        foreach ($extrafields as $field) {
            $extrafieldsarray[$field->id] = $field->name;
        }

        $courses = $DB->get_records('course',array('visible'=>1));
        $coursesselect = [];
        foreach ($courses as $course) {
            $coursesselect[$course->id] = $course->fullname;
        }


        /**
         *  Courtdate reminder task configuration
         */
        $settingspage->add(new admin_setting_heading('local_customnotifications/courtdate_reminder',
            new lang_string('courtdate_reminder', 'local_customnotifications'),
            new lang_string('courtdate_reminder', 'local_customnotifications')));

        $settingspage->add(new admin_setting_configselect('local_customnotifications/fieldid',
            new lang_string('courtdate_profilefield', 'local_customnotifications'),
            new lang_string('courtdate_profilefield_help', 'local_customnotifications'), null,
            $extrafieldsarray));

        $settingspage->add(new admin_setting_configduration('local_customnotifications/courtdate_fbefore',
            new lang_string('courtdate_fbefore', 'local_customnotifications'),
            new lang_string('courtdate_fbefore_help', 'local_customnotifications'), 172800));

        $settingspage->add(new admin_setting_configtext('local_customnotifications/courtdate_fsubject',
            new lang_string('courtdate_fsubject', 'local_customnotifications'),
            new lang_string('courtdate_fsubject_help', 'local_customnotifications'), ''));

        $settingspage->add(new admin_setting_confightmleditor('local_customnotifications/courtdate_fmessage',
            new lang_string('courtdate_fmessage', 'local_customnotifications'),
            new lang_string('courtdate_fmessage_help', 'local_customnotifications'), ''));

        $settingspage->add(new admin_setting_configduration('local_customnotifications/courtdate_sbefore',
            new lang_string('courtdate_sbefore', 'local_customnotifications'),
            new lang_string('courtdate_sbefore_help', 'local_customnotifications'), 86400));

        $settingspage->add(new admin_setting_configtext('local_customnotifications/courtdate_ssubject',
            new lang_string('courtdate_ssubject', 'local_customnotifications'),
            new lang_string('courtdate_ssubject_help', 'local_customnotifications'), ''));

        $settingspage->add(new admin_setting_confightmleditor('local_customnotifications/courtdate_smessage',
            new lang_string('courtdate_smessage', 'local_customnotifications'),
            new lang_string('courtdate_smessage_help', 'local_customnotifications'), ''));

        $settingspage->add(new admin_setting_configselect_autocomplete('local_customnotifications/courtdate_recipient',
            new lang_string('courtdate_recipient', 'local_customnotifications'),
            new lang_string('courtdate_recipient_help', 'local_customnotifications'), 2,
            $userselect));

        /**
         *  Enroll/payment reminder task configuration
         */
        $settingspage->add(new admin_setting_heading('local_customnotifications/enroll_reminder',
            new lang_string('enroll_reminder', 'local_customnotifications'),
            new lang_string('enroll_reminder_help', 'local_customnotifications')));

        $settingspage->add(new admin_setting_configselect_autocomplete('local_customnotifications/enroll_courses',
            new lang_string('enroll_courses', 'local_customnotifications'),
            new lang_string('enroll_courses_help', 'local_customnotifications'), null,
            $coursesselect));

        $settingspage->add(new admin_setting_configduration('local_customnotifications/enroll_delayduration',
            new lang_string('enroll_delayduration', 'local_customnotifications'),
            new lang_string('enroll_delayduration_help', 'local_customnotifications'), 1200, 60));

        $settingspage->add(new admin_setting_configtext('local_customnotifications/enroll_subject',
            new lang_string('enroll_subject', 'local_customnotifications'),
            new lang_string('enroll_subject_help', 'local_customnotifications'), ''));

        $settingspage->add(new admin_setting_confightmleditor('local_customnotifications/enroll_message',
            new lang_string('enroll_message', 'local_customnotifications'),
            new lang_string('enroll_message_help', 'local_customnotifications'), ''));

        $settingspage->add(new admin_setting_configselect_autocomplete('local_customnotifications/enroll_recipient',
            new lang_string('enroll_recipient', 'local_customnotifications'),
            new lang_string('enroll_recipient_help', 'local_customnotifications'), 2,
            $userselect));

        /**
         *  Confirmation reminder task configuration
         */
        $settingspage->add(new admin_setting_heading('local_customnotifications/confirmation_reminder',
            new lang_string('confirmation_reminder', 'local_customnotifications'),
            new lang_string('confirmation_reminder_help', 'local_customnotifications')));

        $settingspage->add(new admin_setting_configduration('local_customnotifications/enroll_delayduration',
            new lang_string('enroll_delayduration', 'local_customnotifications'),
            new lang_string('enroll_delayduration_help', 'local_customnotifications'), 1200, 60));

        $settingspage->add(new admin_setting_configduration('local_customnotifications/confirmation_delayduration',
            new lang_string('confirmation_delayduration', 'local_customnotifications'),
            new lang_string('confirmation_delayduration_help', 'local_customnotifications'), 1200, 60));

        $settingspage->add(new admin_setting_configselect_autocomplete('local_customnotifications/confirmation_recipient',
            new lang_string('confirmation_recipient', 'local_customnotifications'),
            new lang_string('confirmation_recipient_help', 'local_customnotifications'), 2,
            $userselect));

        /**
         *  Plugin customnotifications configuration
         */
        $settingspage->add(new admin_setting_heading('local_customnotifications/footerheading',
            new lang_string('configuration', 'local_customnotifications'),
            new lang_string('configuration_help', 'local_customnotifications')));

        $settingspage->add(new admin_setting_confightmleditor('local_customnotifications/footer',
            new lang_string('footer', 'local_customnotifications'),
            new lang_string('footer_help', 'local_customnotifications'), ''));


    }
    $ADMIN->add('localplugins', $settingspage);
}
