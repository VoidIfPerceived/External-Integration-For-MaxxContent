<?php

use core\session\exception;

require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Login Form for Maxx External Integration Plugin
 */

class mod_extintmaxx_student_login_form extends moodleform {
    function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        // Add a text field for the email
        $mform->addElement('text', 'email', get_string('studentemail', 'extintmaxx'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->addHelpButton('email', 'studentemail', 'extintmaxx');

        // Add a password field for the password
        $mform->addElement('passwordunmask', 'password', get_string('studentpassword', 'extintmaxx'));
        $mform->setType('password', PARAM_TEXT);
        $mform->addHelpButton('password', 'studentpassword', 'extintmaxx');

        // Add a submit button
        $this->add_action_buttons(false, get_string('login', 'extintmaxx'));
    }
}

if ($mform->is_cancelled()) {
    
} else if ($formdata = $mform->get_data()) {
    $studentemail = $formdata->email;
    $studentpassword = $formdata->password;

    $DB->get_record(
        'extintmaxx_student',
        ['email' => $studentemail, 'password' => $studentpassword],
        '*',
        MUST_EXIST
    )
} else {
    
}

echo $OUTPUT->footer();