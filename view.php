<?php

use core\analytics\analyser\student_enrolments;
use core_reportbuilder\external\columns\sort\get;
use mod_extintmaxx\providers\acci;
use mod_extintmaxx\providers\provider_api_method_chains;

require_once('../../config.php');
//Instance View Page

/**
 * Variable Declaration:
 * $acci = instanciate acci class from acci.php
 * $cmid = id of course module (**************Predifined?)
 * $cm = get course module from id
 */

$acci = new acci();
$methodchains = new provider_api_method_chains();

global $USER, $DB;
$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('extintmaxx', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$module = $DB->get_record('extintmaxx', array('id' => $cm->instance), '*', MUST_EXIST);
$provider = $DB->get_record('extintmaxx_admin', array('provider' => $module->provider), '*');

function student_view($redirecturl) {
    $viewurl = "<iframe style=\"position: relative; top: 0; right: 0; bottom: 0; left: 0\" src=\"$redirecturl\" width=\"100%\" height=\"1200px\"></iframe>";
    return $viewurl;
}

// require_login($course, true, $cm);
$PAGE->set_url('/mod/extintmaxx/view.php', array('id' => $cm->id));
$PAGE->set_title('External Integration for Maxx Content');
$PAGE->set_heading('pluginname', 'extintmaxx');
$PAGE->set_pagelayout('standard');

$redirecturl = $methodchains->student_login($USER->id, $provider->provider)->redirecturl;
echo student_view($redirecturl);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'extintmaxx'));

echo $OUTPUT->footer();
