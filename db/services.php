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
 * This file contains the library of functions and constants for the lti module
 *
 * @package mod_lti
 * @copyright  2009 Marc Alier, Jordi Piguillem, Nikolas Galanis
 *  marc.alier@upc.edu
 * @copyright  2009 Universitat Politecnica de Catalunya http://www.upc.edu
 * @author     Marc Alier
 * @author     Jordi Piguillem
 * @author     Nikolas Galanis
 * @author     Chris Scribner
 * @copyright  2015 Vital Source Technologies http://vitalsource.com
 * @author     Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// We defined the web service functions to install.
$functions = array(
    'local_qtracker_new_issue' => array(
        'classname'   => 'local_qtracker_external',
        'methodname'  => 'new_issue',
        'classpath'   => 'local/qtracker/externallib.php',
        'description' => 'Register a new question issue.',
        'type'        => 'write',
        'ajax'         => true,
        //'capabilities' => 'moodle/course:managegroups',
        'capabilities' => array(),   // capabilities required by the function.

    )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Question tracker service' => array(
            'functions' => array ('local_qtracker_new_issue'),
            'restrictedusers' => 0,
            'enabled'=>1,
    )
);
