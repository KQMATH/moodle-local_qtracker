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
 * @package     local_qtracker
 * @author      André Storhaug <andr3.storhaug@gmail.com>
 * @copyright   2020 NTNU
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_qtracker_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    //TODO perform upgrades here...
    if ($oldversion < 2020070800) {
        // Define table capquiz_user_rating to be created.
        $table = new xmldb_table('qtracker_issue');

        $field = new xmldb_field(
            'userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, -1);
        $key = new xmldb_key(
            'userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }
        upgrade_mod_savepoint(true, 2020070800, 'capquiz');
    }
    return true;
}
