<?php

use core_reportbuilder\external\columns\sort\get;
use mod_extintmaxx\providers\acci;

require_once('../../config.php');
//Instance View Page

/**
 * Varaible Declaration:
 * $acci = instanciate acci class from acci.php
 * $cmid = id of course module (**************Predifined?)
 * $cm = get course module from id
 */

$cred1 = 'wouldntyouliketoknow'; //Wouldn't you like to know?
$cred2 = 'wouldntyouliketoknow'; //Wouldn't you like to know?

$acci = new acci();
$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('extintmaxx', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$module = $DB->get_record('extintmaxx', array('id' => $cm->instance), '*', MUST_EXIST);
// $proid = $DB->get_record('extintmaxx_provider', $proid, '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url('/mod/extintmaxx/view.php', array('id' => $cm->id));
$PAGE->set_title('External Integration for Maxx Content');
$PAGE->set_heading('pluginname', 'extintmaxx');
$PAGE->set_pagelayout('standard');


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'extintmaxx'));

echo '<p>Welcome to the External Integration for Maxx Content plugin!</p>';

echo '<p>The Username for this provider is: </p>' . $module->providerusername;
echo '<p>The Password for this provider is: </p>' . $module->providerpassword;
echo '<p>The Provider you have selected is: </p>' . $module->provider;
echo '<p>The API Token for access via these credentials is: </p>' . $module->apitoken;

$response = $acci->admin_login($cred1, $cred2);

print_r($response);

echo $OUTPUT->footer();
