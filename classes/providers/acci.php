<?php

namespace mod_extintmaxx\providers;

require_once("{$CFG->libdir}/filelib.php");
defined('MOODLE_INTERNAL') || die();

use curl;
use mod_lti\local\ltiservice\response;

/**
 * ACCI:
 * - Authenticates to API
 * -- Needs Username and Password
 * --- UN + PW stored and retreived
 * -- cURLS API
 * - get_token (token_type) function
 * - get_referal_types function
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
    private $curl;
    private $acci_core_url = "https://www.lifeskillslink.com";

    function __construct() {
        $this->curl = new curl();
    }
    
    function admin_login($username, $password) {
        $curl = new curl();
        $admin_endpoint = "/api/adminLogin/";

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

        $url = "{$this->acci_core_url}{$admin_endpoint}";

        $curl->setHeader($header);
        
        $response = $curl->post($url, $data, $options);

        if ($response === false) {
            echo "Admin Login Error: ";
            $error = $curl->error;
            echo $error;
            return;
        }

        return $response;
    }

    function get_referaltypes($token) {
        $curl = new curl();
        $referaltypes_endpoint = "/api/getReferralTypesByAdmin/";

        $options = array(
            "CURLOPT_FOLLOWLOCATION" => true,
            "CURLOPT_RETURNTRANSFER" => true,
        );

        $header = array(
            'accept: application/json',
            'X-CSRF-TOKEN: '.$token
        );

        $data = array();

        $url = "{$this->acci_core_url}{$referaltypes_endpoint}";

        $response = $curl->get($url, $data, $options);

        if ($response === false) {
            $error = $curl->error;
            echo $error;
        }

        $response_data = json_decode($response, true);

        return $response_data;
    }

    function get_all_courses($token, $referaltype_id) {
        $curl = new curl();
        $get_all_courses_endpoint = "/api/getAllCourses/";

        $options = array(
            "CURLOPT_FOLLOWLOCATION" => true,
            "CURLOPT_RETURNTRANSFER" => true,
        );

        $header = array(
            'accept: application/json',
            'X-CSRF-TOKEN: '.$token
        );

        $data = array(
            "referraltype_id" => $referaltype_id
        );

        $url = "{$this->acci_core_url}{$get_all_courses_endpoint}";

        $response = $curl->post($url, $data, $options);

        if ($response === false) {
            $error = $curl->error;
            echo $error;
        }

        $response_data = json_decode($response, true);

        return $response_data;
    }

    function get_token($token_type, $studentid = null) {
        
    }

    /**
     * functions for:
     * Admin Login
     * 
     */

}

