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
 * Privacy Subsystem implementation for local_qtracker.
 *
 * @package     local_qtracker
 * @author      André Storhaug <andr3.storhaug@gmail.com>
 * @copyright   2021 NTNU
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\privacy;

use coding_exception;
use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use dml_exception;
use moodle_exception;
use question_display_options;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementation for local_qtracker.
 *
 * @author      André Storhaug <andr3.storhaug@gmail.com>
 * @copyright   2021 NTNU
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin_provider interface.
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     * @param collection $items The initialised collection to add metadata to.
     * @return  collection  A listing of user data stored through this system.
     */
    public static function get_metadata(collection $items): collection {
        // The table 'local_qtracker_issue' stores a record for each qtracker issue.
        // It contains a userid which links to the user that created the issue and contains information about that issue.
        $items->add_database_table('local_qtracker_issue', [
            'userid' => 'privacy:metadata:local_qtracker_issue:userid',
            'title' => 'privacy:metadata:local_qtracker_issue:title',
            'description' => 'privacy:metadata:local_qtracker_issue:description',
            'timecreated' => 'privacy:metadata:local_qtracker_issue:timecreated'
        ], 'privacy:metadata:local_qtracker_issue');

        // The table 'local_qtracker_comment' stores a record of each issue comment.
        // It contains a userid which links to the user that created the comment and contains information about that comment.
        $items->add_database_table('local_qtracker_comment', [
            'userid' => 'privacy:metadata:local_qtracker_comment:userid',
            'description' => 'privacy:metadata:local_qtracker_comment:description',
            'timecreated' => 'privacy:metadata:local_qtracker_comment:timecreated'
        ], 'privacy:metadata:local_qtracker_comment');

        return $items;
    }

    /**
     * Get the list of contexts where the specified user has attempted a capquiz.
     *
     * @param int $userid The user to search.
     * @return  contextlist  $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {

        // TODO: select all from table local_qtracker_issue and local_qtracker_comment left join? on issueid (comments table) to get all contextids stored in the local_qtracker_issue table.
        $sql = "SELECT qi.contextid
                  FROM {local_qtracker_issue} qi
             LEFT JOIN {local_qtracker_comment} qc
                    ON qi.id = qc.issueid
                 WHERE qi.userid = :userid1
                    OR qc.userid = :userid2";
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, [
            'userid1' => $userid,
            'userid2' => $userid
        ]);
        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist)) {
            return;
        }

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "SELECT * FROM {local_qtracker_issue} WHERE contextid $contextsql";
        $issues = $DB->get_records_sql($sql, $contextparams);

        $context = null;
        foreach ($issues as $issue) {
            $context = context::instance_by_id($issue->contextid);
            // Store the quiz attempt data.
            $data = new stdClass();
            $data->title = $issue->title;
            $data->description = $issue->description;
            $data->timecreated = transform::datetime($issue->timecreated);

            $subcontext = [get_string('issues', 'local_qtracker'),
                           get_string('issue', 'local_qtracker') . ' ' . $issue->id];
            // The capquiz attempt data is organised in: {Course name}/{Qtracker}/{Issues}/{_X}/data.json
            // where X is the attempt number.
            writer::with_context($context)->export_data($subcontext, $data);
            //writer::with_context($context)->export_area_files($subcontext, 'local_qtracker', 'description', $issue->id);
        }

        $user = $contextlist->get_user();
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "SELECT qc.id AS id,
                       qi.contextid AS contextid,
                       qi.id AS issueid,
                       qc.description AS description,
                       qc.timecreated AS timecreated
                  FROM {local_qtracker_issue} qi
            INNER JOIN {local_qtracker_comment} qc
                    ON qi.id = qc.issueid
                 WHERE qc.userid = :userid
                   AND qi.contextid {$contextsql}";
        $params = [
            'userid' => $user->id
        ];
        $params += $contextparams;
        $comments = $DB->get_records_sql($sql, $params);

        $context = null;
        foreach ($comments as $comment) {
            $context = context::instance_by_id($comment->contextid);
            // Store the quiz attempt data.
            $data = new stdClass();
            $data->description = $comment->description;
            $data->timecreated = transform::datetime($comment->timecreated);

            $subcontext = [get_string('issues', 'local_qtracker'),
                           get_string('issue','local_qtracker') . ' ' . $comment->issueid,
                           get_string('comments', 'local_qtracker'),
                           get_string('comment', 'local_qtracker') . ' ' . $comment->id];
            // The issue comment data is organised in: {Course name}/{Qtracker}/{Issues}/{_X}/Comments({_Y}/data.json
            // where X is the issue id and Y is the comment id.
            writer::with_context($context)->export_data($subcontext, $data);
            //writer::with_context($context)->export_area_files($subcontext, 'local_qtracker', 'description', $comment->id);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;
        $sql = "SELECT qc.id AS id
                  FROM {local_qtracker_issue} qi
            INNER JOIN {local_qtracker_comment} qc
                    ON qc.issueid = qi.id
                 WHERE qi.contextid = :contextid";
        $params = [
            'contextid' => $context->id
        ];
        $comments = $DB->get_records_sql($sql, $params);

        foreach ($comments as $comment) {
            $DB->delete_records('local_qtracker_comment', ['id' => $comment->id]);
        }

        $sql = "SELECT id
                  FROM {local_qtracker_issue}
                 WHERE contextid = :contextid";
        $params = [
            'contextid' => $context->id
        ];
        $issues = $DB->get_records_sql($sql, $params);

        foreach ($issues as $issue) {
            $DB->delete_records('local_qtracker_issue', ['id' => $issue->id]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }
        $user = $contextlist->get_user();
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "SELECT qc.id AS id
                  FROM {local_qtracker_issue} qi
            INNER JOIN {local_qtracker_comment} qc
                    ON qc.issueid = qi.id
                 WHERE qc.userid = :userid
                   AND qi.contextid {$contextsql}";
        $params = [
            'userid' => $user->id
        ];
        $params += $contextparams;
        $comments = $DB->get_records_sql($sql, $params);

        foreach ($comments as $comment) {
            $DB->delete_records('local_qtracker_comment', ['id' => $comment->id]);
        }

        $sql = "SELECT id
                  FROM {local_qtracker_issue}
                 WHERE userid = :userid
                   AND contextid {$contextsql}";
        $params = [
            'userid' => $user->id
        ];
        $params += $contextparams;
        $issues = $DB->get_records_sql($sql, $params);

        foreach ($issues as $issue) {
            $DB->delete_records('local_qtracker_issue', ['id' => $issue->id]);
        }
    }
}
