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

if (!function_exists("wp_validate_auth_cookie")) {
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
			$data["ID"] = $user->ID;
			return wp_update_user($data);
		} else {
			return wp_insert_user($data);
		}
	}
}
