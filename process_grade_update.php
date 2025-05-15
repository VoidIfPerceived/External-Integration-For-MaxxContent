<?php

require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '../../../config.php');

global $DB;

$courseid = $_REQUEST['id'];
$instanceid = $_REQUEST['instance'];
$userid = $_REQUEST['userid'];

$module = $DB->get_record('extintmaxx', array('id' => $instanceid), '*', MUST_EXIST);
$returnurl = new moodle_url('/course/view.php', array('id' => $courseid));

extintmaxx_update_grades($module, $userid);

redirect($returnurl);