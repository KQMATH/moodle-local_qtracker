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

    //$contexts = new question_edit_contexts($context);
    //if ($contexts->have_one_edit_tab_cap('questions')) {
    $qtrackernode->add(get_string('issues', 'local_qtracker'), new moodle_url(
        $CFG->wwwroot . '/local/qtracker/view.php',
        $params
    ), navigation_node::TYPE_SETTING, null, 'issues');
    //}
}

function qtracker_get_view($calendar, $view, $includenavigation = true, bool $skipevents = false) {
}
