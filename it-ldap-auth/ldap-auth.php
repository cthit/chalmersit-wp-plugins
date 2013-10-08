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


class IT_Auth {

	public function __construct() {
		add_filter('login_url', array($this, 'it_login_url'));
		add_filter('logout_url', array($this, 'it_logout_url'));
		add_filter('lostpassword_url', array($this, 'it_lostpassword_url'));

		add_action('personal_options_update', 'ldap_login_password_and_role_manager_userprofile');
		add_action('edit_user_profile_update', 'ldap_login_password_and_role_manager_userprofile');

		// Remove default authentication
		remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
	}
	public function it_login_url($login_url) {
		die($login_url);
	}
	public function it_logout_url($logout_url) {
		die($logout_url);
	}
	public function it_lostpassword_url($lostpassword_url) {
		die($lostpassword_url);
	}

};
$auth = new IT_Auth();


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
