<?php

require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Enrollment and Login Form for Maxx External Integration Plugin
 */

class mod_extintmaxx_student_enroll_form extends moodleform {
    function definition() {
        global $CFG, $DB, $USER;
        $mform = $this->_form;

        // Add a text field for the email
        $mform->addElement('text', 'email', get_string('studentemail', 'extintmaxx'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->addHelpButton('email', 'studentemail', 'extintmaxx');

        // Add a password field for the password
        $mform->addElement('passwordunmask', 'password', get_string('studentpassword', 'extintmaxx'));
        $mform->setType('password', PARAM_TEXT);
        $mform->addHelpButton('password', 'studentpassword', 'extintmaxx');

        // Add a text field for the case number
        $mform->addElement('text', 'casenumber', get_string('studentcasenumber', 'extintmaxx'));
        $mform->setType('casenumber', PARAM_TEXT);
        $mform->addHelpButton('casenumber', 'studentcasenumber', 'extintmaxx');

        // Add a submit button
        $this->add_action_buttons(false, get_string('enroll', 'extintmaxx'));
    }

    function handling($mform) {
        global $DB;

        if($mform->is_cancelled()) {

        }
        else if ($formdata = $mform->get_data()) {

            if ($formdata->email !== $USER->email) {
                throw new Exception('Email does not match the logged in user.');
            };

            
            // Handle the form data here
            // For example, you can save it to the database or perform other actions
            // $DB->insert_record('your_table', $formdata);
        } else {
            // Display the form again if no data is submitted
        }
    }
}

if ($mform->is_cancelled()) {
    
} else if ($formdata = $mform->get_data()) {
    $studentemail = $formdata->email;
    $studentpassword = $formdata->password;

    $DB->
} else {
    
}