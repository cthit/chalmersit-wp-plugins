<?php


/* Returns a string of all e-mails 
 * Expects an enumerated array with 
 * only the categories that applies
 * to the post.
 */
function get_emails_for_category($cat_id){
	global $wpdb;
	
	$sql = "SELECT user_email FROM it_newsmail, it_users WHERE 
	it_users.ID = it_newsmail.user_id AND (cat_id = -1) GROUP BY user_email;";
	$res = $wpdb->get_results($sql);

	$emails = array();
	foreach ($res as $row) {
		$emails []= $row->user_email;
	}

	return $emails;
}

function get_all_categories() {
	$catObjs = get_categories();

	$cats = array();
	$cats[-1] = array(
		"name" => "Alla nyheter",
		"choice" => false);
	foreach ($catObjs as $obj) {
		$cats[$obj->term_id] = array(
			"name" => $obj->name,
			"choice" => false);
	}
	

	return $cats;
}

function extract_cats_from_post($post_id){
	// TODO: Find out how cats are extracted, and in what form

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
	$sql = "SELECT cat_id FROM ".IT_NEWSMAIL_TABLE." WHERE user_id = ".$user_id;
	$res = $wpdb->get_results($sql);
	$cats = get_all_categories();

	foreach ($res as $obj) {
		$cats[$obj->cat_id]['choice'] = true;
	}

	return $cats;
}
?>

