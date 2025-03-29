<?php

use core_tag\reportbuilder\local\entities\instance;

function extintmaxx_add_instance($instancedata, $mform = null) {
    global $DB;

    $provider = $DB->get_record('extintmaxx_admin', ['provider' => $instancedata->provider], '*', MUST_EXIST);
    if (!$provider) {
        
        return false;
    }


    $instancedata->name = 'View '.get_string($instancedata->provider, 'extintmaxx').' Courses';
    $instancedata->timecreated = time();
    $instancedata->timemodified = time();
    $instancedata->introformat = FORMAT_HTML;

    $id = $DB->insert_record('extintmaxx', $instancedata);
    var_dump($id);

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