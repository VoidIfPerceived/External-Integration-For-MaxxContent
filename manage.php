<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Settings for Maxx External Integration Plugin
 * Admin Settings:
 * Select Provider (Determines Form Requirements) : Ability to select an integrated provider from a menu
 * API Key (Read Requirements from Select Provider) : Text field for the API key of the selected provider
 * Provider Username (Read Requirements from Select Provider) : Text field for the username of the selected provider
 * Provider Password (Read Requirements from Select Provider) : Text field for the password of the selected provider
 */

class manageextintmaxx extends moodleform {
    function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        $mform->addElement('select', 'provider', get_string('providers_selection', 'extintmaxx'), array(get_string('acci', 'extintmaxx')));
        $mform->addHelpButton('provider', 'providers_selection', 'extintmaxx');

        $mform->addElement('text', 'provider_username', get_string('provider_username', 'extintmaxx'));
        $mform->setType('provider_username', PARAM_TEXT);
        $mform->addHelpButton('provider_username', 'provider_username', 'extintmaxx');

        $mform->addElement('text', 'provider_password', get_string('provider_password', 'extintmaxx'));
        $mform->setType('provider_password', PARAM_TEXT);
        $mform->addHelpButton('provider_password', 'provider_password', 'extintmaxx');

        $mform->addElement('button', 'submit', get_string('insertprovidercredentials', 'extintmaxx'));
    }
}

admin_externalpage_setup('manageextintmaxx');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('extintmaxx_settings', 'extintmaxx'));

$mform = new manageextintmaxx();

echo $mform->display();

echo $OUTPUT->footer();

