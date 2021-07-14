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
 * This file is used to render the qtracker block
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker;

use local_qtracker\issue;
use local_qtracker\output\new_question_issue_page;
use local_qtracker\form\view\new_issue_form;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/qtracker/lib.php');
require_once($CFG->libdir . '/questionlib.php');

global $DB, $OUTPUT, $PAGE;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

require_login($course);

$url = new \moodle_url('/local/qtracker/new_issue.php');
$url->param('courseid', $courseid);

$returnurl = new \moodle_url('/local/qtracker/new_issues.php');
$returnurl->param('courseid', $courseid);

$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading(get_string('pluginname', 'local_qtracker'));

// Return to issues list.
if (optional_param('return', false, PARAM_BOOL)) {
    redirect($returnurl);
}

$issuesnode = $PAGE->navbar->add(
    get_string('pluginname', 'local_qtracker'),
    null, \navigation_node::TYPE_CONTAINER, null, 'qtracker'
);
$issuesnode->add(
    get_string('issues', 'local_qtracker'),
    new \moodle_url('/local/qtracker/view.php', array('courseid' => $courseid))
);
$issuesnode->add(get_string('newissue', 'local_qtracker'));

// Process form actions.
$mform = new new_issue_form($PAGE->url);

if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $mform->get_data()) {
    $context = \context_course::instance($courseid);
    $question = \question_bank::load_question($data->questionid);
    question_require_capability_on($question, 'view');

    $issue = issue::create($data->title, $data->description['text'], $question, $context->id);
    $issue->open();
    $issueurl = new \moodle_url('/local/qtracker/issue.php', array('courseid' => $courseid, 'issueid' => $issue->get_id()));
    redirect($issueurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('createnewissue', 'local_qtracker'));
$mform->display();
echo $OUTPUT->footer();
