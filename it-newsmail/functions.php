<?php
function get_categories_for_user($user_id){
	global $wpdb;
	$sql = "SELECT cat_id FROM ".IT_NEWSMAIL_TABLE." WHERE user_id = ".$user_id;
	$res = $wpdb->get_results($sql, ARRAY_N);

	return $map;
}

?>