<?php

/**
 * The student_auth_called event.
 *
 * @package    mod_extintmaxx
 * @copyright  2025 Sophi Dickens
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_extintmaxx\event;

use context;

defined('MOODLE_INTERNAL') || die;

/**
 * The student_auth_called event class.
 * @property-read array $other {
 *      Extra information about event.
 * 
 *      -
 * }
 * 
 * @package mod_extintmaxx
 * @since Moodle 4.1
 * @copyright 2025 Sophi Dickens
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class student_auth_called extends \core\event\base {
    /**
     * Init Method.
     */
    protected function init() {
        $this->data[self::LEVEL_OTHER];
    }
    /**
     * Returns localized general event name.
     */
    public static function get_name() {
        get_string('eventstudentauthcalled', 'extintmaxx');
    }
    /**
     * Returns a localized description of the api call.
     */
    public function get_description() {
        
    }

    
}