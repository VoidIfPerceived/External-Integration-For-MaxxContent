<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
use mod_extintmaxx\providers\acci;

defined('MOODLE_INTERNAL') || die();

/**
 * Settings for Maxx External Integration Plugin
 * Admin Settings:
 * Select Provider (Determines Form Requirements) : Ability to select an integrated provider from a menu
 * API Key (Read Requirements from Select Provider) : Text field for the API key of the selected provider
 * Provider Username (Read Requirements from Select Provider) : Text field for the username of the selected provider
 * Provider Password (Read Requirements from Select Provider) : Text field for the password of the selected provider
 */

class mod_extintmaxx_manage_form extends moodleform {
    function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        $provideroptions = array(
            'acci' => get_string('acci', 'extintmaxx'),
            'nali' => get_string('nali', 'extintmaxx')
        );
        $mform->addElement('select', 'provider', get_string('providersselection', 'extintmaxx'), $provideroptions);
        $mform->addHelpButton('provider', 'providersselection', 'extintmaxx');

        $mform->addElement('text', 'providerusername', get_string('providerusername', 'extintmaxx'));
        $mform->setType('providerusername', PARAM_TEXT);
        $mform->addHelpButton('providerusername', 'providerusername', 'extintmaxx');

        $mform->addElement('text', 'providerpassword', get_string('providerpassword', 'extintmaxx'));
        $mform->setType('providerpassword', PARAM_TEXT);
        $mform->addHelpButton('providerpassword', 'providerpassword', 'extintmaxx');

        $this->add_action_buttons(
            false,
            get_string('insertprovidercredentials', 'extintmaxx')
        );
        
        // $mform->addElement('submit', 'mod_extintmaxx_manage_form', get_string('insertprovidercredentials', 'extintmaxx'));
    }

    function process_manage_form($formdata, $mform = null) {
        $formdata;
    }
}

admin_externalpage_setup('manageextintmaxx');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('extintmaxx_settings', 'extintmaxx'));

// $acci = new acci();
$mform = new mod_extintmaxx_manage_form();

if ($mform->is_cancelled()) {
    
} else if ($formdata = $mform->get_data()) {
    $providerusername = $formdata->providerusername;
    $providerpassword = $formdata->providerpassword;
    $provider = $formdata->provider;
    $apitoken = 0;
    // $acci->admin_login($providerusername, $providerpassword);
    // $apitoken = $acci->data->token;
    $formdata->apitoken = $apitoken;

    var_dump($providerusername, $providerpassword);

    echo '<br>';

    // var_dump($acci);

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

        return $DB->update_record(
            'extintmaxx_admin',
            $formdata
        );
    } else {
        $formdata->timecreated = time();
        $formdata->timemodified = time();
        $id = $DB->insert_record(
            'extintmaxx_admin',
            $formdata
        );

        echo $id;
        return $id;
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

