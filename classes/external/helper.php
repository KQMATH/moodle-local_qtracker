<?php

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
 * External (web service) function calls for retrieving a question issue.
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\external;

defined('MOODLE_INTERNAL') || die();

use external_value;
use external_single_structure;

class helper {
    /**
     * Create issue return value description.
     *
     * @param array $additionalfields some additional field
     * @return single_structure_description
     */
    public static function issue_description($additionalfields = array()) {
        $issuefields = array(
            'id' => new external_value(PARAM_INT, 'The id of the issue'),
            'title' => new external_value(PARAM_TEXT, 'The issue title.'),
            'description' => new external_value(PARAM_TEXT, 'The issue description.'),
            'state' => new external_value(PARAM_TEXT, 'The issue state.'),
            'questionid' => new external_value(PARAM_INT, 'The question id for this issue.'),
            'questionusageid' => new external_value(PARAM_INT, 'The question usage id for this issue.'),
            'slot' => new external_value(PARAM_INT, 'The issslot for the question for the issue.'),
            'userid' => new external_value(PARAM_INT, 'The user id for the user who created the issue.'),
            'timecreated' => new external_value(PARAM_INT, 'The time the issue was created.'),
        );
        if (!empty($additionalfields)) {
            $issuefields = array_merge($issuefields, $additionalfields);
        }
        return new external_single_structure($issuefields);
    }
}
