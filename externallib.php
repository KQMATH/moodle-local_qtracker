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
                'questionid' => new external_value(PARAM_INT, 'question id'),
                'issuetitle' => new external_value(PARAM_TEXT, 'issue title'),
                'issuedescription' => new external_value(PARAM_TEXT, 'issue description'),
            )
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function new_issue($questionid, $issuetitle, $issuedescription) {
        global $USER, $DB;

        $added = false;
        $warnings = array();

        //Parameter validation
        $params = self::validate_parameters(self::new_issue_parameters(),
            array(
                'questionid' => (int) $questionid,
                'issuetitle' => $issuetitle,
                'issuedescription' => $issuedescription,
            )
        );

        //Context validation
        // TODO: ensure proper validation....
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('local/qtracker:createissue', $context)) {
            throw new moodle_exception('cannotcreateissue', 'local_qtracker');
        }

        // Check if question exists.
        $question = $DB->get_record('question', array('id' => $questionid));
        if ($question === false) {
            $warnings[] = array(
                            'item' => 'question',
                            'itemid' => $questionid,
                            'warningcode' => 'unknownquestionidnumber',
                            'message' => 'Unknown question ID ' . $questionid
                        );
        } else { // Insert new issue
            $dataobject = new \stdClass;
            $dataobject->questionid = $questionid;
            $dataobject->title = $issuetitle;
            $dataobject->description = $issuedescription;
            $dataobject->userid = $USER->id;
            $time = time();
            $dataobject->timecreated = $time;
            $DB->insert_record('qtracker_issue', $dataobject);
            $added = true;
        }

        $result = array();
        $result['status'] = $added;
        $result['warnings'] = $warnings;

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function new_issue_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }
}
