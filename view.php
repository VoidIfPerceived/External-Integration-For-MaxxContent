<?php

use core_reportbuilder\external\columns\sort\get;

require_once('../../config.php');

$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('extintmaxx', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$module = $DB->get_record('extintmaxx', array('id' => $cm->instance), '*', MUST_EXIST);



require_login($course, true, $cm);
$PAGE->set_url('/mod/extintmaxx/view.php', array('id' => $cm->id));
$PAGE->set_title('External Integration for Maxx Content');
$PAGE->set_heading('pluginname', 'extintmaxx');
$PAGE->set_pagelayout('standard');



echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'extintmaxx'));

echo '<p>Welcome to the External Integration for Maxx Content plugin!</p>';

echo '<p>The API Token for this activity is: </p>' . $module->apitoken;
echo '<p>The Provider for this activity is: </p>' . $module->provider;
echo $OUTPUT->footer();
