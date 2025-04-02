<?php
namespace mod_extintmaxx\providers;
require_once("{$CFG->libdir}/filelib.php");
//Use required API.php files

use coding_exception;
use core\session\exception;
use mod_extintmaxx\providers\acci;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class provider_api_method_chains {
    function __construct() {
        
    }

    /** Universal Methods */
    /** Checks whether a database entry for the provided provider exists within the plugin 
     *  @param string $provider The supplied provider the DB will be checked for.
     *  @return object $providerdata The DB response object with the provider information requested.
     */
    function provider_record_exists($provider) {
        global $DB;
        $providerrecord = $DB->get_record('extintmaxx_admin', array('provider' => $provider));
        if (!$providerrecord->provider) {
            return false;
        } else if ($providerrecord->provider) {
            return $providerrecord;
        }
    }
    /** Checks whether a database entry for the provided student exists within the plugin
     * @param int $userid The supplied id for the user the DB will be checked for.
     * @param string $provider The supplied provider the DB will check the user for.
     * @todo @param int $courseid The supplied course id the DB will check the user for.
     * @return object $student Array Containing the retrieved DB object of user and a bool of the validity of the request 
     */
    function student_record_exists($userid, $provider) {
        global $DB;
        $studentdata = $DB->get_record(
            'extintmaxx_user',
            array(
                'userid' => $userid,
                'provider' => $provider
            )
        );
        /** @todo $studentdata->courseid Allow users to be enrolled in more than one course within a given courseid */
        if (!$studentdata) {
            $student = array(
                'student' => null,
                'valid' => false
            );
            return $student;
        } else if ($studentdata->userid && $studentdata->provider) {
            $student = array(
                'student' => $studentdata,
                'valid' => true
            );
            return $student;
        }
    }

    function random_string($length = 24, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-?!@#$^*()') {
        if ($length < 1) {
            throw new coding_exception("Length must be a positive integer");
        }
        $pieces = array();
        $maxlength = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; $i++) {
            $pieces [] = $keyspace[random_int(0, $maxlength)];
        }
        return implode('', $pieces);
    }

    /** ACCI Method Chains */
    /** 
     * @param object $adminlogin admin_login() method and params or object returned from admin_login()
     * @param object $provider Provider object returned from provider_record_exists() method
     * @return object $newstudentrecord Copy of record data inserted into DB
    */
    function enroll_student($adminlogin, $provider) {
        global $DB, $USER;
        $acci = new acci();
        //Parse initial data
        $admintoken = $adminlogin->data->token;
        $adminid = $adminlogin->data->user->id;
        /** @todo $provider->statecode Field does not exist within Database */
        // $statecode = $provider->statecode;
        /** @todo $statecode Add (US) State Code to Admin Form */
        $statecode = 'GA';

        $referraltypes = $acci->get_referral_types_by_admin($admintoken);
        $referraltypeid = $referraltypes->data[0]->referraltype_id;

        $getallcourses = $acci->get_all_courses($admintoken, $referraltypeid);
        $getagencies = $acci->get_agency_by_state_id($admintoken, $statecode);
        /** @todo $courseid Add Course Selector to Mod Form */
        $courseid = $getallcourses->data[0]->course_id;
        $agencyid = $getagencies->data[0]->id;

        $password = random_string();

        $enrolledstudent = $acci->new_student_enrollment(
            $admintoken,
            $USER->firstname,
            $USER->lastname,
            $USER->email,
            $password,
            $password,
            $adminid,
            $agencyid,
            $referraltypeid,
            $courseid,
            $USER->id
        );

        $newstudentrecord = new stdClass;
        $newstudentrecord->provider = $provider->provider;
        $newstudentrecord->userid = $USER->id;
        $newstudentrecord->redirecturl = $enrolledstudent->data->redirectUrl;
        $newstudentrecord->provideruserid = $enrolledstudent->data->student->id;
        /** @todo Add DB Fields for following properties */
        // $newstudentrecord->password = $password;
        // $newstudentrecord->studenttoken = $enrolledstudent->data->token;
        // $newstudentrecord->studentremembertoken = $enrolledstudent->data->remember_token;
        // $newstudentrecord->mobileredirecturl = $enrolledstudent->data->mobileRedirectUrl;

        $DB->insert_record('extintmaxx_user', $newstudentrecord);
        return $newstudentrecord;
    }

    /** 
     * @param int $userid
     * @param string $provider
     * @return object $redirecturl
     */
    function student_login($userid, $provider) {
        $acci = new acci();
        $providerrecord = $this->provider_record_exists($provider);
        $studentrecord = $this->student_record_exists($userid, $provider);
        if ($providerrecord && !$studentrecord['student']) {
            //If user has an entry for this provider in the database
            //Return student info
            return $this->enroll_student($acci->admin_login($providerrecord->providerusername, $providerrecord->providerpassword), $providerrecord);
        } else if ($providerrecord && $studentrecord['student']) {
            return $studentrecord['student'];
            //If user does not have an entry for this provider in the database
            //Parse new data and submit to API 
        }
    }
}