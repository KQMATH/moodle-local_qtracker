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
 * lib
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_qtracker\issue;


/**
 * Define constants to store the referance type
 */
define('LOCAL_QTRACKER_REFERENCE_SUPERSEDED', 'superseded');


/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function local_qtracker_extend_navigation_course($navigation, $course, $context) {
    global $CFG;

    if ($context->contextlevel == CONTEXT_COURSE) {
        $params = array('courseid' => $context->instanceid);
    } else if ($context->contextlevel == CONTEXT_MODULE) {
        $params = array('cmid' => $context->instanceid);
    } else {
        return;
    }

    $qtrackernode = $navigation->add(
        get_string('pluginname', 'local_qtracker'),
        null,
        navigation_node::TYPE_CONTAINER,
        null,
        'qtracker'
    );

    // TODO: Check if the user has ANY question issue context capabilities.
    $qtrackernode->add(get_string('issues', 'local_qtracker'), new moodle_url(
        $CFG->wwwroot . '/local/qtracker/view.php',
        $params
    ), navigation_node::TYPE_SETTING, null, 'issues');
}

/**
 * Check capability on category
 *
 * @param mixed $issueorid object or id. If an object is passed, it should include ->contextid and ->userid.
 * @param string $cap 'add', 'edit', 'view'.
 * @return boolean this user has the capability $cap for this issue $issue?
 */
function issue_has_capability_on($issueorid, $cap) {
    global $USER;

    if (is_numeric($issueorid)) {
        $issue = issue::load((int)$issueorid)->get_issue_obj();
    } else if (is_object($issueorid)) {
        if (isset($issueorid->contextid) && isset($issueorid->userid)) {
            $issue = $issueorid;
        }

        if (!isset($issue) && isset($issueorid->id) && $issueorid->id != 0) {
            $issue = issue::load($issueorid->id)->get_issue_obj();
        }
    } else {
        throw new coding_exception('$issueorid parameter needs to be an integer or an object.');
    }

    $context = context::instance_by_id($issue->contextid);

    // These are existing issues capabilities.
    // Each of these has a 'mine' and 'all' version that is appended to the capability name.
    $capabilitieswithallandmine = ['edit' => 1, 'view' => 1];

    if (!isset($capabilitieswithallandmine[$cap])) {
        return has_capability('local/qtracker:' . $cap, $context);
    } else {
        return has_capability('local/qtracker:' . $cap . 'all', $context) ||
            ($issue->userid == $USER->id && has_capability('local/qtracker:' . $cap . 'mine', $context));
    }
}

/**
 * Require capability on issue.
 *
 * @param mixed $issue object or id. If an object is passed, it should include ->contextid and ->userid.
 * @param string $cap 'add', 'edit', 'view'.
 *
 * @return boolean this user has the capability $cap for this issue $issue?
 */
function issue_require_capability_on($issue, $cap) {
    if (!issue_has_capability_on($issue, $cap)) {
        print_error('nopermissions', '', '', $cap);
    }
    return true;
}

/**
 * Check if reference type is valid.
 *
 * @param mixed $issue object or id. If an object is passed, it should include ->contextid and ->userid.
 * @param string $cap 'add', 'edit', 'view'.
 *
 * @return boolean this user has the capability $cap for this issue $issue?
 */
function is_reference_type(string $type) {
    $reftypes = array(LOCAL_QTRACKER_REFERENCE_SUPERSEDED);

    if (!in_array($type, $reftypes) ) {
        return false;
    }
    return true;
}

/**
 *
 *
 * @param $feature
 * @return bool true if a feature is supported
 */
function local_qtracker_supports($feature) {
    switch($feature) {
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return false;
    }
}
