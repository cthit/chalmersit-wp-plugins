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

add_filter('login_url', 'it_auth_login', 0, 2);

remove_all_filters('logout_url', 10);
add_filter('logout_url', 'it_auth_logout', 0, 2);

function it_auth_login($url, $redirect) {
	return IT_LDAP_ACTION;
}

function it_auth_logout($url, $redirect) {
	return IT_LDAP_ACTION_LOGOUT;
}


if (!function_exists("wp_validate_auth_cookie")) {
	function wp_validate_auth_cookie() {

		$url =  BASE_PATH . "userInfo.php?token=" . $_COOKIE[COOKIE_NAME];

		$user_json = file_get_contents($url);
		$user_data = json_decode($user_json, true);

		$user = get_user_by('login', $user_data["cid"]);

		if ( ! $user ) {
			do_action('auth_cookie_bad_username', $cookie_elements);
			return false;
		}

		return $user->ID;
	}
}
