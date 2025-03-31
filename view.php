<?php

use core\analytics\analyser\student_enrolments;
use core_reportbuilder\external\columns\sort\get;
use mod_extintmaxx\providers\acci;

require_once('../../config.php');
require('./student_login_form.php');
require('../extintmaxx/student_enroll_form.php');
//Instance View Page

/**
 * Variable Declaration:
 * $acci = instanciate acci class from acci.php
 * $cmid = id of course module (**************Predifined?)
 * $cm = get course module from id
 */

$acci = new acci();
$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('extintmaxx', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$module = $DB->get_record('extintmaxx', array('id' => $cm->instance), '*', MUST_EXIST);
$provider = $DB->get_record('extintmaxx_admin', array('provider' => $module->provider), '*');

function provider_exists ($module, $provider) {
    if (!$provider) {
        throw new dml_read_exception(
            'missingproviderinfo',
            "\$DB->get_record('extintmaxx_admin', array('provider' => $module->provider), '*')"
        );
        echo "No information found for $module->provider, please contact your teacher.<br>";
    } else {
        return true;
    }
}

function student_exists ($module) {
    global $DB, $USER;
    // Get data of current user from extintmaxx_user table
    $currentuser = $DB->get_record(
        'extintmaxx_user',
        [
            'userid' => $USER->id,
            'provider' => $module->provider
        ],
        '*'
    );
    // Check $currentuser has data, if yes, return it, if no return false
    return $currentuser ? $currentuser : false;
}

/**
 * @return object $enrollform
 */
function student_login($module, $provider, $cmid, $cm) {
    $currentstudent = student_exists($module);
    if ($currentstudent) {
        return $currentstudent->redirecturl;
    }
    $customdata = array(
        'module' => $module,
        'provider' => $provider,
        'cmid' => $cmid
    );
    $actionurl = new moodle_url('/mod/extintmaxx/view.php', array('id' => $cmid));
    $enrollform = new mod_extintmaxx_student_enroll_form($actionurl, $customdata);
    if ($enrollform->is_cancelled()) {
        redirect(new moodle_url('/mod/extintmaxx/view.php', ['id' => $cmid]));
    } else if ($formdata = $enrollform->get_data()) {
        $enrollform->handling($formdata);
        $cmid = $formdata->cmid;
    } else { $enrollform->display(); }
}

function student_view($module, $provider, $redirecturl) {
    $viewurl = "<iframe style=\"position: relative; top: 0; right: 0; bottom: 0; left: 0\" src=\"$redirecturl\" width=\"100%\" height=\"1200px\"></iframe>";
    return $viewurl;
}

// require_login($course, true, $cm);
$PAGE->set_url('/mod/extintmaxx/view.php', array('id' => $cm->id));
$PAGE->set_title('External Integration for Maxx Content');
$PAGE->set_heading('pluginname', 'extintmaxx');
$PAGE->set_pagelayout('standard');


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'extintmaxx'));
// echo '<iframe style="position: relative; top: 0; right: 0; bottom: 0; left: 0" src="https://developer.lifeskillslink.com/api/documentation#/Admin%20Referral%20Type%20Detail/getAllCourses" width="1200px" height="1000px"></iframe><br>';

$studentexists = student_exists($module);

if ($studentexists) {
    $url = student_login($module, $provider, $cmid, $cm);
    student_view($module, $provider, $url);
} else {
    student_login($module, $provider, $cmid, $cm);
}

echo $OUTPUT->footer();
