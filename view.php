<?php

require_once('../../config.php');

global $DB, $OUTPUT, $PAGE;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}


require_login($course);
$PAGE->set_url('/local/qtracker/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'local_qtracker'));

//$simplehtml = new simplehtml_form();
$settingsnode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
$editurl = new moodle_url('/blocks/simplehtml/view.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('resetpage', 'my'), $editurl);
$editnode->make_active();

echo $OUTPUT->header();
//$simplehtml->display();


echo "omg it works";
// Get table renderer and display table here....

echo $OUTPUT->footer();

?>
