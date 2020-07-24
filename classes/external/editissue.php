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
require_once($CFG ->libdir . '/questionlib.php');

use external_value;
use external_function_parameters;
use moodle_exception;
use external_single_structure;
use external_warnings;
use local_qtracker\issue;


class editissue extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function edit_issue_parameters() {
        return new external_function_parameters(
            array(
                'issueid' => new external_value(PARAM_INT, 'issue id'),
                'issuetitle' => new external_value(PARAM_TEXT, 'issue title'),
                'issuedescription' => new external_value(PARAM_TEXT, 'issue description'),
            )
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function edit_issue($issueid, $issuetitle, $issuedescription) {
        global $USER, $DB;

        $added = false;
        $warnings = array();

        //Parameter validation
        $params = self::validate_parameters(self::edit_issue_parameters(),
            array(
                'issueid' => (int) $issueid,
                'issuetitle' => $issuetitle,
                'issuedescription' => $issuedescription,
            )
        );

        //Context validation
        // TODO: ensure proper validation....
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        if (!has_capability('local/qtracker:createissue', $context)) {
            throw new \moodle_exception('cannoteditissue', 'local_qtracker');
        }

        if (!$DB->record_exists_select('qtracker_issue', 'id = :issueid AND userid = :userid',
            array(
                'issueid' => $params['issueid'],
                'userid' => $USER->id
            )
        )) {
            throw new \moodle_exception('cannoteditissue', 'local_qtracker', '', $params['issueid']);
        }

        if (empty($params['issuetitle'])){
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
            $issue = issue::load($params['issueid']);
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
     * @return external_description
     */
    public static function edit_issue_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'issueid' => new external_value(PARAM_INT, 'The id of the new issue'),
                'warnings' => new external_warnings()
            )
        );
    }
}
