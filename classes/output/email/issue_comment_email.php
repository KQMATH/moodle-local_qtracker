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
 * Issue comment email renderable.
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\output\email;

defined('MOODLE_INTERNAL') || die();

use local_qtracker\issue;
use renderable;
use templatable;

/**
 * Issue comment email renderable.
 *
 * @package    local_qtracker
 * @copyright  2021 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue_comment_email implements renderable, templatable {

    /**
     * The course that the comment is in.
     *
     * @var object $course
     */
    protected $course = null;

    /**
     * The comment being displayed.
     *
     * @var object $comment
     */
    protected $comment = null;

    /**
     * Whether to override display when displaying usernames.
     * @var boolean $viewfullnames
     */
    protected $viewfullnames = false;

    /**
     * The user that is viewing the comment.
     *
     * @var object $userto
     */
    protected $userto = null;

    /**
     * The user that made the comment.
     *
     * @var object $author
     */
    protected $author = null;


    /**
     * Builds a renderable comment email
     *
     * @param object $course Course of the issue
     * @param object $comment Issue comment
     * @param object $author Author of the comment
     * @param object $recipient Recipient of the email
     * @param bool $canreply True if the user can reply to the post
     */
    public function __construct($course, $comment, $author, $recipient) {
        $this->course = $course;
        $this->comment = $comment;
        $this->author = $author;
        $this->userto = $recipient;
        $this->issue = issue::load($this->comment->get_issue_id());
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \mod_forum_renderer $renderer The render to be used for formatting the message
     * @param bool $plaintext Whethe the target is a plaintext target
     * @return array Data ready for use in a mustache template
     */
    public function export_for_template(\renderer_base $renderer, $plaintext = false) {
        if ($plaintext) {
            return $this->export_for_template_text($renderer);
        } else {
            return $this->export_for_template_html($renderer);
        }
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \mod_forum_renderer $renderer The render to be used for formatting the message and attachments
     * @return array Data ready for use in a mustache template
     */
    protected function export_for_template_text(\renderer_base $renderer) {
        $data = $this->export_for_template_shared($renderer);
        return $data + array(
            'id'                            => html_entity_decode($this->comment->get_id()),
            'coursename'                    => html_entity_decode($this->get_coursename()),
            'courselink'                    => html_entity_decode($this->get_courselink()),
            'issuetitle'                    => html_entity_decode($this->get_issuetitle()),
            'issuedescription'              => html_entity_decode($this->get_issuedescription()),
            'authorfullname'                => html_entity_decode($this->get_author_fullname()),
            'commentdate'                   => html_entity_decode($this->get_commentdate()),
            'commentdescription'            => html_entity_decode($this->get_commentdescription()),

            // Format some components according to the renderer.
            'message'                       => html_entity_decode($this->comment->get_description()),
            'authorlink'                    => $this->get_authorlink(),
            'authorpicture'                 => $this->get_author_picture($renderer),
        );
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $renderer The render to be used for formatting the message and attachments
     * @return array Data ready for use in a mustache template
     */
    protected function export_for_template_html(\renderer_base $renderer) {
        $data = $this->export_for_template_shared($renderer);
        return $data + array(
            'id'                            => $this->comment->get_id(),
            'coursename'                    => $this->get_coursename(),
            'courselink'                    => $this->get_courselink(),
            'issuetitle'                    => $this->get_issuetitle(),
            'issuedescription'              => $this->get_issuedescription(),
            'commentdescription'            => $this->get_commentdescription(),
            'authorfullname'                => $this->get_author_fullname(),
            'commentdate'                   => $this->get_commentdate(),

            // Format some components according to the renderer.
            'message'                       => $this->comment->get_description(),
        );
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $renderer The render to be used for formatting the message and attachments
     * @return stdClass Data ready for use in a mustache template
     */
    protected function export_for_template_shared(\renderer_base $renderer) {
        return array(
            'issuelink'                     => $this->get_issuelink(),
            'authorlink'                    => $this->get_authorlink(),
            'authorpicture'                 => $this->get_author_picture($renderer),
        );
    }

    /**
     * Get the link to the course.
     *
     * @return string
     */
    public function get_courselink() {
        $link = new \moodle_url(
            '/course/view.php', array(
                'id'    => $this->course->id,
            )
        );

        return $link->out(false);
    }

    /**
     * Get the link to the author's profile page.
     *
     * @return string
     */
    public function get_authorlink() {
        $link = new \moodle_url(
            '/user/view.php', array(
                'id' => $this->comment->get_userid(),
                'course' => $this->course->id,
            )
        );

        return $link->out(false);
    }

    /**
     * Get the link to reply to the current issue.
     *
     * @return string
     */
    public function get_issuelink() {
        return new \moodle_url(
            '/local_qtracker/issue.php', array(
                'courseid' => $this->course->id,
                'issueid' => $this->comment->get_issue_id(),
            )
        );
    }

    /**
     * ID number of the course that the comment is in.
     *
     * @return string
     */
    public function get_courseidnumber() {
        return s($this->course->idnumber);
    }

    /**
     * The full name of the course that the comment is in.
     *
     * @return string
     */
    public function get_coursefullname() {
        return format_string($this->course->fullname, true, array(
            'context' => \context_course::instance($this->course->id),
        ));
    }

    /**
     * The name of the course that the comment is in.
     *
     * @return string
     */
    public function get_coursename() {
        return format_string($this->course->shortname, true, array(
            'context' => \context_course::instance($this->course->id),
        ));
    }

    /**
     * The title of the current issue.
     *
     * @return string
     */
    public function get_issuetitle() {
        return format_string($this->issue->get_title(), true);
    }

    /**
     * The description of the current issue.
     *
     * @return string
     */
    public function get_issuedescription() {
        return format_string($this->issue->get_description(), true);
    }

    /**
     * The description of the current comment.
     *
     * @return string
     */
    public function get_commentdescription() {
        return format_string($this->comment->get_description(), true);
    }

    /**
     * The date of the comment, formatted according to the comment author's
     * preferences.
     *
     * @return string.
     */
    public function get_commentdate() {
        global $CFG;
        $commentcreated = $this->comment->get_timecreated();
        return userdate($commentcreated, "", \core_date::get_user_timezone($this->comment->get_userid()));
    }

    /**
     * The fullname of the comment author.
     *
     * @return string
     */
    public function get_author_fullname() {
        return fullname($this->author, $this->viewfullnames);
    }

    /**
     * The HTML for the author's user picture.
     *
     * @param   \renderer_base $renderer
     * @return string
     */
    public function get_author_picture(\renderer_base $renderer) {
        return $renderer->user_picture($this->author, array('courseid' => $this->course->id));
    }
}
