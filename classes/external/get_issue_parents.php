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
 * get_issue class
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_issue_parents extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'issueid' => new external_value(PARAM_INT, 'issue id')
            )
        );
    }

    /**
     * Returns parents of issue with the id $issueid
     *
     * @param int $issueid id of the issue to be returned
     *
     * @return array with status, the issuedata, and any warnings
     */
    public static function execute($issueid) {
        global $PAGE, $DB;

        $issue = array();
        $parents = array();
        $warnings = array();

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(),
            array(
                'issueid' => (int) $issueid,
            )
        );

        if (!$DB->record_exists_select('local_qtracker_issue', 'id = :issueid',
            array(
                'issueid' => $params['issueid']
            )
        )) {
            throw new \moodle_exception('cannotgetissue', 'local_qtracker', '', $params['issueid']);
        }


        $issue = issue::load($params['issueid']);

        // Context validation.
        $context = \context::instance_by_id($issue->get_contextid());
        self::validate_context($context);

        // Capability checking.
        local_qtracker_issue_require_capability_on($issue->get_issue_obj(), 'view');


        $parents = $issue->get_parents();

        $returnedparents = array();
        foreach ($parents as $parent) {
            // Context validation.

            $context = \context::instance_by_id($parent->get_contextid());
            self::validate_context($context);

            // Capability checking.
            local_qtracker_issue_require_capability_on($parent->get_id(), 'view');

            $renderer = $PAGE->get_renderer('core');
            $exporter = new issue_exporter($parent->get_issue_obj(), ['context' => $context]);
            $parentdetails = $exporter->export($renderer);
            // Return the issue only if all the searched fields are returned.
            // Otherwise it means that the $issue was not allowed to search the returned issue.
            if (!empty($parentdetails)) {
                $validparent = true;

                if ($validparent) {
                    $returnedparents[] = $parentdetails;
                }
            }
        }

        return array('parents' => $returnedparents, 'warnings' => $warnings);

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
                'parents' => new external_multiple_structure(
                    issue_exporter::get_read_structure()
                ),
                'warnings' => new external_warnings()
            )
        );
    }
}
