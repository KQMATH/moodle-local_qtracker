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
 * External (web service) function calls for editing a question issue.
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
 * edit_issue class
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_issue extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'issueid' => new external_value(PARAM_INT, 'issue id'),
                'issuetitle' => new external_value(PARAM_TEXT, 'issue title'),
                'issuedescription' => new external_value(PARAM_RAW, 'issue description'),
            )
        );
    }

    /**
     * Edits issue
     *
     * @param int $issueid id of the issue to be edited
     * @param string $issuetitle new issue title
     * @param string $issuedescription new issue description
     *
     * @return array with status, issueid and any warnings
     */
    public static function execute($issueid, $issuetitle, $issuedescription) {
        global $USER, $DB;

        $added = false;
        $warnings = array();

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(),
            array(
                'issueid' => (int) $issueid,
                'issuetitle' => $issuetitle,
                'issuedescription' => $issuedescription,
            )
        );

        if (!$DB->record_exists_select('local_qtracker_issue', 'id = :issueid',
            array(
                'issueid' => $params['issueid']
            )
        )) {
            throw new \moodle_exception('cannoteditissue', 'local_qtracker', '', $params['issueid']);
        }

        $issue = issue::load($params['issueid']);

        // Context validation.
        $context = \context::instance_by_id($issue->get_contextid());
        self::validate_context($context);

        // Capability checking.
        local_qtracker_issue_require_capability_on($issue->get_issue_obj(), 'edit');

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
        if (empty($warnings)) {
            $issue->set_title($params['issuetitle']);
            $issue->set_description($params['issuedescription']);
            $added = true;
        }

        $result = array();
        $result['status'] = $added;
        $result['issueid'] = $params['issueid'];
        $result['warnings'] = $warnings;

        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'issueid' => new external_value(PARAM_INT, 'The id of the new issue'),
                'warnings' => new external_warnings()
            )
        );
    }
}
