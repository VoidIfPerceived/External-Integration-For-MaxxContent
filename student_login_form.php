<?php

use core\session\exception;

require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');
use mod_extintmaxx\providers\acci;

defined('MOODLE_INTERNAL') || die();

/**
 * Login Form for Maxx External Integration Plugin
 */

class mod_extintmaxx_student_login_form extends moodleform {
    function __construct()
    {
        //Bring view (parent) variables $provider, $module into form for later use
        global $provider, $module;
        $this->_customdata['provider'] = $provider;
        $this->_customdata['module'] = $module;
        parent::__construct();
    }
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

        // Add a text field for the case number
        $mform->addElement('text', 'casenumber', get_string('studentcasenumber', 'extintmaxx'));
        $mform->setType('casenumber', PARAM_TEXT);
        $mform->addHelpButton('casenumber', 'studentcasenumber', 'extintmaxx');

        // Add a button to redirect to the enroll page
        $mform->addElement('button', 'newuserenroll', get_string('newuserenroll', 'extintmaxx'), array('onclick' => "document.location.href=\"/mod/extintmaxx/student_enroll_form.php\""));

        // Add a submit button
        $this->add_action_buttons(false, get_string('existinguserenroll', 'extintmaxx'));
    }

    function validation($data, $files) {
        $errors = array();

        // Validate the email field
        if (empty($data['email'])) {
            $errors['email'] = get_string('required');
        } else if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = get_string('invalidemail', 'extintmaxx');
        }

        // Validate the password field
        if (empty($data['password'])) {
            $errors['password'] = get_string('required');
        }

        return $errors;
        
    }

    function handling($mform) {
        $acci = new acci;
        global $USER, $DB;
        $currentuser = new stdClass();
        if ($mform->is_cancelled()) {
            
        } else if ($formdata = $mform->get_data()) {
            $adminlogin = $acci->admin_login($this->_customdata['provider']->providerusername, $this->_customdata['provider']->providerpassword);
            $remembertoken = $adminlogin->data->user->remember_token;
            $guid = $this->_customdata['module']->providercourseguid;
            $currentuser->email = $formdata->studentemail;
            $currentuser->password = $formdata->studentpassword;
            $currentuser->casenumber = $formdata->casenumber;
            $currentuser->userid = $USER->id;
            $currentuser->firstname = $USER->firstname;
            $currentuser->lastname = $USER->lastname;
            $currentuser->provider = $this->_customdata['module']->provider;
            $loggedinuser = $acci->student_self_enrolled($remembertoken, $currentuser->firstname, $currentuser->lasstname, $currentuser->email, $guid, $currentuser->casenumber);
            $currentuser->provideruserid = $loggedinuser->data->student->id;
            $currentuser->usertoken = $loggedinuser->data->token;
            $currentuser->userremembertoken = $loggedinuser->data->remember_token;
            $currentuser->redirectUrl = $loggedinuser->data->redirectUrl;
            $DB->insert_record('extintmaxx_user', $currentuser);

            return $currentuser->redirectUrl;
        } else {
            
        }
    }
}

$mform = new mod_extintmaxx_student_login_form();