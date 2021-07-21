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
 * This file contains the services for the qtracter module
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// We defined the web service functions to install.
$functions = array(
    'local_qtracker_new_issue' => array(
        'classname'   => 'local_qtracker\external\new_issue',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Register a new question issue.',
        'type'        => 'write',
        'ajax'         => true,
        'capabilities' => array(),   // Capabilities required by the function.
        'loginrequired' => true,
    ),
    'local_qtracker_edit_issue' => array(
        'classname'   => 'local_qtracker\external\edit_issue',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Edit an existing question issue.',
        'type'        => 'write',
        'ajax'         => true,
        'capabilities' => array(),   // Capabilities required by the function.
        'loginrequired' => true,
    ),
    'local_qtracker_delete_issue' => array(
        'classname'   => 'local_qtracker\external\delete_issue',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Delete an existing question issue.',
        'type'        => 'write',
        'ajax'         => true,
        'capabilities' => array(),   // Capabilities required by the function.
        'loginrequired' => true,
    ),
    'local_qtracker_get_issue' => array(
        'classname'   => 'local_qtracker\external\get_issue',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Get an existing question issue.',
        'type'        => 'read',
        'ajax'         => true,
        'capabilities' => array(),   // Capabilities required by the function.
        'loginrequired' => true,
    ),
    'local_qtracker_get_question' => array(
        'classname'   => 'local_qtracker\external\get_question',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Get question by id.',
        'type'        => 'read',
        'ajax'         => true,
        'loginrequired' => true,
    ),
    'local_qtracker_get_question_preview_url' => array(
        'classname'   => 'local_qtracker\external\get_question_preview_url',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Get question preview url.',
        'type'        => 'read',
        'ajax'         => true,
        'loginrequired' => true,
    ),
    'local_qtracker_get_question_edit_url' => array(
        'classname'   => 'local_qtracker\external\get_question_edit_url',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Get question edit url.',
        'type'        => 'read',
        'ajax'         => true,
        'loginrequired' => true,
    ),
    'local_qtracker_get_issues' => array(
        'classname'   => 'local_qtracker\external\get_issues',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Get issues.',
        'type'        => 'read',
        'ajax'         => true,
        'loginrequired' => true,
    ),
    'local_qtracker_get_issue_parents' => array(
        'classname'   => 'local_qtracker\external\get_issue_parents',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Get issue parents.',
        'type'        => 'read',
        'ajax'         => true,
        'loginrequired' => true,
    ),
    'local_qtracker_get_issue_children' => array(
        'classname'   => 'local_qtracker\external\get_issue_children',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Get issue children.',
        'type'        => 'read',
        'ajax'         => true,
        'loginrequired' => true,
    ),
    'local_qtracker_set_issue_relation' => array(
        'classname'   => 'local_qtracker\external\set_issue_relation',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Set issue relation.',
        'type'        => 'write',
        'ajax'         => true,
        'capabilities' => array(),   // Capabilities required by the function.
        'loginrequired' => true,
    ),
    'local_qtracker_delete_issue_relation' => array(
        'classname'   => 'local_qtracker\external\delete_issue_relation',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Delete issue relation.',
        'type'        => 'write',
        'ajax'         => true,
        'capabilities' => array(),   // Capabilities required by the function.
        'loginrequired' => true,
    )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Question tracker service' => array(
        'functions' => array(
            'local_qtracker_new_issue',
            'local_qtracker_edit_issue',
            'local_qtracker_delete_issue',
            'local_qtracker_get_issue',
            'local_qtracker_get_issues',
            'local_qtracker_get_issue_parents',
            'local_qtracker_get_issue_children',
            'local_qtracker_set_issue_relation',
            'local_qtracker_delete_issue_relation',
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);
