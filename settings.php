<?php

defined('MOODLE_INTERNAL') || die();

$settings = new admin_externalpage(
    'manageextintmaxx',
    get_string('pluginspecificheader', 'extintmaxx'),
    new moodle_url('/mod/extintmaxx/manage_form.php'),
    'moodle/site:config'
);