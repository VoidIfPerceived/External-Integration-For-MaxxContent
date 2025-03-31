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
 * Languages configuration for the mod_extintmaxx plugin.
 *
 * @package   mod_extintmaxx
 * @copyright 2025, Sophi Dickens <sophidickens.e@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Basic Strings:
    $string['pluginname'] = 'Maxx External Integration';
    $string['modulename'] = 'Maxx External Integration';
    $string['pluginspecificheader'] = 'Maxx External Integration Settings';
    $string['manage'] = 'Manage';
    $string['extintmaxx_settings'] = 'Maxx External Integration Settings';

// Form Strings:
/** Admin Forms */
    $string['apikey'] = 'API Key';
    $string['apikey_help'] = 'Enter the API Token for the External Provider you would like to integrate with.';
    $string['providersselection'] = 'Providers';
    $string['providersselection_help'] = 'Select the Provider you would like to integrate with.';
    $string['providerusername'] = 'Provider Username';
    $string['providerusername_help'] = 'Enter the Username for the selected provider you would like to integrate with.';
    $string['providerpassword'] = 'Provider Password';
    $string['providerpassword_help'] = 'Enter the Password for the selected provider you would like to integrate with.';
    $string['insertprovidercredentials'] = 'Insert Provider Credentials';
/** Student Forms */
    $string['studentusername'] = 'Username';
    $string['studentusername_help'] = 'Enter your username for ${provider}.';
    $string['studentpassword'] = 'Password';
    $string['studentpassword_help'] = 'Enter your password for ${provider}.';
    $string['studentemail'] = 'Email';
    $string['studentemail_help'] = 'Enter your email for ${provider}.';
    $string['studentfirstname'] = 'First Name';
    $string['studentfirstname_help'] = 'Enter your first name for ${provider}.';
    $string['studentlastname'] = 'Last Name';
    $string['studentlastname_help'] = 'Enter your last name for ${provider}.';
    $string['studentcasenumber'] = 'Case Number';
    $string['studentcasenumber_help'] = 'Enter your case number.';
    $string['enroll'] = 'Create Account';
    $string['login'] = 'Login';

// Providers:
    $string['nali'] = 'North American Learning Institute';
    $string['acci'] = 'ACCI Lifeskills';

// Event Strings:
/** Admin Login Method Called Event */
    $string['eventadminlogincalled'] = 'Admin Login Method Called.';
    $string['eventadminlogincalleddesc'] = 'The admin login method was called by userid: "{$userid}".';
    $string['eventadminlogincalledmessage'] = '{$statusmessage}';
/** Get Referral Types By Admin Method Called Event */
    $string['eventgetreferraltypesbyadmincalled'] = 'Get Referral Types by Admin Called';
    $string['eventgetreferraltypesbyadmincalleddesc'] = 'The get referral types by admin method was called by userid: "{$userid}".';
    $string['eventgetreferraltypesbyadmincalledmessage'] = '{$statusmessage}';
/** Get All Courses Method Called Event */
    $string['eventgetallcoursescalled'] = 'Get All Courses Called';
    $string['eventgetallcoursescalleddesc'] = 'The get all courses method was called by userid: "${userid}".';
    $string['eventgetallcoursescalledmessage'] = '{$statusmessage}';
/** Get Students By Admin Method Called Event */
    $string['eventgetstudentsbyadmincalled'] = 'Get Students by Admin Called';
    $string['eventgetstudentsbyadmincalleddesc'] = 'The get students by admin method was called by userid: "${userid}".';
    $string['eventgetstudentsbyadmincalledmessage'] = '{$statusmessage}';
/** Student Self Enrolled Method Called Event */
    $string['eventstudentselfenrolledcalled'] = 'Student Self Enrolled Called';
    $string['eventstudentselfenrolledcalleddesc'] = 'The student self enrolled method was called by userid: "${userid}".';
    $string['eventstudentselfenrolledcalledmessage'] = '{$statusmessage}';
/** Student Auth Method Called Event */
    $string['eventstudentauthcalled'] = 'Student Auth Called';
    $string['eventstudentauthcalleddesc'] = 'The student auth method was called by userid: "${userid}".';
    $string['eventstudentauthcalledmessage'] = '{$statusmessage}';
/** Admin Form Submitted Event */
    $string['eventadminformsubmitted'] = 'Admin form submitted';
    $string['eventadminformsubmitteddesc'] = 'The admin form was submitted by userid: "${userid}".';
/** Module Form Submitted Event */
    $string['eventmoduleformsubmitted'] = 'Module form submitted';
    $string['eventmoduleformsubmitteddesc'] = 'The module form was submitted by userid: "${userid}".';