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
define("BASE", "https://chalmers.it/auth/");
define("IT_LOGIN_URL", BASE . "login.php");
define("IT_LOGOUT_URL", BASE . "logout.php");
define("IT_FORGOT_URL", BASE . "resetpass.php");

class IT_Auth {

	public function __construct() {
		add_action('personal_options_update', array(&$this, 'updatepass'));
		add_action('edit_user_profile_update', array(&$this, 'updatepass'));
		add_filter('logout_url', array(&$this, 'it_logout_url'), 11, 1);

	}
	private function format_redirect($url, $redir) {
		if (empty($redir)) {
			return $url;
		} else {
			return $url . "?redirect_to=" . urlencode($redir);
		}
	}
	public function it_login_url($redirect = '') {
		return $this->format_redirect(IT_LOGIN_URL, $redirect);
	}
	public function it_logout_url($url) {
		return $this->format_redirect(IT_LOGOUT_URL, "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"]);
	}
	public function it_lostpassword_url($redirect = '') {
		return $this->format_redirect(IT_FORGOT_URL, $redirect);
	}
	public function updatepass($user_id) {
		$d = array('user_pass' => uniqid('nopass').microtime());
		$result = wp_update_user($d);

		if ( !current_user_can( 'edit_user', $user_id ) ) return false;
		if ( $_POST['pass1'].'x' != $_POST['pass2'].'x' ) return false;
		global $current_user;
		if ( ! $current_user ) return false;

		wp_remote_post(IT_FORGOT_URL, array(
			"body" => array(
				"password" => $_POST['pass1'],
				"cookie" => $_COOKIE[COOKIE_NAME]
			)
		));
	}

};
$it_auth = new IT_Auth();
global $it_auth;


if (!function_exists("wp_validate_auth_cookie")) :


function format_wp_user($data) {
	return array(
		"user_login" => $data["cid"],
		"user_pass" => uniqid('nopass').microtime(),
		"user_email" => $data["mail"],
		"nickname" => $data["nick"],
		"first_name" => $data["firstname"],
		"last_name" => $data["lastname"]
	);
}

function wp_validate_auth_cookie() {
	$url =  BASE . "userInfo.php?token=" . $_COOKIE[COOKIE_NAME];

	$user_json = file_get_contents($url);
	$user_data = json_decode($user_json, true);

	if ($user_data === null) {
		return false;
	}
	$user = get_user_by('login', $user_data["cid"]);

	if ( $user ) {
		return $user->ID;
	} else {
		$data = format_wp_user($user_data);
		return wp_insert_user($data);
	}
}
endif;
