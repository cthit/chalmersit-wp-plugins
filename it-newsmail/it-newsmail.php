<?php
/* 
	Plugin Name: IT Newsmail
	Plugin URI: https://chalmers.it
	Description: Newsposts are mailed to users automatically. Users manage subscriptions via a widget 
	Author: Max Witt
	License: MIT
*/

global $wpdb;
define("IT_NEWSMAIL_TABLE", $wpdb->prefix. "newsmail");

register_activation_hook(__FILE__, 'it_newsmail_activate');
register_deactivation_hook(__FILE__,'it_newsmail_deactivate');
add_action("init", "it_newsmail");

require_once "class.ITNewsMail_Widget.php";

ITNewsMail_Widget::init();

/* Register categories for user here! */
function it_newsmail(){
	global $wpdb;
	if($_SERVER['REQUEST_METHOD'] == "POST" &&
					!empty($_POST['action']) &&
					$_POST['action'] == "it_newsmail"){
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;

		$wpdb->query($wpdb->prepare("DELETE FROM ".IT_NEWSMAIL_TABLE." WHERE user_id = %d", $user_id));
		
		if($_POST['itnm-1']){
			$wpdb->insert(IT_NEWSMAIL_TABLE, array(
				'user_id' => $user_id,
				'cat_id' => -1));
		}

	}

}

function it_newsmail_activate(){
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "CREATE TABLE IF NOT EXISTS ".IT_NEWSMAIL_TABLE." (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) unsigned NOT NULL,
		cat_id bigint(20) signed NOT NULL,

		PRIMARY KEY (id)
		);";
	
	dbDelta($sql);
}

function it_newsmail_deactivate(){
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "DROP TABLE ".IT_NEWSMAIL_TABLE.";";
	dbDelta($sql);
}

function itnm_doMail($post_id){
	// Do the actual mailing here
}

?>
