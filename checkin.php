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
 * Implements a result page for driving the checkin transaction.
 * @package auth_selfcheckin
 * @category auth
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright 2008 onwards Valery Fremaux (http://www.myLearningFactory.com)
 */
require('../../config.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot.'/login/lib.php');
require_once($CFG->dirroot.'/auth/ticket/lib.php');

$token = required_param('token', PARAM_RAW);

// Stands for valid authentication.
$ticket = ticket_decode($token);
$config = get_config('auth_ticket');

if (empty($config->shortvaliditydelay)) {
    $config->shortvaliditydelay = 3600;
}

if ($ticket->date < (time() - $config->shortvaliditydelay)) {
    print_error(get_string('obsoleteticket', 'auth_selfcheckin'));
}

$url = new moodle_url('/auth/selfcheckin/checkin.php', ['token' => $token]);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

// If wantsurl is empty or /login/signup.php, override wanted URL.
// We do not want to end up here again if user clicks "Login".
if (empty($SESSION->wantsurl)) {
    $SESSION->wantsurl = $CFG->wwwroot . '/';
} else {
    $wantsurl = new moodle_url($SESSION->wantsurl);
    if ($PAGE->url->compare($wantsurl, URL_MATCH_BASE)) {
        $SESSION->wantsurl = $CFG->wwwroot . '/';
    }
}

if (isloggedin() and !isguestuser()) {
    // Prevent signing up when already logged in.
    echo $OUTPUT->header();
    echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url('/login/logout.php',
        array('sesskey' => sesskey(), 'loginpage' => 1)), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('cannotsignup', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

// If verification of age and location (digital minor check) is enabled.
if (\core_auth\digital_consent::is_age_digital_consent_verification_enabled()) {
    $cache = cache::make('core', 'presignup');
    $isminor = $cache->get('isminor');
    if ($isminor === false) {
        // The verification of age and location (minor) has not been done.
        redirect(new moodle_url('/login/verify_age_location.php'));
    } else if ($isminor === 'yes') {
        // The user that attempts to sign up is a digital minor.
        redirect(new moodle_url('/login/digital_minor.php'));
    }
}

// Plugins can create pre sign up requests.
// Can be used to force additional actions before sign up such as acceptance of policies, validations, etc.
$CFG->registerauth = 'selfcheckin';
core_login_pre_signup_requests();

// Rely on standard signup form to fetch data.
require_once($CFG->dirroot.'/login/signup_form.php');
$mform_signup = new login_signup_form($url, null, 'post', '', array('autocomplete' => 'on'));

if ($mform_signup->is_cancelled()) {
    // redirect(get_login_url());

} else if ($user = $mform_signup->get_data()) {
    // Add missing required fields.
    $user = signup_setup_new_user($user);

    // Plugins can perform post sign up actions once data has been validated.
    $reason = $ticket->reason;
    $params = ['courseid' => 0]; 
    if (preg_match('/courseid=([0-9]+)/', $reason, $matches)) {
        $params = ['user' => $user, 'courseid' => $matches[1]];
        // enrol selfcheckin will use this to enrol user in course.
        core_login_post_signup_requests($params);
    }

    // Standard. 
    $authplugin = get_auth_plugin('selfcheckin');
    $authplugin->user_signup($user, true, $params['courseid']); // prints notice and link to login/index.php
    exit; //never reached
}

$newaccount = get_string('newaccount');
$login      = get_string('login');

$PAGE->navbar->add($login);
$PAGE->navbar->add($newaccount);

$PAGE->set_pagelayout('frontpage');
$PAGE->set_title($newaccount);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

if ($mform_signup instanceof renderable) {
    // Try and use the renderer from the auth plugin if it exists.
    try {
        $renderer = $PAGE->get_renderer('auth_sefcheckin');
    } catch (coding_exception $ce) {
        // Fall back on the general renderer.
        $renderer = $OUTPUT;
    }
    echo $renderer->render($mform_signup);
} else {
    // Fall back for auth plugins not using renderables.
    $mform_signup->display();
}
echo $OUTPUT->footer();



