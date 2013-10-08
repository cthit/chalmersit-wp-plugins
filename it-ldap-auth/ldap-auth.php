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

/*remove_all_filters('authenticate');

add_filter('authenticate', 'ldap_authenticate', 1, 3);

function ldap_authenticate($user, $login, $pass) {
	var_dump($login);
	var_dump($pass);

	return new WP_User(4);
}*/

define("COOKIE_NAME", "chalmersItAuth");
define("BASE_PATH", "https://chalmers.it/auth/");

define("IT_LDAP_ACTION", BASE_PATH . "login.php");
define("IT_LDAP_ACTION_LOGOUT", BASE_PATH . "logout.php");
define("IT_LDAP_ACTION_RESET", BASE_PATH . "resetpass.php");

// add_action('retrieve_password', 'lost_password', 10, 1);

//show_user_profile
//edit_user_profile

function lost_password($cid) {
	wp_remote_post(BASE_PATH."resetpass.php", array("username" => $cid, "no-redirect" => true));
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

if ( !function_exists('wp_set_password') ) :
/**
 * Updates the user's password with a new encrypted one.
 *
 * For integration with other applications, this function can be overwritten to
 * instead use the other package password checking algorithm.
 *
 * @since 2.5
 * @uses $wpdb WordPress database object for queries
 * @uses wp_hash_password() Used to encrypt the user's password before passing to the database
 *
 * @param string $password The plaintext new user password
 * @param int $user_id User ID
 */
function wp_set_password( $password, $user_id ) {
	$token = $_COOKIE[COOKIE_NAME];
	wp_remote_post(BASE_PATH."resetpass.php", array("password" => $password, "cookie" => $token));
	wp_cache_delete($user_id, 'users');
}
endif;
