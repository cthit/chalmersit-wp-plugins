<?php

require_once "inc.test_helpers.php";
require_once "../class.Booking.php";

global $wpdb;

date_default_timezone_set("Europe/Stockholm");
Booking::addLocations(array("Hubben", "Grupprummet"));
Booking::setupDB($wpdb->prefix . "room_bookings");

Booking::setConstraintsForRooms(array(
	"Hubben" => array(2, 3, 4),
	"Grupprummet" => array(1)
));

function bookingShouldHaveAttributes() {
	$booking = new Booking(array(
		"title" => "Sittning",
		"location" => "Hubben",
		"start_date" => strtotime("2012-01-23"),
		"end_date" => strtotime("2012-01-23"), 
		"user_id" => 1, 
		"phone" => "070658421"
	));

	assertEquals("Sittning", $booking->getTitle());
	assertEquals("Hubben", $booking->getLocation());
}

function testValidate() {
	$booking = new Booking(array(
		"title" => "Sittning",
		"location" => "Hubben",
		"start_date" => strtotime("2012-01-23"),
		"end_date" => strtotime("2012-01-23"), 
		"user_id" => 1, 
		"phone" => "0706574258"
	));

	assertTrue($booking->validate());
}

function testIncorrectValidate() {
	$incorrect_booking = new Booking(array(
		"title" => "",
		"location" => "",
		"start_date" => "",
		"end_date" => "", 
		"user_id" => 0, 
		"phone" => ""
	));
	assertFalse($incorrect_booking->validate());
}

function testIsBookingOnWeekend() {
	$booking = new Booking(array(
		"title" => "Hej",
		"location" => "Hubben",
		"start_date" => "2013-02-23",
		"end_date" => "2013-02-23",
		"user_id" => 1,
		"phone" => "070702202"
	));

	assertTrue($booking->isBookedOnWeekend());
}

function testBookBeforeTime() {
	$booking = new Booking(array(
		"title" => "Hej",
		"location" => "Hubben",
		"start_date" => "2013-02-21 15:00",
		"end_date" => "2013-02-21 16:00",
		"user_id" => 1,
		"phone" => "070702202"
	));

	assertFalse($booking->validate());
}

function testBookingLunchRoom() {
	$booking = new Booking(array(
		"title" => "Hej",
		"location" => "Grupprummet",
		"start_date" => "2013-02-21 12:00",
		"end_date" => "2013-02-21 13:00",
		"user_id" => 1,
		"phone" => "070702202"
	));

	assertTrue($booking->validate());
}

function testBookingLunchRoomIncorrect() {
	$booking = new Booking(array(
		"title" => "Hej",
		"location" => "Grupprummet",
		"start_date" => "2013-02-21 11:00",
		"end_date" => "2013-02-21 12:00",
		"user_id" => 1,
		"phone" => "070702202"
	));

	assertFalse($booking->validate());

	$booking2 = new Booking(array(
		"title" => "Hej",
		"location" => "Grupprummet",
		"start_date" => "2013-02-21 14:00",
		"end_date" => "2013-02-21 16:00",
		"user_id" => 1,
		"phone" => "070702202"
	));

	assertFalse($booking2->validate());
}

function testRoomConstraints() {
	$booking = new Booking(array(
		"title" => "Hej",
		"location" => "Grupprummet",
		"start_date" => "2013-02-21 11:00",
		"end_date" => "2013-02-21 12:00",
		"user_id" => 1,
		"phone" => "070702202"
	));
}

function testCollidingDates() {
	$booking = new Booking(array(
		"title" => "Sittning",
		"location" => "Hubben",
		"start_date" => "2012-03-23 17:00:00",
		"end_date" => "2012-03-23 23:00:00", 
		"user_id" => 1, 
		"phone" => "0706574258"
	));

	$colliding = new Booking(array(
		"title" => "Sittning 2",
		"location" => "Hubben",
		"start_date" => "2012-03-23 18:00:00",
		"end_date" => "2012-03-23 23:00:00", 
		"user_id" => 1, 
		"phone" => "0706574258"
	));

	$res = $booking->save();
	assertTrue($res);

	assertFalse($colliding->validate());
}

echo "RUN TESTS\n";
echo "-------------------\n";

#bookingShouldHaveAttributes();
#testValidate();
#testIncorrectValidate();
#testIsBookingOnWeekend();
#testBookBeforeTime();
#testBookingLunchRoomIncorrect();
#testBookingLunchRoom();
#testRoomConstraints();
testCollidingDates();

echo "-------------------\n";

?>