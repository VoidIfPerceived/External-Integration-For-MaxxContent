<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require('./manage_form.php');

use mod_extintmaxx\providers\provider_api_method_chains;
use mod_extintmaxx\providers\acci;

admin_externalpage_setup('manageextintmaxx');
$actionurl = new moodle_url('/mod/extintmaxx/manage_settings.php');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('extintmaxx_settings', 'extintmaxx'));

$methodchains = new provider_api_method_chains();
$acci = new acci();
$mform = new mod_extintmaxx_manage_form($actionurl);

if ($mform->is_cancelled()) {
    
} else if ($formdata = $mform->get_data()) {
    $providerusername = $formdata->providerusername;
    $providerpassword = $formdata->providerpassword;
    $provider = $formdata->provider;
    $apitoken = 0;
    // $acci->admin_login($providerusername, $providerpassword);
    // $apitoken = $acci->data->token;
    $formdata->apitoken = $apitoken;

    $providerexists = $DB->record_exists(
        'extintmaxx_admin',
        ['provider' => $provider]
    );

    if ($providerexists == true) {
        $id = $DB->get_field(
            'extintmaxx_admin',
            'id',
            ['provider' => $provider]
        );

        $formdata->id = $id;
        $formdata->timemodified = time();

        $id = $DB->update_record(
            'extintmaxx_admin',
            $formdata
        );

        $methodchains->update_provider_courses($provider);

        $mform->display();
        $time = new DateTime("now", new DateTimeZone('UTC'));
        $time->format("F j, Y, g:i a T");
        echo "<br><h4>Provider Information Updated. ".$time->format("F j, Y, g:i:s a T")."</h4>";
    } else {
        $formdata->timecreated = time();
        $formdata->timemodified = time();
        $id = $DB->insert_record(
            'extintmaxx_admin',
            $formdata
        );

        $methodchains->update_provider_courses($provider);

        $mform->display();
        $time = new DateTime("now", new DateTimeZone('America'));
        $time->format("F j, Y, g:i a T");
        echo "<br><h4>New Provider Information Received. $time</h4>";
    }
} else {
    $provider = 'acci';

    $providerexists = $DB->record_exists(
        'extintmaxx_admin',
        ['provider' => $provider]
    );

    if ($providerexists == true) {
        $record = $DB->get_record(
            'extintmaxx_admin',
            ['provider' => $provider]
        );
        $providerusername = $record->providerusername;
        $providerpassword = $record->providerpassword;
    } else {
        $providerusername = '';
        $providerpassword = '';
    }
    $toform = array(
        'provider' => $provider,
        'providerusername' => $providerusername,
        'providerpassword' => $providerpassword
    );

    $mform->set_data($toform);
    $mform->display();
}
echo $OUTPUT->footer();