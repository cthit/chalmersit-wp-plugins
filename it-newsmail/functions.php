<?php


/* Returns a string of all e-mails 
 * Expects an enumerated array with 
 * only the categories that applies
 * to the post.
 */
function get_emails_for_categories($cats){
	global $wpdb;
	
	$catString = "";
	$i = 0;
	foreach ($cats as $id) {
		if($i >= 0){
			$catString.= " OR ";
		}
		$catString .= "cat_id = ".$id;
		$i++;
	}
	$sql = "SELECT user_email FROM it_newsmail, it_users WHERE 
	it_users.ID = it_newsmail.user_id AND (".$catString.") GROUP BY user_email;";
	$res = $wpdb->get_results($sql);

	$emails = array();
	foreach ($res as $row) {
		$emails []= $row->user_email;
	}

	return $emails;
}


function get_all_categories() {
	$catObjs = get_categories(array(
		"hide_empty" => 0));

	$cats = array();
	foreach ($catObjs as $obj) {
		$cats[$obj->term_id] = array(
			"name" => $obj->name,
			"choice" => false);
	}
	

	return $cats;
}



/* Takes a post object from the request
 * processor and forms an array which
 * complies to the standard format for
 * cats, populated with choices
 */
function extract_choices($POST){
	$cats = get_all_categories();
	foreach ($POST as $key => $value) {
		if(strpos($key, "itnm") !== false)
			$cats[substr($key, 4)]['choice'] = true;
	}

	return $cats;
}

/* As above, but for choices already in DB
 */

function get_choices_for_user($user_id){
	global $wpdb;
	$sql = "SELECT cat_id FROM ".SUBSCRIBE_TABLE." WHERE user_id = ".$user_id;
	$res = $wpdb->get_results($sql);
	$cats = get_all_categories();

	foreach ($res as $obj) {
		$cats[$obj->cat_id]['choice'] = true;
	}

	return $cats;
}


?>