<?php

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

$url = new \moodle_url('/local/qtracker/tags.php', array('courseid' => $courseid));

$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading(get_string('pluginname', 'local_qtracker'));

echo $OUTPUT->header();

// Get table renderer and display table.
$table = new \local_qtracker\output\tags_table(uniqid(), $url, $context);
$renderer = $PAGE->get_renderer('local_qtracker');
$tagspage = new \local_qtracker\output\tags_page($table, $courseid);
echo $renderer->render($tagspage);

echo $OUTPUT->footer();
