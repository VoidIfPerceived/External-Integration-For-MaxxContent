<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Activity creation/editing form for the mod_[modname] plugin.
 *
 * @package   mod_extintmaxx
 * @copyright 2025, Sophi Dickens <sophidickens.e@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/extintmaxx/lib.php');

class mod_extintmaxx_mod_form extends \moodleform_mod {
    function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        $this->standard_coursemodule_elements();

        $mform->addElement('header', 'pluginspecificheader', get_string('pluginspecificheader', 'extintmaxx'));

        $mform->addElement('text', 'providerusername', get_string('providerusername', 'extintmaxx'));
        $mform->setType('providerusername', PARAM_TEXT);
        $mform->addHelpButton('providerusername', 'providerusername_help', 'extintmaxx');

        $mform->addElement('text', 'providerpassword', get_string('providerpassword', 'extintmaxx'));
        $mform->setType('providerpassword', PARAM_TEXT);
        $mform->addHelpButton('providerpassword', 'providerpassword_help', 'extintmaxx');

        $mform->addElement('select', 'provider', get_string('providersselection', 'extintmaxx'), array('ACCI'));
        $mform->addHelpButton('provider', 'providersselection_help', 'extintmaxx');

        $this->add_action_buttons();

    }


}
