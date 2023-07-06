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
 * Display qtracker question page
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/qtracker/lib.php');

global $DB, $OUTPUT, $PAGE;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$manuallySorted = isset($_GET['tsort']);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}
$context = \context_course::instance($course->id);

require_login($course);
require_capability('local/qtracker:viewall', $context);

$url = new \moodle_url('/local/qtracker/issues.php', array('courseid' => $courseid));

$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading(get_string('pluginname', 'local_qtracker'));


$issuesnode = $PAGE->navbar->add(
    get_string('pluginname', 'local_qtracker'),
    null, \navigation_node::TYPE_CONTAINER, null, 'qtracker'
);
$issuesnode->add(
    get_string('issues', 'local_qtracker'),
    new \moodle_url('/local/qtracker/view.php', array('courseid' => $courseid))
);
$issuesnode->add(get_string('allissues', 'local_qtracker'));


echo $OUTPUT->header();

// Get table renderer and display table.
$table = new \local_qtracker\output\question_issues_table(uniqid(), $url, $context, $manuallySorted);
$renderer = $PAGE->get_renderer('local_qtracker');
$questionspage = new \local_qtracker\output\question_issues_page($table, $courseid);
echo $renderer->render($questionspage);

echo $OUTPUT->footer();
