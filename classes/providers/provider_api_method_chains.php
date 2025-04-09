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
    function admin_record_exists($provider) {
        global $DB;
        $adminrecord = $DB->get_record('extintmaxx_admin', array('provider' => $provider));
        if (!$adminrecord->provider) {
            return false;
        } else if ($adminrecord->provider) {
            return $adminrecord;
        }
    }
    /** Checks whether a database entry for the specified course(s) for a provider exist within the plugin */
    function provider_record_exists($provider, $course=null) {
        global $DB;
        if (!$course) {
            $providerrecord = $DB->get_records('extintmaxx_provider', array('provider' => $provider));
        } else {
            $providerrecord = $DB->get_record('extintmaxx_provider', array('provider' => $provider, 'providercourseid' => $course));
        }
        if (!$providerrecord) {
            return false;
        } else if ($providerrecord) {
            return $providerrecord;
        }
    }
    /** Checks whether a database entry for the provided student exists within the plugin
     * @param int $userid The supplied id for the user the DB will be checked for.
     * @param string $provider The supplied provider the DB will check the user for.
     * @todo @param int $courseid The supplied course id the DB will check the user for.
     * @return object $student Array Containing the retrieved DB object of user and a bool of the validity of the request 
     */
    function student_record_exists($userid, $provider, $courseid=null) {
        global $DB;
        if (!$courseid) {
            $studentdata = $DB->get_record(
                'extintmaxx_user',
                array(
                    'userid' => $userid,
                    'provider' => $provider
                )
            );
        } else {
            $studentdata = $DB->get_record(
                'extintmaxx_user',
                array(
                    'userid' => $userid,
                    'provider' => $provider,
                    'providercourseid' => $courseid
                )
            );
        }
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
     * @param object $provider Provider object returned from admin_record_exists() method
     * @return object $newstudentrecord Copy of record data inserted into DB
    */
    function enroll_student($adminlogin, $provider, $module) {
        global $DB, $USER;
        if (!isloggedin()) {
            echo "<h4>Please log in to continue.</h4>";
        }
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

        $getagencies = $acci->get_agency_by_state_id($admintoken, $statecode);

        /** @todo $courseid Add Course Selector to Mod Form */
        $courseid = $module->providercourseid;
        $agencyid = 0;

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
        );

        $newstudentrecord = new stdClass;
        $newstudentrecord->provider = $provider->provider;
        $newstudentrecord->userid = $USER->id;
        $newstudentrecord->redirecturl = $enrolledstudent->data->redirectUrl;
        $newstudentrecord->provideruserid = $enrolledstudent->data->student->id;
        $newstudentrecord->providercourseid = $courseid;
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
    function student_login($userid, $provider, $module) {
        $acci = new acci();
        $adminrecord = $this->admin_record_exists($provider);
        $studentrecord = $this->student_record_exists($userid, $provider, $module->providercourseid);
        if ($adminrecord && $studentrecord['student'] == null) {
            //If user has an entry for this provider in the database
            //Return student info
            return $this->enroll_student($acci->admin_login($adminrecord->providerusername, $adminrecord->providerpassword), $adminrecord, $module);
        } else if ($adminrecord && $studentrecord['student'] && $studentrecord['student']->providercourseid != $module->providercourseid) {
            //If user has an entry for this provider in the database
            //Return student info
            return $this->enroll_student($acci->admin_login($adminrecord->providerusername, $adminrecord->providerpassword), $adminrecord, $module);
        } else if ($adminrecord && $studentrecord['student']) {
            return $studentrecord['student'];
            //If user does not have an entry for this provider in the database
            //Parse new data and submit to API 
        }
    }

    function update_provider_courses($provider) {
        global $DB;
        $acci = new acci();
        $adminrecord = $this->admin_record_exists($provider);
        $adminlogin = $acci->admin_login($adminrecord->providerusername, $adminrecord->providerpassword);
        $admintoken = $adminlogin->data->token;
        $courses = array();

        $referral = $acci->get_referral_types_by_admin($admintoken)->data[0]->referraltype_id;
        $getallcourses = $acci->get_all_courses($admintoken, $referral);

        foreach ($getallcourses->data as $course) {
            $coursedata = [
                'provider' => $adminrecord->provider,
                'referraltypeid' => $referral,
                'providercourseid' => $course->course_id,
                'courseguid' => $course->guid,
                'providercoursename' => $course->course->title,
                'providercoursedesc' => $course->course->description
            ];
            $courseexists = $this->provider_record_exists($adminrecord->provider, $coursedata['providercourseid']);
            if ($courseexists) {
                $id = $DB->get_field(
                    'extintmaxx_provider',
                    'id',
                    ['provider' => $provider, 'providercourseid' => $coursedata['providercourseid']]
                );
                $coursedata['id'] = $id;
                $DB->update_record('extintmaxx_provider', $coursedata);
            } else {
                $DB->insert_record('extintmaxx_provider', $coursedata);
            }
        }
    }

    /** 
     * @param string $provider Provider of specified CourseID
     * @param int $courseid Inserted Course ID
     * @param array $users Array of user IDs to get course data for
     * @return array $studentdata Array of student course data objects with keys of userids
     */
    function get_students_course_data($adminlogin, $provider, $courseid, $users) {
        $acci = new acci();
        $admintoken = $adminlogin->data->token;
        global $DB;
        $studentdata = array();

        foreach ($users as $userid) {
            $studentrecord = $this->student_record_exists($userid, $provider);
            if ($studentrecord == true) {
                $coursedata = $acci->check_student_status($admintoken, $userid, $courseid);
                $student = new stdClass;
                $student->userid = $userid;
                $student->provider = $provider;
                $student->providercourseid = $courseid;
                $student->coursedata = $coursedata ? $coursedata : "Course data not found";
                array_push($studentdata, $student);
            } else {
                $studentnotfound = new stdClass;
                $studentnotfound->userid = $userid;
                $studentnotfound->provider = $provider;
                $studentnotfound->providercourseid = $courseid;
                $studentnotfound->coursedata = "Student data not found";
                array_push($studentdata, $studentnotfound);
            }
        }

        return $studentdata;
    }
}
