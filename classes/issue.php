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
 * Issue class
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/qtracker/lib.php');

use local_qtracker\referable;

/**
 * Question issue class.
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue extends referable {

    /**
     * @var \stdClass
     */
    protected $issue = null;

    /**
     * @var array
     */
    protected $comments = array();

    /**
     * Constructor.
     *
     * @param int|\stdClass $issue
     * @return void
     */
    public function __construct($issue) {
        global $DB;
        if (is_scalar($issue)) {
            $issue = $DB->get_record('local_qtracker_issue', array('id' => $issue), '*', MUST_EXIST);
            if (!$issue) {
                throw new \moodle_exception('errorunexistingmodel', 'analytics', '', $issue);
            }
        }
        $this->issue = $issue;
    }

    /**
     * Returns the issue id.
     *
     * @return int
     */
    public function get_id() {
        return $this->issue->id;
    }

    /**
     * Returns the issue title.
     *
     * @return int
     */
    public function get_title() {
        return $this->issue->title;
    }

    /**
     * Returns the issue description.
     *
     * @return int
     */
    public function get_description() {
        return $this->issue->description;
    }

    /**
     * Returns the issue description.
     *
     * @return int
     */
    public function get_state() {
        return $this->issue->state;
    }

    /**
     * Returns the issue questionusageid.
     *
     * @return int
     */
    public function get_qubaid() {
        return $this->issue->questionusageid;
    }

    /**
     * Returns the issue questionid.
     *
     * @return int
     */
    public function get_questionid() {
        return $this->issue->questionid;
    }

    /**
     * Returns the issue slot.
     *
     * @return int
     */
    public function get_slot() {
        return $this->issue->slot;
    }

    /**
     * Returns the issue contextid.
     *
     * @return int
     */
    public function get_contextid() {
        return $this->issue->contextid;
    }

    /**
     * Returns the issue userid.
     *
     * @return int
     */
    public function get_userid() {
        return $this->issue->userid;
    }

    /**
     * Returns the issue timecreated.
     *
     * @return int
     */
    public function get_timecreated() {
        return $this->issue->timecreated;
    }

    /**
     * Returns a plain \stdClass with the issue data.
     *
     * @return \stdClass
     */
    public function get_issue_obj() {
        return $this->issue;
    }

    /**
     * Returns a plain \stdClass with the issue data.
     *
     * @param string $description
     *
     * @return issue_comment
     */
    public function create_comment($description) {
        $comment = issue_comment::create($description, $this);
        array_push($this->comments, $comment);
        return $comment;
    }

    /**
     * Add a new comment to this issue.
     *
     * @return \stdClass
     */
    public function get_comments() {
        global $DB;
        if (empty($this->comments)) {
            $this->comments = array();
            $comments = $DB->get_records('local_qtracker_comment', ['issueid' => $this->get_id()]);
            foreach ($comments as $comment) {
                array_push($this->comments, new issue_comment($comment));
            }
        }
        return $this->comments;
    }

    /**
     * Subsube this issue under another "parent" issue.
     * Read: "This issue is superseded by issue passed as param."
     * @param issue $issue to subsume under
     */
    public function subsume(issue $issue) {
        if ($this->is_superseded()) {
            return false;
        }
        $this->make_outgoing_reference($issue, LOCAL_QTRACKER_REFERENCE_SUPERSEDED);
        return true;
    }

    /**
     * Supersede another issue.
     * Read: "Issue passed as param is superseded by this issue."
     * @param string $description
     *
     * @return bool Returns true if success, false otherwise
     */
    public function supersede_issue(issue $issue) {
        if ($issue->is_superseded()) {
            return false;
        }
        $this->make_incoming_reference($issue, LOCAL_QTRACKER_REFERENCE_SUPERSEDED);
        $issue->close();
        return true;
    }

    /**
     * Returns true if issue is superseded by any issue.
     */
    public function is_superseded() {
        $outrefs = $this->get_outgoing_references();
        $refs = reference_manager::filter_references_by_type($outrefs, LOCAL_QTRACKER_REFERENCE_SUPERSEDED);
        if (!empty($refs)) {
            return true;
        }
        return false;
    }

    /**
     * Get all parent issues (issues that this issue is superseded by)
     */
    public function get_parents() {
        $parents = [];
        $outrefs = $this->get_outgoing_references();
        $refs = reference_manager::filter_references_by_type($outrefs, LOCAL_QTRACKER_REFERENCE_SUPERSEDED);
        foreach ($refs as $ref) {
            $issue = issue::load($ref->get_target_id());
            array_push($parents, $issue);
        }
        return $parents;
    }

    /**
     * Get all child issues (that have been subsumed under this issue)
     */
    public function get_children() {
        $children = [];
        $inrefs = $this->get_incoming_references();
        $refs = reference_manager::filter_references_by_type($inrefs, LOCAL_QTRACKER_REFERENCE_SUPERSEDED);
        foreach ($refs as $ref) {
            array_push($children, issue::load($ref->get_source_id()));
        }
        return $children;
    }

    /**
     * Remove parent issue (that supersedes this issue)
     */
    public function remove_parent(issue $parent) {
        $parentref = null;
        $outrefs = $this->get_outgoing_references();
        $refs = reference_manager::filter_references_by_type($outrefs, LOCAL_QTRACKER_REFERENCE_SUPERSEDED);
        foreach ($refs as $ref) {
            if ($ref->get_target_id() === $parent->get_id()) {
                $parentref = $ref;
            }
        }
        if (is_null($parentref)) {
            return false;
        }
        return $parentref->delete();
    }

    /**
     * Remove child issue (that has been subsumed under this issue)
     */
    public function remove_child(issue $child) {
        $childref = null;
        $inrefs = $this->get_incoming_references();
        $refs = reference_manager::filter_references_by_type($inrefs, LOCAL_QTRACKER_REFERENCE_SUPERSEDED);
        foreach ($refs as $ref) {
            if ($ref->get_source_id() === $child->get_id()) {
                $childref = $ref;
            }
        }
        if (is_null($childref)) {
            return false;
        }
        return $childref->delete();
    }

    /**
     * Loads and returns issue with id $issueid
     *
     * @param int $issueid
     * @return issue|null
     */
    public static function load(int $issueid) {
        global $DB;
        $issueobj = $DB->get_record('local_qtracker_issue', ['id' => $issueid]);
        if ($issueobj === false) {
            return null;
        }
        return new issue($issueobj);
    }

    /**
     * Creates a new issue.
     *
     * @param string $title
     * @param string $description
     * @param \question_definition $question
     * @param int $contextid
     * @param \question_usage_by_activity|null $quba
     * @param int|null  $slot
     *
     * @return issue
     */
    public static function create($title, $description, \question_definition $question, $contextid, $quba = null, $slot = null) {
        global $USER, $DB;

        $issueobj = new \stdClass();
        $issueobj->title = $title;
        $issueobj->description = $description;
        $issueobj->questionid = $question->id;
        $issueobj->questionusageid = !is_null($quba) ? $quba->get_id() : null;
        $issueobj->slot = $slot;
        $issueobj->contextid = $contextid;
        $issueobj->state = 'new';
        $issueobj->userid = $USER->id;
        $time = time();
        $issueobj->timecreated = $time;
        // $issueobj->timemodified = $time;
        // $issueobj->usermodified = $USER->id;

        $id = $DB->insert_record('local_qtracker_issue', $issueobj);
        $issueobj->id = $id;

        $issue = new issue($issueobj);
        return $issue;
    }

    /**
     * Delete this issue.
     *
     * @return void
     */
    public function close() {
        global $DB;
        $this->issue->state = "closed";
        $DB->update_record('local_qtracker_issue', $this->issue);
    }

    /**
     * Delete this issue.
     *
     * @return void
     */
    public function open() {
        global $DB;
        $this->issue->state = "open";
        $DB->update_record('local_qtracker_issue', $this->issue);
    }

    /**
     * Delete this issue.
     *
     * @return void
     */
    public function comment() {

        $this->comments;
        $DB->update_record('local_qtracker_issue', $this->issue);
    }

    /**
     * Delete this issue and related comments.
     *
     * @return void
     */
    public function delete() {
        global $DB;
        $comments = $this->get_comments();
        foreach ($comments as $comment) {
            $comment->delete();
        }
        $outrefs = $this->get_outgoing_references();
        foreach ($outrefs as $outref) {
            $outref->delete();
        }
        $inrefs = $this->get_incoming_references();
        foreach ($inrefs as $inref) {
            $inref->delete();
        }
        return $DB->delete_records('local_qtracker_issue', array('id' => $this->get_id()));
    }

    /**
     * Sets this issues title to $title
     *
     * @param string $title
     */
    public function set_title($title) {
        global $DB;
        $this->issue->title = $title;
        $DB->update_record('local_qtracker_issue', $this->issue);
    }

    /**
     * Sets this issues description to $title
     *
     * @param string $title
     */
    public function set_description($title) {
        global $DB;
        $this->issue->description = $title;
        $DB->update_record('local_qtracker_issue', $this->issue);
    }

    /**
     * Sets this issues contextid to $contextid
     *
     * @param string $contextid
     */
    public function set_contextid($contextid) {
        global $DB;
        $this->issue->contextid = $contextid;
        $DB->update_record('local_qtracker_issue', $this->issue);
    }
}
