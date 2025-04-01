<?php
namespace mod_extintmaxx\providers;
require_once("{$CFG->libdir}/filelib.php");
//Use required API.php files

use core\session\exception;
use mod_extintmaxx\providers\acci;

defined('MOODLE_INTERNAL') || die();

class provider_api_method_chains {
    /** ACCI Method Chains */
    function __construct() {
        
    }

    /** Checks whether the provided provider exists within the database 
     *  @param string $provider The supplied provider the DB will be checked for.
     *  @return object $providerdata The DB data with the provider information requested.
     */
    function provider_record_exists($provider) {
        global $DB;
        $providerdata = $DB->get_record('extintmaxx_admin', array('provider' => $provider));
        if (!$providerdata->provider) {
            return false;
        } else if ($providerdata->provider) {
            return $providerdata;
        }
    }

    function student_record_exists($userid, $provider) {
        global $DB;
        $studentdata = $DB->get_record(
            'extintmaxx_user',
            array(
                'userid' => $userid,
                'provider' => $provider
            )
        );
        if (!$studentdata->userid || !$studentdata->provider) {
            $student = array(
                $studentdata,
                false
            );
            return $student;
        } else if ($studentdata->userid && $studentdata->provider) {
            $student = array(
                $studentdata,
                true
            );
            return $student;
        }
    }
}