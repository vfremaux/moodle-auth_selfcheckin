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
 * Authentication Plugin: Email Authentication
 *
 * @author Martin Dougiamas
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth_selfcheckin
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/auth/ticket/lib.php');

/**
 * Email authentication plugin.
 */
class auth_plugin_selfcheckin extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'selfcheckin';
        $this->config = get_config('auth_selfcheckin');
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
            return validate_internal_user_password($user, $password);
        }
        return false;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object  (with system magic quotes)
     * @param  string  $newpassword Plaintext password (with system magic quotes)
     * @return boolean result
     *
     */
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        // This will also update the stored hash to the latest algorithm
        // if the existing hash is using an out-of-date algorithm (or the
        // legacy md5 algorithm).
        return update_internal_user_password($user, $newpassword);
    }

    function can_signup() {
        return true;
    }

    /**
     * Sign up a new user ready for confirmation.
     * Password is passed in plaintext.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     */
    function user_signup($user, $notify = true, $courseid = null) {
        // Standard signup, without custom confirmatinurl.
        return $this->user_signup_internal($user, $notify, $courseid);
    }

    /**
     * Sign up a new user ready for confirmation.
     *
     * Password is passed in plaintext.
     * A custom confirmationurl could be used.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     * @param string $confirmationurl user confirmation URL
     * @return boolean true if everything well ok and $notify is set to true
     * @throws moodle_exception
     * @since Moodle 3.2
     */
    public function user_signup_internal($user, $notify = true, $courseid = null) {
        global $CFG, $DB;

        include_once($CFG->dirroot.'/user/profile/lib.php');
        include_once($CFG->dirroot.'/user/lib.php');

        $plainpassword = $user->password;
        $user->password = hash_internal_user_password($user->password);
        if (empty($user->calendartype)) {
            $user->calendartype = $CFG->calendartype;
        }

        $user->confirmed = 1;
        $user->id = user_create_user($user, false, false);

        user_add_password_history($user->id, $plainpassword);

        // Save any custom profile field information.
        profile_save_data($user);

        if ($courseid) {
            if (function_exists('debug_trace')) {
                debug_trace('auth_selfcheckin : Enrol in course '.$courseid, TRACE_DEBUG);
            }
            // Enrol in course.
            $enrolplugin = enrol_get_plugin('selfcheckin');
            $params = array('enrol' => 'selfcheckin', 'courseid' => $courseid, 'status' => ENROL_INSTANCE_ENABLED);
            $enrols = $DB->get_records('enrol', $params);

            if ($enrols) {
                $enrol = reset($enrols);

                $starttime = time();
                $endtime = $enrol->enrolenddate;
                if (!empty($enrol->enrolperiod)) {
                    $endtime = $starttime + $enrol->enrolperiod;
                }
                if (function_exists('debug_trace')) {
                    $log = 'auth_selfcheckin : enrolling user '.$user->id.' in course '.$courseid;
                    $log .= ' with enrolid : '.$enrol->id.' / roleid : '.$role->id;
                    $log .= ' starting on : '.$starttime.' / up to : '.$endtime;
                    debug_trace($log, TRACE_DEBUG);
                }
                $enrolplugin->enrol_user($enrol, $user->id, $enrol->roleid, $starttime, $endtime, ENROL_USER_ACTIVE);
            } else {
                if (function_exists('debug_trace')) {
                    debug_trace('auth_selfcheckin : No selfcheckin enrol method instance in '.$courseid, TRACE_ERRORS);
                }
            }
            $courseurl = new moodle_url('/course/view.php', ['id' => $courseid]);
        } else {
            if (function_exists('debug_trace')) {
                debug_trace('auth_selfcheckin : No course to enrol in');
            }
            $courseurl = $CFG->wwwroot;
        }
        $ticket = ticket_generate($user, 'Course direct access', $courseurl, null, 'long');

        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();

        if (!$this->send_notification_email($user, $plainpassword, $ticket)) {
            print_error('auth_selfcheckinnoemail', 'auth_selfcheckin');
        }

        if ($notify) {
            global $CFG, $PAGE, $OUTPUT;
            $emailconfirm = get_string('emailconfirm');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $PAGE->set_heading($PAGE->course->fullname);
            echo $OUTPUT->header();
            notice(get_string('emailconfirmsent', 'auth_selfcheckin', $user->email), "$CFG->wwwroot/index.php");
        } else {
            return true;
        }
    }

    public function send_notification_email($user, $plainpassword, $ticket) {
        global $SITE, $CFG;

        $a = new StdClass;
        $a->wwwroot = $CFG->wwwroot;
        $a->sitename = $SITE->fullname;
        $a->username = $user->username;
        $a->password = $plainpassword;
        $a->firstname = $user->firstname;
        $a->lastname = $user->lastname;
        $a->courseaccessurl = new moodle_url('/login/index.php', ['ticket' => $ticket]);

        $emailsubject = get_string('selfcheckinemailsubject', 'auth_selfcheckin');
        $emailmessage = get_string('selfcheckinemailtext', 'auth_selfcheckin', $a);
        $emailmessagehtml = get_string('selfcheckinemailhtml', 'auth_selfcheckin', $a);

        return email_to_user($user, null, $emailsubject, $emailmessage, $emailmessagehtml);
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    function can_confirm() {
        return false;
    }

    function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return true;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null; // use default internal method
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return true;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    function can_be_manually_set() {
        return true;
    }

    /**
     * Returns whether or not the captcha element is enabled.
     * @return bool
     */
    function is_captcha_enabled() {
        return false;
    }
}
