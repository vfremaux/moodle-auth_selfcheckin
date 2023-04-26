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
 * Strings for component 'auth_selfcheckin', language 'en'.
 *
 * @package   auth_selfcheckin
 * @copyright 2023 onwards Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_selfcheckindescription'] = '<p>QRCode-based self-registration enables a user to create their own account via a QRCode link shown by the teacher. The user then receives an email with account confirmation. As the procedure is secured from the beginning, there is no need of accountconfirmation by email, just email notification.</p>';
$string['auth_selfcheckinnoemail'] = 'Tried to send you an email but failed!';
$string['auth_selfcheckinsettings'] = 'Settings';
$string['pluginname'] = 'QRCode based self-registration';
$string['privacy:metadata'] = 'The QRCode-based self-registration authentication plugin does not store any personal data.';
$string['selfcheckinemailsubject'] = 'Your registration on {$a->sitename}';
$string['emailconfirmsent'] = '
<p>You are now registered in Moodle.</p>
<p>A summary email has been sent to you at : {$a}<br/>
You will find your login credentials, and a link to sign in directly, with 24 hours validity.</p>
';
$string['selfcheckinemailtext'] = '
Welcome {$a->firstname} {$a->lastname}

You have successfully registered in the {$a->sitename} courseware.

Your account is fully setup and needs no confirmation.

You will access your courses by connecting to : {$a->wwwroot} using your following credentials: 
Your username: {$a->username}
Your password: {$a->password}
Do not communicate your password to anyone!

Enjoy your learning content!
';
$string['selfcheckinemailhtml'] = '
<h2>>Welcome {$a->firstname} {$a->lastname}</h2>

<p>You have successfully registered in the {$a->sitename} courseware.</p>
<p>Your account is fully setup and needs no confirmation.</p>
<p>You will access your courses by connecting to : <a href="{$a->wwwroot}">{$a->wwwroot}</a> using your following credentials:<br/>
Your username: <b>{$a->username}</b><br/>
Your password: <b>{$a->password}</b><br/>
Do not communicate your password to anyone!</p>

<p>Enjoy your learning content!</p>
';

