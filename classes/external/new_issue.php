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
 * External (web service) function calls for creating a new question issue.
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/local/qtracker/lib.php');

use external_value;
use external_function_parameters;
use moodle_exception;
use external_single_structure;
use external_warnings;
use local_qtracker\issue;

/**
 * new_issue class
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class new_issue extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function new_issue_parameters() {
        return new external_function_parameters(
            array(
                'qubaid' => new external_value(PARAM_INT, 'question usage id'),
                'slot' => new external_value(PARAM_INT, 'slot'),
                'contextid' => new external_value(PARAM_INT, 'issue context'),
                'issuetitle' => new external_value(PARAM_TEXT, 'issue title'),
                'issuedescription' => new external_value(PARAM_TEXT, 'issue description'),
            )
        );
    }

    /**
     * Creates new issue
     *
     * @param $issuetitle new issues title
     * @param $issuedescription new issues description
     * @param $contextid
     * @param $qubaid
     * @param $slot
     *
     * @return array with status, issueid and any warnings
     */
    public static function new_issue($qubaid, $slot, $contextid, $issuetitle, $issuedescription) {
        global $USER, $DB;

        $added = false;
        $warnings = array();

        // Parameter validation.
        $params = self::validate_parameters(self::new_issue_parameters(),
            array(
                'qubaid' => (int) $qubaid,
                'slot' => (int) $slot,
                'contextid' => (int) $contextid,
                'issuetitle' => $issuetitle,
                'issuedescription' => $issuedescription,
            )
        );

        // Context validation.
        // TODO: ensure proper validation....
        $context = \context::instance_by_id($params['contextid']);
        self::validate_context($context);

        // Capability checking.
        if (!has_capability('local/qtracker:addissue', $context)) {
            throw new moodle_exception('cannotcreateissue', 'local_qtracker');
        }

        if (empty($params['issuetitle'])) {
            $warnings[] = array(
                'item' => 'issuetitle',
                'itemid' => 0,
                'warningcode' => 'fielderror',
                'message' => 'Empty issue title.'
            );
        }

        if (empty($params['issuedescription'])) {
            $warnings[] = array(
                'item' => 'issuedescription',
                'itemid' => 0,
                'warningcode' => 'fielderror',
                'message' => 'Empty issue description.',
            );
        }

        $quba = \question_engine::load_questions_usage_by_activity($params['qubaid']);
        $question = $quba->get_question($params['slot']);

        $issueid = 0;

        if (empty($warnings)) {
            $issue = issue::create(
                $params['issuetitle'],
                $params['issuedescription'],
                $question,
                $params['contextid'],
                $quba,
                $params['slot']
            );
            $issueid = $issue->get_id();
            $added = true;
        }

        $result = array();
        $result['status'] = $added;
        $result['issueid'] = $issueid;
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
                'issueid' => new external_value(PARAM_INT, 'The id of the new issue'),
                'warnings' => new external_warnings()
            )
        );
    }
}
