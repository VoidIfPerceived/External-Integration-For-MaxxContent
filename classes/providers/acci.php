<?php

namespace mod_extintmaxx\providers;

require_once("{$CFG->libdir}/filelib.php");
defined('MOODLE_INTERNAL') || die();

use curl;
use CurlHandle;
use mod_lti\local\ltiservice\response;

/**
 * ACCI:
 * - Authenticates to API
 * -- Needs Username and Password
 * --- UN + PW stored and retreived
 * -- cURLS API
 * - get_token (token_type) function
 * - get_referral_types function
 * - get_all_courses function
 * - End requesting functions with a store token to {token_type}
 */
class acci {
    /**
     * $DB
     * -provider_data TABLE
     * -- id
     * -- provider
     * -- token
     * -- token_type
     * -- studentid
     */

    /**
     * @var string $accicoreurl Base URL for ACCI API
     */
    private $accicoreurl = "https://developer.lifeskillslink.com";

    /** Constructor for the ACCI class */
    function __construct() {

    }

        function status_message($status, $message) {
            echo "Status: $status<br>Message: $message<br>";
        }
    /** Gets all courses available to an admin
     *  @param string $username **REQUIRED** admin username
     *  @param string $password **REQUIRED** admin password
     *  @return object $responsedata API Response in object format
     */
    function admin_login($username, $password) {
        $curl = new curl();
        $adminendpoint = "/api/adminLogin/";

        $options = array(
            "CURLOPT_FOLLOWLOCATION" => true,
            "CURLOPT_RETURNTRANSFER" => true,
        );

        $data = array(
            "email" => $username,
            "password" => $password
        );

        $header = array(
            'accept: application/json',
        );

        $url = "{$this->accicoreurl}{$adminendpoint}";

        $curl->setHeader($header);

        /**
         *  @var string $response CURL Response from API, Returns the following data on success:
         *  @param string $url Full Method URL
         *  @param array $data Data sent to API
         *  @param array $options CURL options
         *  - @return bool status code (status)
         *  - @return string message (message)
         *  - @return array data array (data):
         *      - @return string token (token)
         *      - @return string token expiration (expires_at)
         *      - @return array user info array (user)
         */
        $response = $curl->post($url, $data, $options);

        if ($response == false) {
            echo "Admin Login Curl Error: ";
            $error = $curl->error;
            echo $error;
            return;
        }

        $responsedata = json_decode($response);

        echo "Raw Response Status Code: ".var_dump($response)."<br>";
        $adminstatus = $responsedata->status==true ? "Success" : "Error";
        $adminmessage = $responsedata->message;
        $this->status_message($adminstatus, $adminmessage);

        return $responsedata;
    }
    /** Gets referral types available to an admin
     *  @param string $token **REQUIRED** admintoken
     *  @return object $responsedata API Response in object format
     */
    function get_referral_types_by_admin($token) {
        $curl = new curl();
        $referraltypesendpoint = "/api/getReferralTypesByAdmin";

        $options = array(
            "CURLOPT_FOLLOWLOCATION" => true,
            "CURLOPT_RETURNTRANSFER" => true,
        );

        $header = array(
            'accept: application/json',
        );

        $data = array(
            "token" => $token
        );

        $url = "{$this->accicoreurl}{$referraltypesendpoint}";

        echo "Referral URL: {$url}<br>";

        $curl->setHeader($header);

        /**
         * @var string $response **get_referral_types_by_admin** CURL Response from API, Returns the following data on success:
         *  @param string $url Full Method URL
         *  @param array $data Data sent to API
         *  @param array $options CURL options
         *  - @return bool status code (status)
         *  - @return string message (message)
         *  - @return array data array (data):
         *      - @return int id (id)
         *      - @return int superadmin id (superadmin_id)
         *      - @return int referral type id (referraltype_id)
         *      - @return array superadmin info array (superadmin)
         *      - @return array referral type array (referraltype)
         *          - @return int id (id)
         *          - @return string referral type name (name)
         *          - @return string referral type description (description)
         *          - @return string referral type icon (icon)
         */
        $response = $curl->post($url, $data, $options);

        if ($response == false) {
            echo "Admin Login Curl Error: ";
            $error = $curl->error;
            echo $error;
            return;
        }

        $responsedata = json_decode($response);

        $getreferraltypesstatus = $responsedata->status==true ? "Success" : "Error";
        $getreferraltypesmessage = $responsedata->message;
        $this->status_message($getreferraltypesstatus, $getreferraltypesmessage);

        return $responsedata;
    }
    /** Gets all courses available to an admin
     *  @param string $token **REQUIRED** admintoken
     *  @param string $referraltypeid **REQUIRED** referral type id
     *  @return object $responsedata API Response in object format
     */
    function get_all_courses($token, $referraltypeid) {
        $curl = new curl();
        $getallcoursesendpoint = "/api/getAllCourses";

        $options = array(
            "CURLOPT_FOLLOWLOCATION" => true,
            "CURLOPT_RETURNTRANSFER" => true,
        );

        $header = array(
            'accept: application/json'
        );

        $data = array(
            "token" => $token,
            "id" => $referraltypeid
        );

        $url = "{$this->accicoreurl}{$getallcoursesendpoint}";

        $curl->setHeader($header);

        /**
         *  @var string $response **get_all_courses** CURL Response from API, returns the following data on success:
         *  @param string $url Full Method URL
         *  @param array $data Data sent to API
         *  @param array $options CURL options
         *  - @return bool status (status)
         *  - @return string message (message)
         *  - @return array data array (data):
         *      - @return object course object [data array index]
         *          - @return int referral type id (referraltype_id)
         *          - @return int course id (course_id)
         *          - @return string course type (course_type)
         *          - @return string course guid (guid)
         *          - @return array course info array (course)
         */
        $response = $curl->post($url, $data, $options);

        if ($response == false) {
            echo "Admin Login Curl Error: ";
            $error = $curl->error;
            echo $error;
            return;
        }

        $responsedata = json_decode($response);

        /**
         * Add ability to store all courses:
         *  - Course GUID (guid)
         * Pull Course from API using GUID and "get_course_by_id" function/endpoint
         *  - Course Name (title)
         *  - Course Description (description)
         *  - Course Type (course_type)
         *  - Course ID (course_id)
         */

        $getreferraltypesstatus = $responsedata->status==true ? "Success" : "Error";
        $getreferraltypesmessage = $responsedata->message;
        $this->status_message($getreferraltypesstatus, $getreferraltypesmessage);

        return $responsedata;
    }
    /** Gets students presently enrolled under an admin's course
     *  @param string $token **REQUIRED** admintoken
     *  @return object $responsedata API Response in object format
     */
    function get_students_by_admin($token) {
        $curl = new curl();
        $getstudentsbyadminendpoint = "/api/getStudentsByAdmin";

        $options = array(
            "CURLOPT_FOLLOWLOCATION" => true,
            "CURLOPT_RETURNTRANSFER" => true,
        );

        $header = array(
            'accept: application/json'
        );

        $data = array(
            "token" => $token
        );

        $url = "{$this->accicoreurl}{$getstudentsbyadminendpoint}";

        $curl->setHeader($header);

        /**
         *  @var string $response **get_students_by_admin** CURL Response from API, Returns the following data on success:
         *  @param string $url Full Method URL
         *  @param array $data Data sent to API
         *  @param array $options CURL options
         *  - @return bool status code (status)
         *  - @return string message (message)
         *  - @return array data array (data):
         *      - @return int id (id)
         *      - @return int superadmin id (superadmin_id)
         *      - @return int referral type id (referraltype_id)
         *      - @return array superadmin info array (superadmin)
         *      - @return array referral type array (referraltype)
         *          - @return int id (id)
         *          - @return string referral type name (name)
         *          - @return string referral type description (description)
         *          - @return string referral type icon (icon)
         */
        $response = $curl->post($url, $data, $options);

        if ($response == false) {
            echo "Admin Login Curl Error: ";
            $error = $curl->error;
            echo $error;
            return;
        }

        $responsedata = json_decode($response);

        $getreferraltypesstatus = $responsedata->status==true ? "Success" : "Error";
        $getreferraltypesmessage = $responsedata->message;
        $this->status_message($getreferraltypesstatus, $getreferraltypesmessage);

        return $responsedata;
    }

    /** Adds students inserted information to admin enrollment
     *  @param string $token **REQUIRED** admintoken
     *  @param string $firstname **REQUIRED** Student firstname
     *  @param string $lastname **REQUIRED** Student lastname
     *  @param string $studentemail **REQUIRED** Student email
     *  @param string $token **REQUIRED** admintoken
     *  @param string $courseguid **REQUIRED** Course guid {get_all_courses()}
     *  @param string $casenumber **REQUIRED** Student casenumber
     *  @param string $coachname *Optional* Coach name
     *  @param string $coachemail *Optional* Coach email
     *  @param string $coachphone *Optional* Coach phone
     *  @return object $responsedata API Response in object format
     */
    function student_self_enrolled($token, $firstname, $lastname, $studentemail, $courseguid, $casenumber, $coachname = null, $coachemail = null, $coachphone = null) {
        $curl = new curl();
        /** @var string $studentselfenrolledendpoint URL endpoint of student_self_enrolled method */
        $studentselfenrolledendpoint = "/api/studentSelfEnrolled";

        /** Optional param null checks (additional confirmation of value) */
        $coachname = $coachname ? $coachname : null;
        $coachemail = $coachemail ? $coachemail : null;
        $coachphone = $coachphone > 0 ? $coachphone : null;

        $options = array(
            "CURLOPT_FOLLOWLOCATION = TRUE",
            "CURLOPT_RETURNTRANSFER = TRUE"
        );

        $header = array(
            'accept: application/json',
        );

        $data = array(
            'token' => $token,
            'course_guid' => $courseguid,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $studentemail,
            'casenumber' => $casenumber,
            'coachname' => $coachname,
            'coachemail' => $coachemail,
            'coachphone' => $coachphone 
        );

        $url = "{$this->accicoreurl}{$studentselfenrolledendpoint}";

        $curl->setHeader($header);

        /**
         *  @var string $response CURL Response from API, Returns the following data on success:
         *  @param string $url Full Method URL
         *  @param array $data Data sent to API
         *  @param array $options CURL options
         *  - @return bool status code (status)
         *  - @return string message (message)
         *  - @return array data array (data):
         *      - @return string admintoken (token)
         *      - @return string student auto login url (redirect_url)
         *      - @return array student info array (student)
         *          - @return int id (id)
         *          - @return string student firstname (firstname)
         *          - @return string student lastname (lastname)
         *          - @return string student email (email)
         *          - @return array admin info array (adminusr)
         *          - @return array superadmin info array (superadmin)
         */
        $response = $curl->post($url, $data, $options);

        if ($response == false) {
            echo "Admin Login Curl Error: ";
            $error = $curl->error;
            echo $error;
            return;
        }

        $responsedata = json_decode($response);

        $studentselfenrolledstatus = $responsedata->status==true ? "Success" : "Error";
        $studentselfenrolledmessage = $responsedata->message;
        $this->status_message($studentselfenrolledstatus, $studentselfenrolledmessage);

        

        return $responsedata;
    }
    /** Logs in a student to ACCI via API
     *  @param string $studentemail **REQUIRED** student email
     *  @param string $studentpassword **REQUIRED** student password
     *  @param string $token **REQUIRED** admintoken
     *  @return object $responsedata API Response in object format
     */
    function student_auth($studentemail, $studentpassword, $token) {
        $curl = new curl();
        $studentauthendpoint = "/api/studentLogin/";

        $options = array(
            "CURLOPT_FOLLOWLOCATION = TRUE",
            "CURLOPT_RETURNTRANSFER = TRUE"
        );

        $header = array(
            'accept: application/json',
            'X-CSRF-TOKEN: '.$token
        );

        $data = array(
            'email' => $studentemail,
            'password' => $studentpassword
        );

        $url = "{$this->accicoreurl}{$studentauthendpoint}";

        $curl->setHeader($header);

        /**
         *  @var string $response CURL Response from API, Returns the following data on success:
         *  @param string $url Full Method URL
         *  @param array $data Data sent to API
         *  @param array $options CURL options
         *  - @return bool status code (status)
         *  - @return string message (message)
         *  - @return array data array (data):
         *      - @return string admintoken (token)
         *      - @return string student auto login url (redirect_url)
         */
        $response = $curl->post($url, $data, $options);

        if ($response == false) {
            echo "Admin Login Curl Error: ";
            $error = $curl->error;
            echo $error;
            return;
        }

        $responsedata = json_decode($response);

        $getreferraltypesstatus = $responsedata->status==true ? "Success" : "Error";
        $getreferraltypesmessage = $responsedata->message;
        $this->status_message($getreferraltypesstatus, $getreferraltypesmessage);

        return $responsedata;
    }

    /**
     * functions for:
     * Admin Login
     *  
     */

}

