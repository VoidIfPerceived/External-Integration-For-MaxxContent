<?php

global $CFG, $DB, $USER;
require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');

use mod_extintmaxx\providers\acci;

defined('MOODLE_INTERNAL') || die();

/**
 * Enrollment and Login Form for Maxx External Integration Plugin
 */

class mod_extintmaxx_student_enroll_form extends moodleform {
    /**
     * Form definition
     */
    function definition() {
        global $CFG;
        $mform = $this->_form;

        // Add a text field for the email
        $mform->addElement('text', 'email', get_string('studentemail', 'extintmaxx'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->addHelpButton('email', 'studentemail', 'extintmaxx');

        // Add a password field for the password
        $mform->addElement('passwordunmask', 'password', get_string('studentpassword', 'extintmaxx'));
        $mform->setType('password', PARAM_TEXT);
        $mform->addHelpButton('password', 'studentpassword', 'extintmaxx');

        // Password Confirmation Field
        $mform->addElement('passwordunmask', 'passwordconfirmation', get_string('studentpasswordconfirmation', 'extintmaxx'));
        $mform->setType('passwordconfirmation', PARAM_TEXT);
        $mform->addHelpButton('passwordconfirmation', 'studentpasswordconfirmation', 'extintmaxx');

        // Add a text field for the case number
        $mform->addElement('text', 'casenumber', get_string('studentcasenumber', 'extintmaxx'));
        $mform->setType('casenumber', PARAM_TEXT);
        $mform->addHelpButton('casenumber', 'studentcasenumber', 'extintmaxx');

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        // Add a submit button
        $this->add_action_buttons(false, get_string('newuserenroll', 'extintmaxx'));

        // $mform->setDefault('email',$this->_customdata['email']);
    }

    /**
     * Form validation
     */
    function validation($data, $files) {
        global $USER;
        $errors = array();

        // Validate the email field
        if (empty($data['email'])) {
            $errors['email'] = get_string('required');
        } else if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = get_string('invalidemail', 'extintmaxx');
        } else if ($data['email'] !== $USER->email) {
            $errors['email'] = get_string('emailmismatch', 'extintmaxx');
        }

        // Validate the password field
        if (empty($data['password'])) {
            $errors['password'] = get_string('required');
        }

        // Validate the password confirmation field
        if (empty($data['passwordconfirmation'])) {
            $errors['passwordconfirmation'] = get_string('required');
        } else if ($data['password'] !== $data['passwordconfirmation']) {
            $errors['passwordconfirmation'] = get_string('passwordmismatch', 'extintmaxx');
        }

        return $errors;
    }

    /** 
     * Asks current user to manually enroll themself under the module instance's selected course
    */
    function enroll_student($userdata, $provider, $module) {
        global $DB, $USER;
        $acci = new acci();
        // Calling ACCI API to get necessary API information
        $adminlogin = $acci->admin_login($provider->providerusername, $provider->providerpassword);
        $admintoken = $adminlogin->data->token;
        $adminid = $adminlogin->data->user->id;
        $referraltypes = $acci->get_referral_types_by_admin($admintoken);
        $referralid = $referraltypes->data[0]->referraltype->id;
        $getallcourses = $acci->get_all_courses($admintoken, $referralid);
        $getagencies = $acci->get_agency_by_state_id($admintoken, "GA");
        print_object($getagencies);
        $agencyid = $getagencies->data[0]->id;
        $courseid = $getallcourses->data[0]->course_id;
        // Call ACCI API to enroll new student
        $newuser = $acci->new_student_enrollment(
            $admintoken,
            $userdata->firstname,
            $userdata->lastname,
            $userdata->email,
            $userdata->password,
            $userdata->passwordconfirmation,
            $adminid,
            $agencyid,
            $referralid,
            $courseid
        );
        // Insert the new user into the extintmaxx_user table
            $userrecord = new stdClass;
            $userrecord->userid = $USER->id;
            $userrecord->provideruserid = $newuser->data->student->id;
            // $userrecord->usertoken = $newuser->data->token;
            $userrecord->userremembertoken = $newuser->data->remember_token;
            $userrecord->redirecturl = $newuser->data->redirectUrl;
            $userrecord->provider = $module->provider;
        $DB->insert_record('extintmaxx_user', $userrecord);
        return $newuser->data->redirectUrl;
    }

    /**
     * Handles the form submission
     */
    function handling($formdata) {
        global $USER;
        $newuser = new stdClass();
        $studentemail = $formdata->email;
        $studentpassword = $formdata->password;
        $studentpasswordconfirmation = $formdata->passwordconfirmation;
        $studentcasenumber = $formdata->casenumber;
        $module = $this->_customdata['module'];
        $provider = $this->_customdata['provider'];
        $studentexists = student_exists($module);
    
        if (!$studentexists) {
            // If student does not exist, create a new user
            $newuser->userid = $USER->id;
    
            $newuser->email = $studentemail;
            $newuser->password = $studentpassword;
            $newuser->passwordconfirmation = $studentpasswordconfirmation;
            $newuser->casenumber = $studentcasenumber;
            $newuser->firstname = $USER->firstname;
            $newuser->lastname = $USER->lastname;


            $enrollstudent = $this->enroll_student($newuser, $provider, $module);
            // Call the function to enroll the student
            // redirect(new moodle_url('/mod/extintmaxx/view.php', array('id' => $formdata->cmid)));
            return $enrollstudent;
        } else {
            // If student exists, show an error message
            throw new Exception('A student with this id is already in the plugin\'s database, please login or contact an administrator for assistance.');
        }
        if ($formdata->email !== $USER->email) {
            throw new Exception('Email does not match the logged in user.');
        };
    }
}