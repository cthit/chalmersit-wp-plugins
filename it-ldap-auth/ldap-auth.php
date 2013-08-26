<?php
/*
	Plugin Name: IT LDAP Auth
	Plugin URI: http://chalmers.it
	Description: LDAP authentication plugin for Chalmers IT sites
	Version: 1.0
	Author: Johan 'Ndushi' Lindskogen
	Author URI: http://lindskogen.se
	License: MIT
*/

define("COOKIE_NAME", "chalmersItAuth");
define("BASE_PATH", "https://chalmers.it/auth/");

define("IT_LDAP_ACTION", BASE_PATH . "login.php");
define("IT_LDAP_ACTION_LOGOUT", BASE_PATH . "logout.php");
define("IT_LDAP_ACTION_RESET", BASE_PATH . "resetpass.php");

add_filter('login_url', 'it_auth_login', 0, 2);
add_filter('logout_url', 'it_auth_logout', 0, 2);

function it_auth_login($url, $redirect) {
	return IT_LDAP_ACTION;
}

function it_auth_logout($url, $redirect) {
	return IT_LDAP_ACTION_LOGOUT;
}


function format_wp_user($data) {
	$userdata = array(
		"user_login" => $data["cid"],
		"user_pass" => "this password is not used",
		"user_email" => $data["mail"],
		"nickname" => $data["nick"],
		"first_name" => $data["firstname"],
		"last_name" => $data["lastname"]
	);

	return $userdata;
}

if ( !function_exists('wp_validate_auth_cookie') ) :
/**
 * Validates authentication cookie.
 *
 * @return bool|int False if invalid cookie, User ID if valid.
 */
function wp_validate_auth_cookie() {

	$url =  BASE_PATH . "userInfo.php?token=" . $_COOKIE[COOKIE_NAME];

	$user_json = file_get_contents($url);
	$user_data = json_decode($user_json, true);

	if ($user_data === null) {
		return false;
	}
	$user = get_user_by('login', $user_data["cid"]);

	$data = format_wp_user($user_data);
	if ( $user ) {
		return $user->ID;
	} else {
		return wp_insert_user($data);
	}
}
endif;

if ( !function_exists('wp_authenticate') ) :
/**
 * Checks a user's login information and logs them in if it checks out.
 *
 * @since 2.5.0
 *
 * @param string $username User's username
 * @param string $password User's password
 * @return WP_Error|WP_User WP_User object if login successful, otherwise WP_Error object.
 */
function wp_authenticate($username, $password) {
	$username = sanitize_user($username);
	$password = trim($password);

	$user = apply_filters('authenticate', null, $username, $password);

	if ( $user == null ) {
		// TODO what should the error message be? (Or would these even happen?)
		// Only needed if all authentication handlers fail to return anything.
		$user = new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid username or incorrect password.'));
	}

	$ignore_codes = array('empty_username', 'empty_password');

	if (is_wp_error($user) && !in_array($user->get_error_code(), $ignore_codes) ) {
		do_action('wp_login_failed', $username);
	}

	$url =  BASE_PATH . "userInfo.php?token=" . $_COOKIE[COOKIE_NAME];

	$user_json = file_get_contents($url);
	$user_data = json_decode($user_json, true);

	$new_data = format_wp_user($user_data);
	$new_data["ID"] = $user->ID;
	
	wp_update_user($new_data);

	return $user;
}
endif;
