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
require_once($CFG->dirroot . '/local/qtracker/lib.php');

use external_value;
use external_function_parameters;
use external_single_structure;
use external_warnings;
use local_qtracker\external\helper;

class get_question extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_question_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'question id')
            )
        );
    }


    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_question($questionid) {
        global $PAGE, $USER;

        $status = false;
        $warnings = array();

        //Parameter validation
        $params = self::validate_parameters(self::get_question_parameters(),
            array(
                'id' => (int) $questionid,
            )
        );

        $question = \question_bank::load_question_data($params['id']);
        if (!$question) {
            throw new \moodle_exception('cannotgetquestion', 'local_qtracker', '', $params['id']);
        }

        //Context validation
        $context = \context::instance_by_id($question->contextid);
        self::validate_context($context);

        question_require_capability_on($question, 'view');

        $renderer = $PAGE->get_renderer('core');
        $exporter = new \core_question\external\question_summary_exporter($question, ['context' => $context]);
        $questionsummary = $exporter->export($renderer);

        $result = array();
        $result['status'] = $status;
        $result['question'] = $questionsummary;
        $result['warnings'] = $warnings;

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_question_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'question' => \core_question\external\question_summary_exporter::get_read_structure(),
                'warnings' => new external_warnings()
            )
        );

    }
}
