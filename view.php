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

$PAGE->set_context(context_system::instance());

function admin_actions($courseid) {
    $reportingurl = "/mod/extintmaxx/reporting.php?courseid=$courseid";
    echo "<a href='$reportingurl'>View Reporting</a>";
}

function view_reporting() {

}

function return_to_course_url() {

}

function acci_course_url($providerstudent, $providercourse, $providerrecord) {
    $methodchains = new provider_api_method_chains();
    $acci = new acci();
    $studentcoursedata = $methodchains->get_students_course_data($acci->admin_login($providerrecord->providerusername, $providerrecord->providerpassword), $providerrecord->provider, $providercourse->providercourseid, [$providerstudent->provideruserid]);
    $studentcompletion = $studentcoursedata[0]->coursedata->data->studentcourses->percentage_completed;
    if ($studentcompletion > 0) {
        $studentcourses = $studentcoursedata[0]->coursedata->data->studentcourses;
        $currentframeid = $studentcourses->frame_id;
        $nextframeid = $studentcourses->next_frame_id;
        $previousframeid = $studentcourses->previous_frame_id;
        $courseforwardurl = "https://www.lifeskillslink.com/studentcourse?id=$providercourse->providercourseid&fid=$currentframeid&next_frame_id=$nextframeid&previous_frame_id=$previousframeid";
    } else {
        $courseforwardurl = "https://www.lifeskillslink.com/studentcourse?id=$providercourse->providercourseid&student_id=$providerstudent->provideruserid";
    }

    return $courseforwardurl;
}

function get_redirect_url($providerstudent) {
    if (isguestuser() == true) {
        $redirecturl = 'invalidlogin';
    } else {
        $redirecturl = $providerstudent->redirecturl;
    }
    return $redirecturl;
}

function update_completion_data($provider) {
    if ($provider == 'acci') {
        $accigradecheck = new acci_grade_check();
        echo "acci gradecheck through adhoc:";
        \core\task\manager::queue_adhoc_task($accigradecheck, true);
        echo "acci gradecheck through direct execute:";
        $accigradecheck->execute();
    }
}

function generate_iframe($redirecturl, $courseforwardurl) {
    if ($redirecturl == 'invalidlogin') {
        $viewurl = "<h2>Invalid Login, Please Log In.</h2>";
        return $viewurl;
    } else {
        echo $viewurl = 
        "<div style=\"
            position: relative; 
            overflow:hidden;
            position:relative;
            top:0px;
            width:100%;
            height:740px;\"
        >
            <iframe id=\"viewurl\" 
                style=\"
                position:absolute;
                top:-67px;
                height:740px;
                width:100%;
                left:0;
                scrolling:no;\"
                src=\"$redirecturl\">
            </iframe>
        </div>
        ";
    }
}

function iframe_course_redirect($courseforwardurl) {
    return "<script>
            iframe = document.getElementById('viewurl');
            addEventListener('load', function() {
                iframe = iframe.contentWindow.location.href = '$courseforwardurl';
            });
            </script>";
    // return "<script>
    //             var iframe = document.getElementById('viewurl');
    //             var hasRedirected = false;
                
    //             if (hasRedirected == false) {
    //                 iframe.onload = function() {
    //                     if (hasRedirected == false) {
    //                         iframe.contentWindow.location.href = '$courseforwardurl';
    //                     }
    //                 }
    //                 hasRedirected = true;
    //             }
    //         });
    //         </script>";
}

function view_page($redirecturl, $courseforwardurl) {
    $iframe = generate_iframe($redirecturl, $courseforwardurl);
    return $iframe;
}

function exit_activity_button($courseid, $instanceid) {
    global $USER;
    $returnurl = new moodle_url("/mod/extintmaxx/process_grade_update.php", array('id' => $courseid, 'instance' => $instanceid, 'userid' => $USER->id));
    return "<a href=\"$returnurl\" style=\"
    position:relative
    z-index:1
    \"
    class=\"btn btn-primary btn-md\">
    Exit Activity</a>";
}

$PAGE->set_url('/mod/extintmaxx/view.php', array('id' => $cm->id));
$PAGE->set_title('External Integration for Maxx Content');

echo $OUTPUT->header();

if (has_capability('mod/extintmaxx:basicreporting', $context = context_course::instance($cm->course))) {
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    admin_actions($course->id);
} else {
    $acciuserid = $DB->get_record(
        'extintmaxx_user',
        array('userid' => $USER->id, 'provider' => $provider->provider, 'instanceid' => $module->id)
    );
    $providerstudent = $methodchains->student_login($USER->id, $provider->provider, $module, $module->id);
    $redirecturl = get_redirect_url($providerstudent);
    $courseforwardurl = acci_course_url($providerstudent, $providercourse, $provider);
    $logout = $acci->student_logout($acciuserid->provideruserid, $acci->admin_login($provider->providerusername, $provider->providerpassword)->data->user->superadmin->consumer_key);
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('incourse');
    echo exit_activity_button($cm->course, $module->id);
    view_page($redirecturl, $courseforwardurl);
    echo iframe_course_redirect($courseforwardurl);
}

echo $OUTPUT->footer();