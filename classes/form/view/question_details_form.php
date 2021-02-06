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

/**
 * Question form
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_details_form extends \moodleform {

    /**
     * question_details_form constructor.
     * @param $question
     * @param \moodle_url $url
     */
    public function __construct($question, \moodle_url $url) {
        $this->question = $question;
        parent::__construct($url);
    }

    /**
     *
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'question' ,
            get_string('question', 'core') . " - " . $this->question->name);

        $description = \html_writer::start_div();
        $description .= $this->question->name;
        $description .= $this->question->questiontext;
        $description .= \html_writer::end_div();

        $mform->addElement('html', $description);
        $mform->setExpanded('question', false);

    }
}
