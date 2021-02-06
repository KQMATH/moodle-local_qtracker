<?php
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

namespace local_qtracker;

defined('MOODLE_INTERNAL') || die();

/**
 * Question comment class.
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue_comment {

    /**
     * @var \stdClass
     */
    protected $comment = null;

    /**
     * Constructor.
     *
     * @param int|\stdClass $comment
     * @return void
     */
    public function __construct($comment) {
        global $DB;
        if (is_scalar($comment)) {
            $comment = $DB->get_record('qtracker_comment', array('id' => $comment), '*', MUST_EXIST);
            if (!$comment) {
                throw new \moodle_exception('errorunexistingmodel', 'analytics', '', $comment);
            }
        }
        $this->comment = $comment;
    }

    /**
     * Returns the comment id.
     *
     * @return int
     */
    public function get_id() {
        return $this->comment->id;
    }

    /**
     * Returns the related issue id.
     *
     * @return int
     */
    public function get_issue_id() {
        return $this->comment->issueid;
    }

    /**
     * Returns the comment description.
     *
     * @return int
     */
    public function get_description() {
        return $this->comment->description;
    }

    /**
     * Returns the comment userid.
     *
     * @return int
     */
    public function get_userid() {
        return $this->comment->userid;
    }

    /**
     * Returns the comment timecreated.
     *
     * @return int
     */
    public function get_timecreated() {
        return $this->comment->timecreated;
    }

    /**
     * Returns a plain \stdClass with the comment data.
     *
     * @return \stdClass
     */
    public function get_comment_obj() {
        return $this->comment;
    }

    /**
     * Returns a plain \stdClass with the comment data.
     *
     * @return \stdClass
     */
    public function get_comments() {
        global $DB;

        return $this->comment;
    }

    /**
     * Loads and returns issue_comment with id $comment
     *
     * @param $comment
     *
     * @return issue_comment
     */
    public static function load(int $comment) {
        global $DB;
        $commentobj = $DB->get_record('qtracker_comment', ['id' => $comment]);
        if ($commentobj === false) {
            return null;
        }
        return new issue_comment($commentobj);
    }

    /**
     * Creates a new comment.
     *
     * @param $description
     * @param issue $issue
     *
     * @return issue_comment
     */
    public static function create($description, issue $issue) {
        global $USER, $DB;

        $commentobj = new \stdClass();
        $commentobj->description = $description;
        $commentobj->issueid = $issue->get_id();
        $commentobj->userid = $USER->id;
        $time = time();
        $commentobj->timecreated = $time;
        // $commentobj->usermodified = $USER->id;

        $id = $DB->insert_record('qtracker_comment', $commentobj);
        $commentobj->id = $id;

        $comment = new issue_comment($commentobj);
        return $comment;
    }

    /**
     * Delete this comment.
     *
     * @return void
     */
    public function delete() {
        global $DB;
        return $DB->delete_records('qtracker_comment', array('id' => $this->get_id()));
    }

    /**
     * Sets description of this comment
     *
     * @param $title
     *
     * @return void
     */
    public function set_description($title) {
        global $DB;
        $this->comment->description = $title;
        $DB->update_record('qtracker_comment', $this->comment);
    }
}
