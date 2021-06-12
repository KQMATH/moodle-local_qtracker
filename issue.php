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
use mod_capquiz\capquiz;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/qtracker/lib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$issueid = required_param('issueid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
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
    null, \navigation_node::TYPE_CONTAINER, null, 'qtracker'
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

// TODO: require capability for editing issues.
// Process issue actions.
$commentissue = optional_param('commentissue', false, PARAM_BOOL);
$commenttext = optional_param('commenteditor', false, PARAM_RAW);
if ($commentissue) {
    $issue->create_comment($commenttext);
    redirect($PAGE->url);
}

$commentanddmissue = optional_param('commentanddmissue', false,PARAM_BOOL);
if ($commentanddmissue) {
    $user = $DB->get_record('user', array('id' => $issue->get_userid()));
    $message = new \core\message\message();
    $message->component = 'local_qtracker'; // Your plugin's name
    $message->name = 'issueresponse'; // Your notification name from message.php
    $message->userfrom = $USER; // If the message is 'from' a specific user you can set them here
    $message->userto = $user;
    $message->subject = 'Issue \''.$issue->get_title().'\'';
    $message->fullmessage = $commenttext;
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->fullmessagehtml = '<p>'.$commenttext.'</p>';
    $message->smallmessage = '';
    $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
    $message->contexturl = (new \moodle_url('/course/'))->out(false); // A relevant URL for the notification
    $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl
    message_send($message);
    $issue->create_comment($commenttext);

}

$commentandmailissue = optional_param('commentandmailissue', false, PARAM_BOOL);
if ($commentandmailissue) {
    $user = $DB->get_record('user', array('id' => $issue->get_userid()));
    email_to_user($user, $USER,get_string('issuesubject', 'local_qtracker'), $commenttext);
    $issue->create_comment($commenttext);
}

$closeissue = optional_param('closeissue', false, PARAM_BOOL);
if ($closeissue) {
    if ($commenttext != false) {
        $issue->create_comment($commenttext);
    }
    $issue->close();
    redirect($PAGE->url);
}

$reopenissue = optional_param('reopenissue', false, PARAM_BOOL);
if ($reopenissue) {
    $issue->open();
    redirect($PAGE->url);
}

$deletecommentid = optional_param('deletecommentid', null, PARAM_INT);
if (!is_null($deletecommentid)) {
    $comment = issue_comment::load($deletecommentid);
    $comment->delete();
    redirect($PAGE->url);
}

// Capability checking.
issue_require_capability_on($issue->get_issue_obj(), 'view');

$renderer = $PAGE->get_renderer('local_qtracker');
$questionissuepage = new question_issue_page($issue, $courseid);

$data = $renderer->render($questionissuepage);

echo $OUTPUT->header();
echo $data;
echo $OUTPUT->footer();

if ($issue->get_state() == 'new') {
    $issue->open();
}
