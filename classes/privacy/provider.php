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
        // The table 'qtracker_issue' stores a record for each qtracker issue.
        // It contains a userid which links to the user that created the issue and contains information about that issue.
        $items->add_database_table('qtracker_issue', [
            'userid' => 'privacy:metadata:qtracker_issue:userid',
            'title' => 'privacy:metadata:qtracker_issue:title',
            'description' => 'privacy:metadata:qtracker_issue:description',
            'timecreated' => 'privacy:metadata:qtracker_issue:timecreated'
        ], 'privacy:metadata:qtracker_issue');

        // The table 'qtracker_comment' stores a record of each issue comment.
        // It contains a userid which links to the user that created the comment and contains information about that comment.
        $items->add_database_table('qtracker_comment', [
            'userid' => 'privacy:metadata:qtracker_comment:userid',
            'description' => 'privacy:metadata:qtracker_comment:description',
            'timecreated' => 'privacy:metadata:qtracker_comment:timecreated'
        ], 'privacy:metadata:qtracker_comment');

        return $items;
    }

    /**
     * Get the list of contexts where the specified user has attempted a capquiz.
     *
     * @param int $userid The user to search.
     * @return  contextlist  $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {

        // TODO: select all from table qtracker_issue and qtracker_comment left join? on issueid (comments table) to get all contextids stored in the qtracker_issue table.
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
        // TODO: Export all data from all issues with appropriate context id, but first delete all comments with correct contextid (linked in qtracker_issue table).
        global $DB;
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        // TODO: for ALL USERS : delete all issues with appropriate context id, but first delete all comments with correct contextid (linked in qtracker_issue table).
        global $DB;
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // TODO: for one specific user, delete all issues with appropriate context id, but first delete all comments with correct contextid (linked in qtracker_issue table).

        global $DB;

    }
}
