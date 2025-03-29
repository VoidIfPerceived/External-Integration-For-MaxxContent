<?php

use core_reportbuilder\external\columns\sort\get;
use mod_extintmaxx\providers\acci;

require_once('../../config.php');
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
        echo "No information found for ".$module->provider."<br>";
    } else {
        return true;
    }
}

function student_login($module, $provider) {
    $acci = new acci();
    
    $adminlogin = $acci->admin_login($provider->providerusername, $provider->providerpassword);
    echo "<br>";
    // Parse Admin Token
    $admintoken = $adminlogin->data->token;
    echo "Admin Token: $admintoken <br>";
    echo "<br>";
    $referraltypes = $acci->get_referral_types_by_admin($admintoken);
    echo "<br>";
    $referralid = $referraltypes->data[0]->referraltype->id;
    echo "Referral ID: $referralid <br>";
    echo "<br>";

    $getallcourses = $acci->get_all_courses($admintoken, $referralid);

    $getstudentsbyadmin = $acci->get_students_by_admin($admintoken);
    echo "All Students: ".json_encode($getstudentsbyadmin)."<br>";
}

function student_view($module, $provider, $admintoken, $studenttoken) {

}

require_login($course, true, $cm);
$PAGE->set_url('/mod/extintmaxx/view.php', array('id' => $cm->id));
$PAGE->set_title('External Integration for Maxx Content');
$PAGE->set_heading('pluginname', 'extintmaxx');
$PAGE->set_pagelayout('embedded');


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'extintmaxx'));
echo '<iframe style="position: relative; top: 0; right: 0; bottom: 0; left: 0" src="https://developer.lifeskillslink.com/api/documentation#/Admin%20Referral%20Type%20Detail/getAllCourses" width="100%" height="1000px"></iframe><br>';

provider_exists($module, $provider);
student_login($module, $provider);

echo $OUTPUT->footer();
