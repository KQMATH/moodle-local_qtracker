<?php

require_once('../../config.php');

global $DB, $OUTPUT, $PAGE;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);

// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);
$url = new moodle_url('/local/qtracker/view.php', array('courseid' => $courseid));

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'local_qtracker'));

$settingsnode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
$editurl = new moodle_url('/blocks/simplehtml/view.php', array('id' => $id, 'courseid' => $courseid));
$editnode = $settingsnode->add(get_string('resetpage', 'my'), $editurl);
$editnode->make_active();

echo $OUTPUT->header();

// Get table renderer and display table here....
$table = new \local_qtracker\output\questions_table(uniqid(), $url);
$renderer = $PAGE->get_renderer('local_qtracker');
$questionspage = new \local_qtracker\output\questions_page($table);
echo $renderer->render($questionspage);

echo $OUTPUT->footer();
