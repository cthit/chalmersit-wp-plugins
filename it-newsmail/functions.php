<?php
function get_categories_for_user($user_id){
	global $wpdb;
	$sql = "SELECT cat_id FROM ".IT_NEWSMAIL_TABLE." WHERE user_id = ".$user_id;
	$res = $wpdb->get_results($sql);

	return $res;
}

/* Returns a string of all e-mails */
function get_emails_for_category($cat_id){
	global $wpdb;
	
	$sql = "SELECT user_email FROM it_newsmail, it_users WHERE 
	it_users.ID = it_newsmail.user_id AND (cat_id = -1) GROUP BY user_email;";
	$res = $wpdb->get_results($sql);

	$emails = "";
	foreach ($res as $row) {
		$emails .= $row->user_email.", ";
	}

	return $emails;
}
?>