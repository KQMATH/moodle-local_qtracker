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
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;
use stdClass;
use help_icon;

/**
 * Question issue registration block class.
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue_registration_block implements renderable, templatable {

    /** @var \question_definition[] Array of {@link \question_definition} */
    public $questions = array();

    /** @var \question_usage_by_activity */
    protected $quba;

    /** @var array of stdclass strings to display */
    public $slots = array();

    /** @var array of existing issue ids display */
    public $issueids = array();

    /** @var help_icon The help icon. */
    protected $helpicon;

    // TODO: create an alternative (class) for registering issues  that are not linked to an attempt....

    /**
     * Construct the contents of the block
     * @param \question_definition[] $questions The questions that can be filed issues for.
     * @param int $userid The id of the user.
     * @throws \coding_exception If called at incorrect times
     */
    public function __construct(\question_usage_by_activity $quba, $slots, $contextid) {

        $this->quba = $quba;
        $this->slots = $slots;
        $this->contextid = $contextid;

        // Todo  remove questions.....
        foreach ($this->slots as $slot) {
            $this->questions[] = $this->quba->get_question($slot);
        }
        $this->load_issues();
        $this->helpicon = new help_icon('question', 'local_qtracker');
    }

    private function load_issues() {
        global $DB;

        $queryparams = ['questionusageid' => $this->quba->get_id()];
        list($sql, $params) = $DB->get_in_or_equal($this->slots, SQL_PARAMS_NAMED);
        $queryparams += $params;
        $where = 'questionusageid = :questionusageid AND slot ' . $sql;
        $this->issueids = $DB->get_fieldset_select('qtracker_issue', 'id', $where, $queryparams);
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

        // TODO: only check if questions exists... otherwise i dont need them...
        if (count($this->questions) > 1) {
            $data->hasmultiple = true;

            $select = new stdClass();
            $options = array();
            $select->name = "slot";;
            $select->label = "Question";
            $select->helpicon = $this->helpicon->export_for_template($output);

            foreach ($this->questions as $key => $question) {
                $option = new stdClass();
                $option->value = $this->slots[$key];
                $option->name = $this->slots[$key];
                array_push($options, $option);
            }
            $select->options = $options;
            $data->select = $select;
        } else {
            $data->hasmultiple = false;
            $data->slot = $this->slots[0];
        }

        $data->qubaid = $this->quba->get_id();
        $data->action = $url;
        $data->tooltip = "This is a tooltip";

        $button = new stdClass();
        $button->type = "submit";
        $button->classes = "col-auto";
        $button->label = "Submit new issue";
        $data->button = $button;
        $data->issueids = json_encode($this->issueids);
        $data->contextid = $this->contextid;

        // TODO: Fix this as both the button and the select gets this. Wrap in separate mustashe templates.

        // $data->questions = $questions;
        return $data;
    }
}
