<?php
/*
	General functions and template tags
 */

function get_bookings($from_today) {
	global $wpdb;

	if(!$from_today) {
		$query = "SELECT * FROM ".IT_BOOKING_TABLE.
			" ORDER BY start_time, end_time ASC";
	}else {
		$query = "SELECT * FROM ".IT_BOOKING_TABLE.
			" WHERE start_time > CURRENT_TIMESTAMP".
			" ORDER BY start_time, end_time ASC";
	}

	return $wpdb->get_results($query);
}

function get_past_bookings_for_group($group_id) {
	return __get_past_or_future_bookings(array(
		"booking_group" => $group_id
	), false);
}

function get_future_bookings_for_group($group_id) {
	return __get_past_or_future_bookings(array(
		"booking_group" => $group_id
	), true);
}

function get_bookings_for_user($user_id) {
	global $wpdb;
	$query = "SELECT * FROM ".IT_BOOKING_TABLE.
			" WHERE user_id = $user_id" .
			" ORDER BY start_time, end_time ASC";

	return $wpdb->get_results($query);
}

function get_past_bookings_for_user($user_id) {
	return __get_past_or_future_bookings(array("user_id"=>$user_id), false);
}

function get_future_bookings_for_user($user_id) {
	return __get_past_or_future_bookings(array("user_id"=>$user_id), true);
}

function __get_past_or_future_bookings($entity, $future) {
	if($entity == null) return null;

	global $wpdb;
	$values = array_values($entity);
	$keys = array_keys($entity);

	if(is_array($values[0])) {
		$id_condition = "$keys[0] = " . implode(" OR $keys[0] = ", $values[0]);
	}
	else {
		$id_condition = $keys[0] . " = ".$values[0];
	}

	$operator = ($future) ? ">" : "<";

	$query = "SELECT * FROM ".IT_BOOKING_TABLE.
				" WHERE ($id_condition) AND".
				" end_time $operator CURRENT_TIMESTAMP".
				" ORDER BY start_time, end_time ASC";

	return $wpdb->get_results($query);
}


/*
	Helpers
 */

function preserve_field($post_var, $fallback = "", $attr = "value"){
	if(isset($post_var) && !empty($post_var)) {
		echo ($attr != false) ? $attr . '="'.$post_var.'"' : $post_var;
	}
	else {
		echo ($attr != false) ? $attr . '="'.$fallback.'"' : $fallback;	
	}
}


/*
	Custom functions for use with the Groups WP plugin
 */

function getGroupsForUser($user_id) {
	if(!defined('GROUPS_FILE')) {
		return null;	
	}

	global $wpdb;
	$sql = "SELECT * FROM it_groups_group t1, it_groups_user_group t2 ".
			"WHERE t2.user_id = ".$user_id." AND t2.group_id = t1.group_id ORDER BY t2.group_id";

	return $wpdb->get_results($sql);
}

function getGroupIDsForUser($user_id) {
	if(!defined('GROUPS_FILE')) {
		return null;	
	}
	
	global $wpdb;

	$sql = "SELECT group_id FROM it_groups_user_group WHERE user_id = ".$user_id;
	$res = $wpdb->get_results($sql);
	$map = array();
	$cb = function($item) {
		return $item->group_id;
	};

	if($res) {
		$map = array_map($cb, $res);
	}
	else {
		return null;
	}

	return $map;
}	

?>