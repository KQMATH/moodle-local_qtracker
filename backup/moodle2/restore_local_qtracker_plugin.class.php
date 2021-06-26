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
 * Defines restore_local_qtracker_plugin class
 *
 * @package     local_qtracker
 * @author      David Rise Knotten <david_knotten@hotmail.no>
 * @copyright   2021 Norwegian University of Science and Technology (NTNU)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Local qtracker restore
 *
 * @package     local_qtracker
 * @author      David Rise Knotten <david_knotten@hotmail.no>
 * @copyright   2021 Norwegian University of Science and Technology (NTNU)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class restore_local_qtracker_plugin extends restore_local_plugin {

    /**
     *
     */
    protected function define_question_plugin_structure() {
        $paths = array();

        //plugin_local_qtracker_question
        $elename = 'issue'; // This defines the postfix of 'process_*' below.
        $elepath = $this->get_pathfor('issue');
        $paths[] = new restore_path_element($elename, $elepath);
        return $paths; // And we return the interesting paths.
    }

    public function process_issue($data) {
        global $DB;

        $data = (object)$data;
        print_object($data);

        $this->get_task()->

        $oldquestionid   = $this->get_old_parentid('question');
        $oldcontextid   = $this->get_old_parentid('context');

        //$newquestionid   = $this->get_new_parentid('question');
        //$questioncreated = (bool) $this->get_mappingid('question_created', $oldquestionid);
        //$oldquestioncategoryid = $this->get_old_parentid('question_category');
        //$newquestioncategoryid = $this->get_new_parentid('question_category');
        //$questioncategory = $this->get_mapping('question_category', $newquestioncategoryid);

        $question = $this->get_mapping('question', $oldquestionid);
        $context = $this->get_mapping('context', $oldcontextid);
        $quba = $this->get_mapping('question_usage', $data->questionusageid);


        echo '<h1>Contextid</h1>';
        print_object($this->get_mappingid('context',$this->get_task()->get_old_contextid()));
        print_object($this->get_mapping('context',$this->get_task()->get_old_contextid())->id);
        //$data->questionid = $newquestionid;
        //echo "<h1>Questioncategory</h1>";
        //print_object($questioncategory);
        //echo "<h1>Question</h1>";
        //print_object($question);
        //echo "<h1>Data</h1>";
        //print_object($data);
        //$DB->insert_record("local_qtracker_issue", $data);

        echo '<h1>Question</h1>';
        print_object($question);
        echo '<h1>Context</h1>';
        print_object($context);
        echo '<h1>Quba</h1>';
        print_object($quba);
        echo '<h1></h1>';
        //print_object();
        echo '<h1></h1>';
        //print_object();


        //throw new Error("dsakd");
        $issue = \local_qtracker\issue::create($data->title, $data->description, $question, $this->get_mapping('context',$this->get_task()->get_old_contextid())->id, $quba);

        //throw new Error("sksk");

        //$DB->insert_record('local_qtracker_issue', $data);

    }
}
