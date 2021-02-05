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
 * External (web service) function calls for deleting a question issue.
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


class delete_issue extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function delete_issue_parameters() {
        return new external_function_parameters(
            array(
                'issueid' => new external_value(PARAM_INT, 'issue id'),
            )
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function delete_issue($issueid) {
        global $USER, $DB;

        $deleted = false;
        $warnings = array();

        // Parameter validation.
        $params = self::validate_parameters(self::delete_issue_parameters(),
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
            throw new \moodle_exception('cannotdeleteissue', 'local_qtracker', '', $params['issueid']);
        }

        $issue = issue::load($params['issueid']);

        // Context validation.
        $context = \context::instance_by_id($issue->get_contextid());
        self::validate_context($context);

        // Capability checking.
        issue_require_capability_on($issue->get_issue_obj(), 'edit');

        if (empty($warnings)) {
            $deleted = $issue->delete();
        }

        $result = array();
        $result['status'] = $deleted;
        $result['issueid'] = $params['issueid'];
        $result['warnings'] = $warnings;

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function delete_issue_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'issueid' => new external_value(PARAM_INT, 'The id of the new issue'),
                'warnings' => new external_warnings()
            )
        );
    }
}
