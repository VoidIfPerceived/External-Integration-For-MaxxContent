<?php

use core_reportbuilder\external\columns\sort\get;
use mod_extintmaxx\providers\provider_api_method_chains;
use mod_extintmaxx\providers\acci;
require_once(__DIR__ . '/../../config.php');

global $USER, $DB;
$methodchains = new provider_api_method_chains();
$acci = new acci();
$adminrecord = $methodchains->admin_record_exists('acci');

/** Gets the course which was passed into reporting.php */
function get_current_course($courseid) {
    global $DB;
    return $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
}

function get_all_courses() {
    global $DB;
    $courses = $DB->get_records('course');
    return $courses;
}

function get_allowed_extintmaxx_instances($caplevel, $courseid=null) {
    global $DB;
    // if ($courseid) {
    //     $instances = $DB->get_records('extintmaxx', ['course' => $courseid]);
    // } else 
    if ($caplevel == 'full') {
        $instances = $DB->get_records('extintmaxx');
    }

    return $instances;
}

function get_allowed_students($caplevel, $instances) {
    $studentsbyinstance = array();
    global $DB;
    $allstudentinfo = array();
    foreach ($instances as $instance) {
        $studentsbyinstanceid = $DB->get_records('extintmaxx_user', ['instanceid' => $instance->id]);
        array_push($allstudentinfo, $studentsbyinstanceid);
    }
    while (count($studentsbyinstance) < count($allstudentinfo)) {
        foreach ($allstudentinfo[count($studentsbyinstance)] as $student) {
            array_push($studentsbyinstance, $student);
        }
    }
    return $studentsbyinstance;
}

function parse_table_information($caplevel, $requesteddata = [], $students, $adminrecord) {
    $methodchains = new provider_api_method_chains();
    $acci = new acci();
    $tabledata = array();
    $provideruserids = array();
    foreach ($students as $student) {
        array_push($provideruserids, $student->provideruserid);
    }
    $courses = get_all_courses();
    $instancesbycourseid = array();
    foreach ($courses as $course) {
        $instances = get_allowed_extintmaxx_instances($caplevel, $course);
        array_push($instancesbycourseid, $instances);
    }
    $providercourseids = array();
    while (count($providercourseids) < count($instancesbycourseid)) {
        $providercourseid = $instances[count($providercourseids)+1]->providercourseid;
        array_push($providercourseids, $providercourseid);
    }
    $providercourseids = array_unique($providercourseids);
    $studentsbyprovidercourse = array();
    foreach ($providercourseids as $providercourseid) {
        if ($providercourseid ) {

        }
        $studentdata = $methodchains->get_students_course_data($adminrecord, 'acci', $providercourseid, $provideruserids);
        if ($studentdata) {
            array_push($studentsbyprovidercourse, $studentdata);
        }
    }
    foreach ($studentsbyprovidercourse as $studentdata) {
        foreach ($studentdata as $student) {
            $student = $student->coursedata->data;
            $rowdata = array();
            foreach ($requesteddata as $field) {
                $fieldarray = explode(':', $field);
                $x = count($fieldarray);
                if ($x < 2) {
                    $data = $student->{$fieldarray[0]};
                } else if ($x < 3) {
                    $data = $student->{$fieldarray[0]}->{$fieldarray[1]};
                } else if ($x < 4) {
                    $data = $student->{$fieldarray[0]}->{$fieldarray[1]}->{$fieldarray[2]};
                } else {
                    new Exception("Field Flooded! Specified field is too deep.");
                };
                array_push($rowdata, $data);
            }
            array_push($tabledata, table_row(false, $rowdata));
        }
    }
    return $tabledata;
}

function table_row($ishead = false, $rowdata = []) {
    $tr = ["<tr>"];
    if ($ishead == true) {
        foreach ($rowdata as $column) {
            $columnname = get_string($column, 'extintmaxx');
            $th = "<th>$columnname</th>";
            array_push($tr, $th);
        }
    } else {
        foreach ($rowdata as $column) {
            $td = "<td>$column</td>";
            array_push($tr, $td);
        }
    }
    array_push($tr, "</tr>");
    return $tr;
}

/** @param array $columns Contains the values that are used as column titles as well as search terms */
/** @param array $data More specific than columns, contains objects which consist of a column and a piece of data to search for */
function render_data_table($caplevel, $adminrecord, $columns = [], $data = []) {
    $acci = new acci();
    $table = ["<table>"];
    $rowdata = parse_table_information(
        $caplevel,
        $columns, 
        get_allowed_students($caplevel, get_allowed_extintmaxx_instances($caplevel)), 
        $acci->admin_login($adminrecord->providerusername, $adminrecord->providerpassword)
    );
    $th = table_row(true, $columns);
    foreach ($th as $header) {
        array_push($table, $header);
    }
    foreach ($rowdata as $object) {
        foreach ($object as $column) {
            array_push($table, $column);
        }
    }
        //Need length of columns array
    
    array_push($table, "</table>");
    return $table;
}

echo $OUTPUT->header();

$courseid = $_GET['courseid'];

$PAGE->set_heading(get_string('reporting', 'extintmaxx'));

if (has_capability('mod/extintmaxx:fullreporting', $context = context_system::instance())) {
    $PAGE->set_context($context);
    $PAGE->set_url('/mod/extintmaxx/reporting.php');
    $caplevel = 'full';
} else if (has_capability('mod/extintmaxx:basicreporting', $context = context_course::instance($courseid))) {
    $PAGE->set_context($context);
    $caplevel = 'basic';
} else {
    throw new required_capability_exception('', '', 'unable to view reports page', '');
}

$searchcolumns = [
    'firstname',
    'lastname',
    'email',
    'studentcourses:student_id',
    'studentcourses:course_id',
    'studentcourses:percentage_completed',
    'studentcourses:course:title'
];

echo implode("", render_data_table($caplevel, $adminrecord, $searchcolumns));

echo $OUTPUT->footer();