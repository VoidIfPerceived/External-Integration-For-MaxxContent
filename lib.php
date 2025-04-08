<?php

use core_tag\reportbuilder\local\entities\instance;
use mod_extintmaxx\providers\provider_api_method_chains;

function extintmaxx_add_instance($instancedata, $mform = null) {
    global $DB;
    $methodchains = new provider_api_method_chains();

    $provider = $DB->get_record('extintmaxx_admin', ['provider' => $instancedata->provider], '*', MUST_EXIST);
    if (!$provider) {
        
        return false;
    }

    $selectedcourse = $methodchains->provider_record_exists($instancedata->provider, $instancedata->providercourse);

    $instancedata->providercourseid = $selectedcourse->providercourseid;
    $instancedata->providercoursename = $selectedcourse->providercoursename;
    $instancedata->name = get_string($instancedata->provider, 'extintmaxx')." - ".$instancedata->providercoursename;
    $instancedata->timecreated = time();
    $instancedata->timemodified = time();
    $instancedata->introformat = FORMAT_HTML;

    $id = $DB->insert_record('extintmaxx', $instancedata);

    return $id;

    // if ($mform->is_cancelled()) {
    //     return true;
    // }
    // else if ($fromform = $mform->get_data()) {
    //     var_dump($fromform);
    // } else {
    //     $mform->set_data($toform);
    //     $mform->display();
    // };
}

function extintmaxx_update_instance($instancedata, $mform): bool {
    global $DB;

    $instancedata->timemodified = time();
    $instancedata->id = $instancedata->instance;

    return $DB->update_record('extintmaxx', $instancedata);
}

function extintmaxx_delete_instance($id): bool {
    global $DB;

    return $DB->delete_records('extintmaxx', ['id' => $id]);
}

function extintmaxx_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE: return true;

        default: return null;
    }
}

function extintmaxx_grade_item_update($instance, $grades=NULL) {
    require_once($CFG->libdir . '/gradelib.php');

    if (property_exists($instance, 'cmidnumber')) { // May not be always present.
        $params = array('itemname' => $instance->name, 'idnumber' => $instance->cmidnumber);
    } else {
        $params = array('itemname' => $instance->name);
    }

    if ($quiz->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $instance->grade;
        $params['grademin']  = 0;

    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }
}

function extintmaxx_update_grades($instance, $userid = 0, $nullifnone = true) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if ($instance->grade == 0) {
        extintmaxx_grade_item_update($quiz);

    } else if ($grades = extintmaxx_get_user_grades($quiz, $userid)) {
        extintmaxx_grade_item_update($quiz, $grades);

    } else if ($userid && $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        extintmaxx_grade_item_update($quiz, $grade);

    } else {
        extintmaxx_grade_item_update($quiz);
    }
}

function extintmaxx_get_user_grades($instance, $userid = 0) {
    global $DB;

    if ($userid) {
        return $DB->get_record('extintmaxx_grades', ['userid' => $userid, 'extintmaxxid' => $instance->id]);
    } else {
        return $DB->get_records('extintmaxx_grades', ['extintmaxxid' => $instance->id]);
    }
}