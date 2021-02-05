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
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/local/qtracker/lib.php');

use external_value;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_warnings;
use local_qtracker\issue;
use local_qtracker\external\helper;
use local_qtracker\external\issue_exporter;

class get_issues extends \external_api {

    /**
     * Returns description of get_issues() parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function get_issues_parameters() {
        return new external_function_parameters(
            array(
                'criteria' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'key' => new external_value(PARAM_ALPHA, 'the issue column to search, expected keys (value format) are:
                                "id" (int) matching issue id,
                                "questionid" (int) issue questionid,
                                "state" (string) issue state,
                                "title" (Sstring) issue last name
                                (Note: you can use % for searching but it may be considerably slower!)'),
                            'value' => new external_value(PARAM_RAW, 'the value to search')
                        )
                    ),
                    'the key/value pairs to be considered in issue search. Values can not be empty.
                        Specify different keys only once (fullname => \'issue1\', auth => \'manual\', ...) -
                        key occurences are forbidden.
                        The search is executed with AND operator on the criterias. Invalid criterias (keys) are ignored,
                        the search is still executed on the valid criterias.
                        You can search without criteria, but the function is not designed for it.
                        It could very slow or timeout. The function is designed to search some specific issues.'
                )
            )
        );
    }

    /**
     * Retrieve matching issue.
     *
     * @throws moodle_exception
     * @param array $criteria the allowed array keys are id/lastname/firstname/idnumber/issuename/email/auth.
     * @return array An array of arrays containing issue profiles.
     * @since Moodle 2.5
     */
    public static function get_issues($criteria = array()) {
        global $CFG, $issue, $DB, $PAGE, $USER;

        require_once($CFG->dirroot . "/local/qtracker/lib.php");
        $params = self::validate_parameters(
            self::get_issues_parameters(),
            array('criteria' => $criteria)
        );

        // Validate the criteria and retrieve the issues.
        $issues = array();
        $warnings = array();
        $sqlparams = array();
        $usedkeys = array();

        $sql = '1 = 1';
        foreach ($params['criteria'] as $criteriaindex => $criteria) {

            // Check that the criteria has never been used.
            if (array_key_exists($criteria['key'], $usedkeys)) {
                throw new moodle_exception('keyalreadyset', '', '', null, 'The key ' . $criteria['key'] . ' can only be sent once');
            } else {
                $usedkeys[$criteria['key']] = true;
            }

            $invalidcriteria = false;
            // Clean the parameters.
            $paramtype = PARAM_RAW;
            switch ($criteria['key']) {
                case 'id':
                    $paramtype = PARAM_INT;
                    break;
                case 'questionid':
                    $paramtype = PARAM_INT;
                    break;
                case 'userid':
                    $paramtype = PARAM_INT;
                    break;
                case 'state':
                    $paramtype = PARAM_TEXT;
                    break;
                case 'title':
                    $paramtype = PARAM_TEXT;
                    break;
                default:
                    // Send back a warning that this search key is not supported in this version.
                    // This warning will make the function extandable without breaking clients.
                    $warnings[] = array(
                        'item' => $criteria['key'],
                        'warningcode' => 'invalidfieldparameter',
                        'message' =>
                        'The search key \'' . $criteria['key'] . '\' is not supported, look at the web service documentation'
                    );
                    // Do not add this invalid criteria to the created SQL request.
                    $invalidcriteria = true;
                    unset($params['criteria'][$criteriaindex]);
                    break;
            }

            if (!$invalidcriteria) {
                $cleanedvalue = clean_param($criteria['value'], $paramtype);

                $sql .= ' AND ';

                // Create the SQL.
                switch ($criteria['key']) {
                    case 'id':
                    case 'questionid':
                    case 'state':
                        $sql .= $criteria['key'] . ' = :' . $criteria['key'];
                        $sqlparams[$criteria['key']] = $cleanedvalue;
                        break;
                    case 'title':
                        $sql .= $DB->sql_like($criteria['key'], ':' . $criteria['key'], false);
                        $sqlparams[$criteria['key']] = $cleanedvalue;
                        break;
                    default:
                        break;
                }
            }
        }

        $issues = $DB->get_records_select('qtracker_issue', $sql, $sqlparams, 'id ASC');

        // Finally retrieve each issues information.
        $returnedissues = array();
        foreach ($issues as $issue) {
            // Context validation.
            $context = \context::instance_by_id($issue->contextid);
            self::validate_context($context);

            // Capability checking.
            issue_require_capability_on($issue, 'view');

            $renderer = $PAGE->get_renderer('core');
            $exporter = new issue_exporter($issue, ['context' => $context]);
            $issuedetails = $exporter->export($renderer);
            // Return the issue only if all the searched fields are returned.
            // Otherwise it means that the $issue was not allowed to search the returned issue.
            if (!empty($issuedetails)) {
                $validissue = true;

                foreach ($params['criteria'] as $criteria) {
                    if (empty($issuedetails->{$criteria['key']})) {
                        $validissue = false;
                    }
                }

                if ($validissue) {
                    $returnedissues[] = $issuedetails;
                }
            }
        }

        return array('issues' => $returnedissues, 'warnings' => $warnings);
    }

    /**
     * Returns description of get_issues result value.
     *
     * @return external_description
     * @since Moodle 2.5
     */
    public static function get_issues_returns() {
        return new external_single_structure(
            array(
                'issues' => new external_multiple_structure(
                    issue_exporter::get_read_structure()
                ),
                'warnings' => new external_warnings('always set to \'key\'', 'faulty key name')
            )
        );
    }
}
