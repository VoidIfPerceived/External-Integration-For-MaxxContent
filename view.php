<?php

use core\analytics\analyser\student_enrolments;
use core\session\exception;
use core_reportbuilder\external\columns\sort\get;
use mod_extintmaxx\providers\acci;
use mod_extintmaxx\providers\provider_api_method_chains;
use mod_extintmaxx\task\acci_grade_check;

require_once(__DIR__ . '/../../config.php');
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
$providercourse = $methodchains->provider_record_exists($provider->provider, $module->providercourseid);
$providerstudent = $methodchains->student_login($USER->id, $provider->provider, $module);

$PAGE->set_context(context_system::instance());

function update_completion_data($provider) {
    if ($provider == 'acci') {
        $accigradecheck = new acci_grade_check();
        echo "acci gradecheck through adhoc:";
        \core\task\manager::queue_adhoc_task($accigradecheck, true);
        echo "acci gradecheck through direct execute:";
        $accigradecheck->execute();
    }
}

function student_view($redirecturl) {
    if ($redirecturl == 'invalidlogin') {
        $viewurl = "<h2>Invalid Login, Please Log In.</h2>";
        return $viewurl;
    } else {
        $viewurl = "<iframe style=\"position: relative; top: 0; right: 0; bottom: 0; left: 0\" src=\"$redirecturl\" width=\"100%\" height=\"1200px\"></iframe>";
        return $viewurl;
    }
}

$PAGE->set_url('/mod/extintmaxx/view.php', array('id' => $cm->id));
$PAGE->set_title('External Integration for Maxx Content');
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
if (isguestuser() == true) {
    $redirecturl = 'invalidlogin';
} else {
    $redirecturl = $providerstudent->redirecturl;
}
echo update_completion_data('acci');

echo student_view($redirecturl);

echo $OUTPUT->footer();