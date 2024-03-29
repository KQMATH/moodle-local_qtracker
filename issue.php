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

use core\check\performance\debugging;
use core\message\message;
use local_qtracker\issue;
use local_qtracker\output\question_issue_page;
use local_qtracker\output\email\issue_comment_email;
use mod_capquiz\capquiz;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/qtracker/lib.php');
require_once($CFG->dirroot . '/local/qtracker/locallib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$issueid = required_param('issueid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid));
if (!$course) {
    print_error('invalidcourseid');
}

require_login($course);

$url = new \moodle_url('/local/qtracker/issue.php');
$url->param('courseid', $courseid);
$url->param('issueid', $issueid);

$returnurl = new \moodle_url('/local/qtracker/issues.php');
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
    null,
    \navigation_node::TYPE_CONTAINER,
    null,
    'qtracker'
);
$issuesnode->add(
    get_string('issues', 'local_qtracker'),
    new \moodle_url('/local/qtracker/view.php', array('courseid' => $courseid))
);
$issuesnode->add(get_string('issue', 'local_qtracker'));



// Load issue.
$issue = issue::load($issueid);
if (!$issue) {
    $issuesurl = new \moodle_url('/local/qtracker/view.php', array('courseid' => $courseid));
    redirect($issuesurl);
}

// Process issue actions.
$commentissue = optional_param('commentissue', false, PARAM_BOOL);
$commenttext = optional_param('commenteditor', false, PARAM_RAW);
$sendmessage = optional_param('sendmessage', false, PARAM_BOOL);

if ($commentissue) {
    $comment = $issue->create_comment($commenttext);
    if ($sendmessage) {
        if (local_qtracker_send_comment($course, $issue, $comment)) {
            $comment->mark_mailed();
        }
    }
    redirect($PAGE->url);
}

$closeissue = optional_param('closeissue', false, PARAM_BOOL);
if ($closeissue) {
    if ($commenttext != false) {
        $comment = $issue->create_comment($commenttext);
        if ($sendmessage) {
            if (local_qtracker_send_comment($course, $issue, $comment)) {
                $comment->mark_mailed();
            }
        }
    }
    $issue->close();
    redirect($PAGE->url);
}

$reopenissue = optional_param('reopenissue', false, PARAM_BOOL);
if ($reopenissue) {
    $issue->open();
    redirect($PAGE->url);
}

$notifycommentid = optional_param('notifycommentid', null, PARAM_INT);
if (!is_null($notifycommentid)) {
    $comment = issue_comment::load($notifycommentid);
    if (local_qtracker_send_comment($course, $issue, $comment)) {
        $comment->mark_mailed();
    }
    redirect($PAGE->url);
}

$deletecommentid = optional_param('deletecommentid', null, PARAM_INT);
if (!is_null($deletecommentid)) {
    $comment = issue_comment::load($deletecommentid);
    $comment->delete();
    redirect($PAGE->url);
}

// Capability checking.
local_qtracker_issue_require_capability_on($issue->get_issue_obj(), 'view');

$renderer = $PAGE->get_renderer('local_qtracker');
$questionissuepage = new question_issue_page($issue, $courseid);

$data = $renderer->render($questionissuepage);

echo $OUTPUT->header();
echo $data;
echo $OUTPUT->footer();

if ($issue->get_state() == 'new') {
    $issue->open();
}
