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
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class local_qtracker_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function new_issue_parameters() {
        return new external_function_parameters(
            array(
                'questionid' => new external_value(PARAM_INT, 'question id', VALUE_REQUIRED),
                'issuetitle' => new external_value(PARAM_TEXT, 'issue title', VALUE_REQUIRED),
                'issuedescription' => new external_value(PARAM_TEXT, 'issue description', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function new_issue($questionid, $issuetitle, $issuedescription) {
        global $USER;

        //Parameter validation
        $params = self::validate_parameters(self::new_issue_parameters(),
            array(
                'questionid' => $questionid,
                'issuetitle' => $issuetitle,
                'issuedescription' => $issuedescription,
            )
        );

        //Context validation
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('local/qtracker:createissue', $context)) {
            throw new moodle_exception('cannotcreateissue', 'local_qtracker');
        }

        return $params;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function new_issue_returns() {
        return new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }
}
