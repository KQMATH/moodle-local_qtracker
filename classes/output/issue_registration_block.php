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
 * Renderable for block
 *
 * @package    block_studiosity
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2019 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\output;

defined('MOODLE_INTERNAL') || die;

use local_qtracker\form\view\issue_registration_form;
use moodle_url;
use renderable;
use renderer_base;
use templatable;
use stdClass;


/**
 * Studiosity block class.
 *
 * @package    block_studiosity
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2019 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue_registration_block implements renderable, templatable {

    /** @var \question_definition[] Array of {@link \question_definition} */
    public $questions = array();

    /** @var int User ID */
    public $userid;

    /** @var array of stdclass strings to display */
    public $slots = array();

    /**
     * Construct the contents of the block
     * @param \question_definition[] $questions The questions that can be filed issues for.
     * @param int $userid The id of the user.
     * @throws \coding_exception If called at incorrect times
     */
    public function __construct(array $questions, $userid, $slots=null) {
        $this->questions = $questions;
        $this->userid = $userid;
        $this->slots = $slots;
        if (!is_null($slots)) {
            if (count($questions) != count($slots)) {
                throw new \coding_exception('The number of questions and slots does not match.');
            }
        }
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     * @return stdClass Data to be used for the template
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;
        $url = $PAGE->url;
        $data = new stdClass();
        $data->userid = $this->userid;


        if (count($this->questions) > 1) {
            $data->hasmultiple = true;

            $select = new stdClass();
            $options = array();
            $select->name = "questionid";
            $select->formid = "fjndfsndf";
            $select->label = "fmdifj";
            foreach ($this->questions as $question) {
                $questions[] = [
                    'name' => $question->name,
                    'selected' => true
                ];

                $option = new stdClass();
                $option->value = $question->id;
                $option->name = $question->name;
                array_push($options, $option);
            }
            $select->options = $options;
            $data->select = $select;

        } else {
            $data->hasmultiple = false;
            $data->questionid = $this->questions[0]->id;
        }

        $data->action = $url;
        $data->tooltip = "This is a tooltip";

        // TODO: Fix this as both the button and the select gets this. Wrap in separate mustashe templates.
        $data->label = "Send feedback";

        //$data->questions = $questions;
        return $data;
    }
}
