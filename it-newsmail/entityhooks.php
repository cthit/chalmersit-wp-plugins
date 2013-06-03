<?php
/*
	This file contains methods for keeping the newsmail DB up to date
	when the sets of users and categories are modified from outside.
	Users and categories should be removed from the DB when they are
	removed by wordpress itself.
	Users subscribing to all news should auto-subscribe to new categories.

*/

add_action('create_category', 'itnm_catAdded');
add_action('delete_category', 'itnm_catDeleted');
add_action('delete_user', 'itnm_userDeleted');

function itnm_catAdded($cat_id) {
	global $wpdb;
	$sql = $wpdb->prepare("INSERT INTO ".SUBSCRIBE_TABLE." (user_id, cat_id) 
SELECT user_id, %d AS cat_id FROM ".SUBSCRIBE_TABLE." GROUP BY user_id 
HAVING count(user_id) = (SELECT COUNT(*) FROM it_term_taxonomy WHERE taxonomy = 'category')-1;", $cat_id);

	$wpdb->query($sql);
}


function itnm_catDeleted($cat_id) {
	global $wpdb;
	$sql = $wpdb->prepare("DELETE FROM ".SUBSCRIBE_TABLE." WHERE cat_id = %d;", $cat_id);
	$wpdb->query($sql);
}

function itnm_userDeleted($user_id) {
	global $wpdb;
	$sql = $wpdb->prepare("DELETE FROM ".SUBSCRIBE_TABLE." WHERE user_id = %d;", $user_id);
	$wpdb->query($sql);
}


?>