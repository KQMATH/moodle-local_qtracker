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

require_once($CFG->libdir . "/externallib.php");
require_once($CFG ->libdir . '/questionlib.php');

use external_value;
use external_function_parameters;
use external_single_structure;
use external_warnings;
use local_qtracker\issue;

class getissue extends \external_api {

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
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_issue($issueid) {
        global $USER, $DB;

        $status = false;
        $issuedata = array();
        $warnings = array();

        //Parameter validation
        $params = self::validate_parameters(self::get_issue_parameters(),
            array(
                'issueid' => (int) $issueid,
            )
        );

        //Context validation
        // TODO: ensure proper validation....
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        if (!has_capability('local/qtracker:readissue', $context)) {
            throw new \moodle_exception('cannotgetissue', 'local_qtracker');
        }

        if (!$DB->record_exists_select('qtracker_issue', 'id = :issueid AND userid = :userid',
            array(
                'issueid' => $params['issueid'],
                'userid' => $USER->id
            )
        )) {
            throw new \moodle_exception('cannotgetissue', 'local_qtracker', '', $params['issueid']);
        }

        if (empty($warnings)) {
            $issue = issue::load($params['issueid']);

            $issuedata['id'] = $issue->get_id();
            $issuedata['title'] = $issue->get_title();
            $issuedata['description'] = $issue->get_description();
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
                'issue' => new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'The id of the issue'),
                            'title' => new external_value(PARAM_TEXT, 'The issue title.'),
                            'description' => new external_value(PARAM_TEXT, 'The issue description.'),
                            'questionid' => new external_value(PARAM_INT, 'The question id for this issue.'),
                            'questionusageid' => new external_value(PARAM_INT, 'The question usage id for this issue.'),
                            'slot' => new external_value(PARAM_INT, 'The issslot for the question for the issue.'),
                            'userid' => new external_value(PARAM_INT, 'The user id for the user who created the issue.'),
                            'timecreated' => new external_value(PARAM_INT, 'The time the issue was created.'),
                        )
                    ),
                'warnings' => new external_warnings()
            )
        );

    }
}
