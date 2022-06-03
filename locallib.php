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
 * Library of functions for QTracker.
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2022 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_qtracker\issue;
use local_qtracker\issue_comment;
use local_qtracker\output\email\issue_comment_email;

/**
 * Send comment as message.
 */
function local_qtracker_send_comment($course, issue $issue, issue_comment $comment) {
    global $DB, $USER, $PAGE;

    $htmlemailrenderer = $PAGE->get_renderer('local_qtracker', 'email', 'htmlemail');
    $textemailrenderer = $PAGE->get_renderer('local_qtracker', 'email', 'textemail');

    $recipient = $DB->get_record('user', array('id' => $issue->get_userid()));
    $postsubject = get_string('issueupdatednotify', 'local_qtracker', $issue->get_title());

    $data = new issue_comment_email($course, $comment, $USER, $recipient);

    $message = new \core\message\message();
    $message->component           = 'local_qtracker';
    $message->name                = 'issueresponse';
    $message->userfrom            = $USER;
    $message->userto              = $recipient;
    $message->subject             = $postsubject;
    $message->fullmessage         = $textemailrenderer->render($data);
    $message->fullmessageformat   = FORMAT_HTML;
    $message->fullmessagehtml     = $htmlemailrenderer->render($data);
    $message->smallmessage        = '';
    $message->notification        = 1;
    $message->replyto             = null;

    // TODO: make issue.php page handle correctly students viewing comments.
    //$contexturl = new \moodle_url('/local/qtracker/issue.php', ['courseid' => $course->id, 'issueid' => $issue->get_id()]);
    //$message->contexturl = $contexturl->out();
    //$message->contexturlname = $issue->get_title();

    return message_send($message);
}
