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
 * The Custom reminder event class.
 *
 * @package    local_customnotifications
 * @category   admin
 * @copyright  Lukas Celinak, Edumood s.r.o., Slovakia
 * @auther     2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_customnotifications\event;

defined('MOODLE_INTERNAL') || die();
class  reminder_sent extends \core\event\base  {
    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'user';
        $this->data['crud'] = 'u'; // Usually we perform update db queries so 'u' its ok!
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_remindersent', 'local_customnotifications');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        return "Notification '{$this->other}' was sent to user with id '{$this->relateduserid}' ";
    }
}