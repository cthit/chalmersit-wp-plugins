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
add_action("publish_post", "itnm_doMail");

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
	$post = get_post($post_id);
	$thecontent = apply_filters('the_content', $post->post_content);
	$cat = get_the_category($post_id);
	$author = get_user_by('id', $post->post_author)->display_name;
	$allrecipients = get_emails_for_category($cat->cat_ID);

	$subject = "Chalmers.it: ".$post->post_title;

	$message = "<a href=\"".get_permalink($post_id)."\" >";
	$message .= "<h2>".$post->post_title."</h2></a>";
	$message .= $thecontent;
	$message .= "<p><em>".$post->post_date." by ".$author."</em></p>";

	$headers['from'] = 'From: Chalmers.it <noreply@chalmers.it>';
	$headers['mime']     = 'MIME-Version: 1.0';
	$headers['type']     = 'Content-Type: text/html; charset="utf8"';
	
	$recipients = "";
	for ($i=0; $i<count($allrecipients);i++) { 
	 	# code...
		$recipients .= $allrecipients[$i];

		if($i % 90 == 89){
			$headers['bcc'] = 'BCC: '.$recipients;	
			$header = implode("\n", $headers);
			wp_mail("", $subject, $message, $header);
			$recipients = "";		
		}
	}

	$headers['bcc'] = 'BCC: '.$recipients;	
	$header = implode("\n", $headers);
	wp_mail("", $subject, $message, $header);
}
?>
