<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Event observers supported by this module.
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\event;

use local_qtracker\issue;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers supported by this module.
 *
 * @package    local_qtracker
 * @copyright  2021 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_deleted_observer {
    /**
     * Change all issues contextid's to the parent context when course module is deleted.
     *
     * @param \core\event\course_module_deleted $event
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        global $DB;
        $coursecontext = \context_course::instance($event->courseid);
        $records = $DB->get_records('local_qtracker_issue', ['contextid' => $event->contextid], '', 'id');
        foreach ($records as $record) {
            $issue = issue::load($record->id);
            $issue->set_contextid($coursecontext->id);
        }
    }
}
