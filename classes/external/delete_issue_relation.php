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
use external_multiple_structure;
use external_warnings;
use local_qtracker\issue;

/**
 * delete_issue_relation class
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_issue_relation extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'parentid' => new external_value(PARAM_INT, 'issue id of parent'),
                'childid' => new external_value(PARAM_INT, 'issue id of child')
            )
        );
    }

    /**
     * Supersedes issue passed as child under issue passed as parent
     *
     * @param int $parentid id of the parent issue to supersede a child issue
     * @param int $childid id of the child issue to be superseded by a parent issue
     *
     * @return array with status, the issuedata, and any warnings
     */
    public static function execute($parentid, $childid) {
        global $PAGE, $DB;

        $deleted = false;
        $warnings = array();

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(),
            array(
                'parentid' => (int) $parentid,
                'childid' => (int) $childid,
            )
        );

        if (!$DB->record_exists_select('local_qtracker_issue', 'id = :parentid',
            array(
                'parentid' => $params['parentid']
            )
        )) {
            throw new \moodle_exception('cannoteditissue', 'local_qtracker', '', $params['parentid']);
        }

        if (!$DB->record_exists_select('local_qtracker_issue', 'id = :childid',
            array(
                'childid' => $params['childid']
            )
        )) {
            throw new \moodle_exception('cannoteditissue', 'local_qtracker', '', $params['childid']);
        }


        $parent = issue::load($params['parentid']);
        $child = issue::load($params['childid']);

        // Context validation.
        $parentcontext = \context::instance_by_id($parent->get_contextid());
        self::validate_context($parentcontext);
        $childcontext = \context::instance_by_id($child->get_contextid());
        self::validate_context($childcontext);

        // Capability checking.
        issue_require_capability_on($parent->get_issue_obj(), 'edit');
        issue_require_capability_on($child->get_issue_obj(), 'edit');

        if (empty($warnings)) {
            $deleted = $parent->remove_child($child);
        }

        $result = array();
        $result['status'] = $deleted;
        $result['warnings'] = $warnings;

        return $result;
    }

     /**
     * Returns description of get_issues result value.
     *
     * @return external_description
     * @since Moodle 2.5
     */
    public static function execute_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }
}
