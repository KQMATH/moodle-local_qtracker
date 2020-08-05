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
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\form\view;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue_registration_form extends \moodleform {


    // TODO: FIX EVERYTHING IN THIS FILE AND REPLACE THE CURRENT JS SETUP.


    public function __construct($questions, \moodle_url $url) {
        $this->questions = $questions;
        parent::__construct($url);
    }

    public function definition() {
        $form = $this->_form;

        $options = [];
        $i=0;
        foreach ($this->questions as $key => $question) {
            $options[$i] = $question->name;
            $i++;
        }

        $form->addElement('select', 'question', null, $options);
        //$mform->addHelpButton('question', 'question', 'block_community');
        //$mform->setDefault('question', $question);


        $form->addElement('text', 'default_user_rating', get_string('default_user_rating', 'capquiz'));
        $form->setType('default_user_rating', PARAM_INT);
        $form->setDefault('default_user_rating', 'lol');
        $form->addRule('default_user_rating', get_string('default_user_rating_required', 'capquiz'), 'required', null, 'client');

        $form->addElement('submit', 'submitbutton', get_string('savechanges'));
    }

    public function validations($data, $files) {
        $errors = [];
        if (empty($data['default_user_rating'])) {
            $errors['default_user_rating'] = get_string('default_user_rating_required', 'capquiz');
        }
        if (empty($data['starstopass']) || $data['starstopass'] < 0 || $data['starstopass'] > 5) {
            $errors['starstopass'] = get_string('stars_to_pass_required', 'capquiz');
        }
        return $errors;
    }

}
