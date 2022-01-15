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
 * Question form
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\form\view;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/questionlib.php');

/**
 * Question form
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class new_issue_form extends \moodleform {

    /**
     * question_details_form constructor.
     * @param \stdClass $question The question to be formed
     * @param \moodle_url $url Questions moodle url
     */
    public function __construct(\moodle_url $url) {
        parent::__construct($url);
    }


    /**
     * Defines form
     */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));
        $mform->addElement('text', 'title', get_string('title', 'local_qtracker'));
        $mform->addRule('title', get_string('required'), 'required', null, 'client');
        $mform->setType('title', PARAM_RAW);

        $mform->addElement('text', 'questionid', get_string('questionid', 'local_qtracker'));
        $mform->addRule('questionid', get_string('required'), 'required', null, 'client');
        $mform->addRule('questionid', get_string('required'), 'nonzero', null, 'client');
        $mform->setType('questionid', PARAM_INT);
        $mform->addHelpButton('questionid', 'newissue_questionid', 'local_qtracker');

        $mform->addElement('editor', 'description', get_string('description', 'local_qtracker'));
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->setType('description', PARAM_RAW);

        $this->add_action_buttons();
    }

    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $questionid = $data['questionid'];
        $question = $DB->get_record('question', array('id' => $questionid));
        if (!$question) {
            $errors['questionid'] = get_string('errornonexistingquestion', 'local_qtracker');
        } else {
            question_require_capability_on($question, 'use');
        }

        return $errors;
    }
}
