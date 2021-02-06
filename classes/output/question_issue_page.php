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
 * Renderable for issues page
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

use coding_exception;
use dml_exception;
use moodle_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use local_qtracker\issue;
use local_qtracker\external\issue_exporter;
use local_qtracker\external\issue_comment_exporter;
use local_qtracker\form\view\question_details_form;

/**
 * Class containing data for question issue page.
 *
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_issue_page implements renderable, templatable {

    /** The default number of results to be shown per page. */
    const DEFAULT_PAGE_SIZE = 20;

    /** @var issue|null  */
    protected $questionissue = null;

    /** @var array  */
    protected $courseid = [];

    /**
     * Construct this renderable.
     *
     * @param \local_qtracker\question_issues_table $questionissuestable
     * @param courseid
     */
    public function __construct(issue $questionissue, $courseid) {
        $this->questionissue = $questionissue;
        $this->courseid = $courseid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $DB, $PAGE;
        $data = new stdClass();

        $context = \context_course::instance($this->courseid);
        $issueexporter = new issue_exporter($this->questionissue->get_issue_obj(), ['context' => $context]);
        $issuedetails = $issueexporter->export($output);

        // Process default issue description.
        $issuedescription = new stdClass();
        $user = $DB->get_record('user', array('id' => $issuedetails->userid));
        $issuedescription->fullname = $user->username;
        $issuedescription->userurl = "http://lol.no";
        $userpicture = new \user_picture($user);
        $userpicture->size = 0; // Size f2.
        $issuedescription->profileimageurl = $userpicture->get_url($PAGE)->out(false);

        $issuedetails->issuedescription = $issuedescription;

        $commentsdetails = array();

        // Process all issue comments...
        $comments = $this->questionissue->get_comments();
        foreach ($comments as $comment) {
            $commentexporter = new issue_comment_exporter($comment->get_comment_obj(), ['context' => $context]);
            $commentdetails = $commentexporter->export($output);
            // Get the user data.
            $user = $DB->get_record('user', array('id' => $commentdetails->userid));
            $commentdetails->fullname = $user->username;
            $userurl = new \moodle_url('/user/view.php');
            $userurl->param('id', $user->id);
            $userurl->param('course', $this->courseid);
            $commentdetails->userurl = $userurl;
            $userpicture = new \user_picture($user);
            $userpicture->size = 0; // Size f2.
            $commentdetails->profileimageurl = $userpicture->get_url($PAGE)->out(false);

            $deleteurl = new \moodle_url('/local/qtracker/issue.php');
            $deleteurl->param('courseid', $this->courseid);
            $deleteurl->param('issueid', $this->questionissue->get_id());
            $deleteurl->param('deletecommentid', $commentdetails->id);
            $commentdetails->deleteurl = $deleteurl;
            array_push($commentsdetails, $commentdetails);
        }

        $issuedetails->comments = $commentsdetails;
        $issuedetails->{$issuedetails->state} = true;
        $data->questionissue = $issuedetails;

        // Set the user picture data.
        $user = $DB->get_record('user', array('id' => $USER->id));
        $userpicture = new \user_picture($user);
        $userpicture->size = 0; // Size f2.
        $data->profileimageurl = $userpicture->get_url($PAGE)->out(false);

        if ($this->questionissue->get_state() == "closed") {
            $reopenbutton = new stdClass();
            $reopenbutton->label = get_string('reopenissue', 'local_qtracker');
            $reopenbutton->name = "reopenissue";
            $reopenbutton->value = true;
            $data->reopenbutton = $reopenbutton;
        } else {
            $closebutton = new stdClass();
            $closebutton->label = get_string('closeissue', 'local_qtracker');
            $closebutton->name = "closeissue";
            $closebutton->value = true;
            $data->closebutton = $closebutton;
        }

        $commentbutton = new stdClass();
        $commentbutton->primary = true;
        $commentbutton->name = "commentissue";
        $commentbutton->value = true;
        $commentbutton->label = get_string('comment', 'local_qtracker');
        $data->commentbutton = $commentbutton;

        $question = \question_bank::load_question($this->questionissue->get_questionid());
        question_require_capability_on($question, 'use');

        $questiondata = new stdClass();
        $questiondata->questionid = $question->id;
        $questiondata->questionname = $question->name;
        $questiondata->preview_url = question_preview_url($question->id, null, null, null, null, $context);

        $edit_url = new \moodle_url('/question/question.php');
        $edit_url->param('id', $question->id);
        $edit_url->param('courseid', $this->courseid);
        $questiondata->edit_url = $edit_url;

        $form = new question_details_form($question, $PAGE->url);
        $questiondata->questiontext = $form->render();
        $data->question = $questiondata;

        // Setup text editor.
        $editor = editors_get_preferred_editor(FORMAT_HTML);
        $options = array();
        $editor->use_editor('commenteditor', $options);

        return $data;
    }
}
