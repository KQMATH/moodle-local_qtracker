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
 * @author      André Storhaug <andr3.storhaug@gmail.com>
 * @copyright   2021 Norwegian University of Science and Technology (NTNU)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Local qtracker restore
 *
 * @package     local_qtracker
 * @copyright   2021 Norwegian University of Science and Technology (NTNU)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class restore_local_qtracker_plugin extends restore_local_plugin {

    /**
     * @var array $issues Stores the IDs of the newly created issues.
     */
    protected $issues = array();

    /**
     *
     */
    protected function define_course_plugin_structure() {
        $paths = array();

        //plugin_local_qtracker_question
        $elename = 'issue'; // This defines the postfix of 'process_*' below.
        $elepath = $this->get_pathfor('/issue');
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'comment'; // This defines the postfix of 'process_*' below.
        $elepath = $this->get_pathfor('/comments/comment');
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'reference'; // This defines the postfix of 'process_*' below.
        $elepath = $this->get_pathfor('/references/reference');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    protected function define_module_plugin_structure() {
        $paths = array();

        $elename = 'issue';
        $elepath = $this->get_pathfor('/issue');
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'comment';
        $elepath = $this->get_pathfor('/comments/comment');
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'reference';
        $elepath = $this->get_pathfor('/references/reference');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    public function process_issue($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->questionid = $this->get_mappingid('question', $data->questionid);

        $oldcontextid = $data->contextid;
        $newcontextid = $this->get_mappingid('context', $oldcontextid);
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (isset($data->questionusageid)) {
            $data->questionusageid = $this->get_mappingid('question_usage', $data->questionusageid);
        }
        $data->contextid = $newcontextid;
        $newitemid = $DB->insert_record('local_qtracker_issue', $data);

        if (!$newcontextid) {
            // Add the array of issues we need to process later.
            $data->id = $newitemid;
            $data->contextid = $oldcontextid;
            $this->issues[$data->id] = $data;
        }

        $this->set_mapping('local_qtracker_issue', $oldid, $newitemid);
    }

    public function process_comment($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->issueid = $this->get_mappingid('local_qtracker_issue', $data->issueid);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $newitemid = $DB->insert_record('local_qtracker_comment', $data);
        $this->set_mapping('local_qtracker_comment', $oldid, $newitemid);
    }

    public function process_reference($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->sourceid = $this->get_mappingid('local_qtracker_issue', $data->sourceid);
        $data->targetid = $this->get_mappingid('local_qtracker_issue', $data->targetid);
        $newitemid = $DB->insert_record('local_qtracker_reference', $data);
        $this->set_mapping('local_qtracker_reference', $oldid, $newitemid);
    }

    /**
     * This function is executed after all the tasks in the plan have been finished.
     * This must be done here because the activities have not been restored yet.
     */
    public function after_restore_module() {
        global $DB;
        // Need to go through and change the values.
        foreach ($this->issues as $issue) {
            $updateissue = new stdClass();
            $updateissue->id = $issue->id;
            $updateissue->contextid = $this->get_mappingid('context', $issue->contextid);
            $DB->update_record('local_qtracker_issue', $updateissue);
        }
    }
}
