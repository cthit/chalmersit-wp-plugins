<?php
/*
	Plugin Name: IT Bookings
	Plugin URI: http://chalmers.it
	Description: A bookings plugin for rooms
	Version: 1.0
	Author: Johan Brook
	Author URI: http://johanbrook.com
	License: MIT
*/

global $wpdb;
define("IT_BOOKING_TABLE", $wpdb->prefix . "room_bookings");
define("IT_RECURRING_BOOKING_TABLE", $wpdb->prefix . "room_recurring_bookings");
define("IT_PARTY_BOOKING_TABLE", $wpdb->prefix . "room_party_bookings");

define("IT_BOOKING_PATH", __DIR__);

$IT_BOOKINGS_TEMPLATE_PATH = IT_BOOKING_PATH."/templates/_bookings.php";

# Mail configuration

$mail_variables = array(
	"receivers" => array(),			# String array with mail addresses
	"party_receivers" => array()
);

add_action("init", "it_setup");
add_action("init", "new_booking");
add_action("wp_enqueue_scripts", "it_bookings_scripts");

register_activation_hook(__FILE__, 'it_bookings_activate');
register_deactivation_hook(__FILE__, 'it_bookings_deactivate');

require_once "functions.php";
require_once "class.Booking.php";
require_once "class.PartyBooking.php";
require_once "class.RecurringBooking.php";


function it_setup() {
	add_shortcode("bokning", "show_booking");

	Booking::setupDB(IT_BOOKING_TABLE);
	RecurringBooking::setupDB(IT_RECURRING_BOOKING_TABLE);
	PartyBooking::setupDB(IT_PARTY_BOOKING_TABLE);
}

function it_bookings_scripts() {
	wp_register_script('it_bookings', plugins_url("javascripts/bookings.js", __FILE__), array("jquery"), null, true);
	wp_enqueue_script('it_bookings');
}

function it_bookings_activate() {
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "CREATE TABLE IF NOT EXISTS ".IT_BOOKING_TABLE." (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		title varchar(255) NOT NULL,
		description text,
		phone varchar(255) NOT NULL,
		location varchar(255) NOT NULL,
		booking_group varchar(255),
		start_time datetime NOT NULL,
		end_time datetime NOT NULL,
		user_id bigint(20) unsigned NOT NULL,
		PRIMARY KEY (id)
	)";

	dbDelta($sql);

	$sql_recurring = "CREATE TABLE IF NOT EXISTS ".IT_RECURRING_BOOKING_TABLE." (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			booking_id mediumint(9) NOT NULL,
			repeat_interval mediumint(9) NOT NULL,
			start_date datetime NOT NULL,
			end_date datetime NOT NULL,

			PRIMARY KEY (id)
		)";

	dbDelta($sql_recurring);

	$sql_party_events = "CREATE TABLE IF NOT EXISTS ".IT_PARTY_BOOKING_TABLE." (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			booking_id mediumint(9) NOT NULL,
			contact_person_name bigint(20) NOT NULL,
			contact_person_phone varchar(255) NOT NULL,
			has_warrant BOOLEAN NOT NULL DEFAULT 0,

			PRIMARY KEY (id)
		)";

	dbDelta($sql_party_events);
   	
}


function it_bookings_deactivate() {
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "DROP TABLE ".IT_BOOKING_TABLE;
	dbDelta($sql);
	$sql_recurring = "DROP TABLE ".IT_RECURRING_BOOKING_TABLE;
	dbDelta($sql_recurring);

	$sql_party_events = "DROP TABLE ".IT_PARTY_BOOKING_TABLE;
	dbDelta($sql_party_events);
}


function new_booking() {
	if($_SERVER['REQUEST_METHOD'] == "POST" &&
		!empty($_POST['action']) &&
		$_POST['action'] == "new_booking" &&
		wp_verify_nonce($_POST['_wpnonce'], "it-new-booking")) {

		if(!is_user_logged_in()) return;

		global $errors, $notice, $current_user;

		$user_id = $current_user->ID;
		$title = $_POST['title'];
		$desc = $_POST['description'];
		$start_date = $_POST['start_date'] . $_POST['start_time'];
		$end_date = $_POST['end_date'] . $_POST['end_time'];
		$phone = $_POST['booking_phone'];
		$location = $_POST['location'];
		$group = $_POST['user_groups'];
		$repeat_booking = ($_POST['booking_repeat'] == "yes") ? true : false;

		$is_party_booking = ($_POST['booking_is_party'] == "yes") ? true : false;
		$party_responsible_name = $_POST['party_responsible_name'];
		$party_responsible_phone = $_POST['party_responsible_phone'];
		$party_has_warrant = ($_POST['party_has_warrant'] == "yes") ? true : false;

		$params = array(
				"title" => $title,
				"location" => $location,
				"start_date" => $start_date,
				"end_date" => $end_date,
				"user_id" => $user_id,
				"phone" => $phone,
				"description" => $desc,
				"group" => $group
			);

		if($is_party_booking) {
			$booking = new PartyBooking($params + array(
				"responsible_name" => $party_responsible_name,
				"responsible_phone" => $party_responsible_phone,
				"has_warrant" => $party_has_warrant
			));
		}
		else {
			$booking = new Booking($params);
		}

		if($booking->save()) {
			$notice = "Du har bokat ".$location;

			if(send_mail($booking)) {
				$notice .= ". Ett mail har skickats till ansvarig";
			}

			add_action("it_bookings_feedback", "it_bookings_notice");
		}
		else {
			$errors = $booking->errors();
			add_action("it_bookings_feedback", "it_bookings_errors");
		}
	}
}


function send_mail($booking) {
	global $mail_variables;

	$receivers = $mail_variables['receivers'];

	$subject = sprintf('Bokning av %1$s: "%2$s"', $booking->getLocation(), $booking->getTitle());

	$message = "Titel:\t".$booking->getTitle()."\n";
	$message .= "Lokal:\t".$booking->getLocation()."\n";
	$message .= "Startdatum:\t".$booking->getStartDate()."\n";
	$message .= "Slutdatum:\t".$booking->getEndDate()."\n";
	$message .= "Telefon:\t".$booking->getPhone()."\n\n";
	$message .= "Beskrivning:\n". $booking->getDescription() ."\n\n";

	if($booking instanceof PartyBooking) {
		$message .= "Festanmält:\tJa\n";
		$message .= "Alkoholtillstånd:\t" . (($booking->hasWarrant()) ? "Ja" : "Nej") . "\n";
		$message .= "Festansvarig:\t" . ($booking->getResponsibleName()) ."\n";
		$message .= "Festansvarig, tel:\t" . (($booking->getResponsiblePhone())) . "\n";
	}

	$did_send_mail = wp_mail($receivers, $subject, $message);

	return $did_send_mail;
}

function send_party_email($booking) {

}


function it_bookings_errors() {
	global $errors;?>

	<div class="booking-error message-warning">
		<ul class="list">
		<?php foreach($errors as $key => $msg) : ?>
			<li><?php echo $msg;?></li>
		<?php endforeach;?>
		</ul>
	</div>

	<?php
}

function it_bookings_notice() {
	global $notice;?>

	<div class="booking-notice message-positive">
		<?php echo $notice;?>
	</div>
	<?php
}

function set_booking_emails($emails) {
	global $mail_variables;
	$mail_variables['receivers'] = $emails;
}

function set_party_booking_emails($emails) {
	global $mail_variables;
	$mail_variables['party_receivers'] = $emails;
}

function show_booking($attr, $content = null) {
	global $IT_BOOKING_FORM_PATH;
	ob_start();
	include IT_BOOKING_PATH."/templates/_bookings.php";
	return ob_get_clean();
}

?>