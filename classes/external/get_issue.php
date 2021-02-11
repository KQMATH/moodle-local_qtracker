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
 * External (web service) function calls for retrieving a question issue.
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
use external_single_structure;
use external_warnings;
use local_qtracker\issue;
use local_qtracker\external\helper;

/**
 * get_issue class
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_issue extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_issue_parameters() {
        return new external_function_parameters(
            array(
                'issueid' => new external_value(PARAM_INT, 'issue id')
            )
        );
    }

    /**
     * Returns issue with the id $issueid
     *
     * @param int $issueid id of the issue to be returned
     *
     * @return array with status, the issuedata, and any warnings
     */
    public static function get_issue($issueid) {
        global $USER, $DB;

        $status = false;
        $issuedata = array();
        $warnings = array();

        // Parameter validation.
        $params = self::validate_parameters(self::get_issue_parameters(),
            array(
                'issueid' => (int) $issueid,
            )
        );

        if (!$DB->record_exists_select('qtracker_issue', 'id = :issueid AND userid = :userid',
            array(
                'issueid' => $params['issueid'],
                'userid' => $USER->id
            )
        )) {
            throw new \moodle_exception('cannotgetissue', 'local_qtracker', '', $params['issueid']);
        }

        $issue = issue::load($params['issueid']);

        // Context validation.
        $context = \context::instance_by_id($issue->get_contextid());
        self::validate_context($context);

        // Capability checking.
        issue_require_capability_on($issue->get_issue_obj(), 'view');

        if (empty($warnings)) {
            $issuedata['id'] = $issue->get_id();
            $issuedata['title'] = $issue->get_title();
            $issuedata['description'] = $issue->get_description();
            $issuedata['state'] = $issue->get_state();
            $issuedata['questionid'] = $issue->get_questionid();
            $issuedata['questionusageid'] = $issue->get_qubaid();
            $issuedata['slot'] = $issue->get_slot();
            $issuedata['userid'] = $issue->get_userid();
            $issuedata['timecreated'] = $issue->get_timecreated();

            $status = true;
        }

        $result = array();
        $result['status'] = $status;
        $result['issue'] = $issuedata;
        $result['warnings'] = $warnings;

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_issue_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'issue' => helper::issue_description(),
                'warnings' => new external_warnings()
            )
        );

    }
}
