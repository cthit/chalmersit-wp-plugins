<?php

require_once "functions.php";

class Booking {

	private static $table_name;

	private static $allowed_locations = array();
	private static $room_constraints = array();
	private static $super_group;
	
	private $title;
	private $location;
	private $start_timestamp;
	private $startHr;
	private $end_timestamp;
	private $endHr;
	private $is_repeating;

	private $booker_phone;
	private $booker_group;
	private $description;

	protected $errors;
	private $user_groups;

	private $booking_time_weekday;
	private $booking_time_weekend;
	private $lunchroom_booking_start;
	private $lunchroom_booking_end;

	

	function __construct($params = array()) {

		$this->title = $params["title"];
		$this->location = $params["location"];
		$this->start_timestamp = $this->stringToMysqlDatetime($params["start_date"]);
		$this->startHr = strtotime($params["start_date"]);
		$this->end_timestamp = $this->stringToMysqlDatetime($params["end_date"]);
		$this->endHr = strtotime($params["end_date"]);
		$this->user = $params["user_id"];
		$this->booker_phone = preg_replace('/[^0-9]/s', '', $params["phone"]);
		$this->description = $params['description'];
		$this->is_repeating = $params['is_repeating'];
		$this->booker_group = ($params['group'] != -1) ? $params['group'] : "";

		$this->errors = array();

		$this->user_groups = getGroupIDsForUser($this->user);

		$this->booking_time_weekday = 17;
		$this->booking_time_weekend = 00;
		$this->lunchroom_booking_start = 12;
		$this->lunchroom_booking_end = 13;

		
	}

	protected function stringToMysqlDatetime($str) {
		return date("Y-m-d H:i:s", strtotime($str));
	}

	private function isUserInSuperGroup() {
		return in_array(self::$super_group, $this->user_groups);
	}

	private function getBookingsInRange($start, $end, $location) {
		global $wpdb;
		$sql = "SELECT * FROM ".self::$table_name .
				" WHERE '$start' < end_time AND ".
				"'$end' > start_time AND location = '$location'";

		return $wpdb->get_results($sql);
	}

	public function toString() {
		return "$this->title, $this->location, $this->start_timestamp, $this->end_timestamp, ".
				"$this->user, $this->booker_phone";
	}

	public static function setupDB($tname) {
		self::$table_name = $tname;
	}

	public static function addLocation($loc) {
		self::$allowed_locations[] = $loc;
	}

	public static function addLocations($locs) {
		foreach($locs as $loc)
			self::addLocation($loc);
	}

	public static function getLocations() {
		return self::$allowed_locations;
	}

	public static function setSuperGroup($group_id) {
		self::$super_group = $group_id;
	}

	# Use -1 to signify that no group constraints is set on the room
	# e.g. everybody may book it.
	public static function setConstraintsForRooms($constraints) {
		self::$room_constraints = $constraints;
	}


	/* Methods */

	public function getTitle() {
		return $this->title;
	}

	public function getLocation() {
		return $this->location;
	}

	public function getStartDate() {
		return $this->start_timestamp;
	}

	public function getEndDate() {
		return $this->end_timestamp;
	}

	public function getUserID() {
		return $this->user;
	}

	public function getPhone() {
		return $this->booker_phone;
	}

	public function getGroup() {
		return $this->booker_group;
	}

	public function getDescription() {
		return $this->description;
	}

	public function isBookedOnWeekend() {
		return (date('N', strtotime($this->start_timestamp)) >= 6);
	}

	public function save() {
		global $wpdb;

		if(self::$table_name == null || empty(self::$table_name)) {
			error_log("Bookings: Table name cannot be empty");
			return false;
		}

		if(!$this->validate()) {
			return false;
		}

		$wpdb->insert(self::$table_name, array(
			"title" => $this->title, 
			"description" => $this->description, 
			"user_id" => $this->user,
			"booking_group" => $this->booker_group,
			"phone" => $this->booker_phone,
			"location" => $this->location,
			"start_time" => $this->start_timestamp,
			"end_time" => $this->end_timestamp
		));

		return $wpdb->insert_id;
	}

	public function validate() {

		$forbidden_groupRoom = array(8,9,10,11,13,14,15,16,32,33,34,35,37,38,39,40,56,57,58,59,61,62,63,64,80,81,82,83,85,86,87,88,104,105,106,107,109,110,111,112);
		$forbidden_hubben = array(8,9,10,11,12,13,14,15,16,32,33,34,35,36,37,38,39,40,56,57,58,59,60,61,62,63,64,80,81,82,83,84,85,86,87,88,104,105,106,107,108,109,110,111,112);

		# Title can't be empty
		if(empty($this->title)) {
			$this->errors["empty_title"] = "Du måste fylla i en titel";
		}

		# Phone can't be empty
		if(empty($this->booker_phone)) {
			$this->errors["empty_phone"] = "Du måste fylla i ditt telefonnummer";
		}

		if(preg_match("/[^0-9]/", $this->booker_phone)) {
			$this->errors['incorrect_phone'] = "Telefonnumret du angav är inte korrekt";
		}

		# Start or end time can't be before current time 

		if(($current = time()) && strtotime($this->start_timestamp) < $current || 
				strtotime($this->end_timestamp) < $current) {
			$this->errors['invalid_date'] = "Du kan inte boka i dåtid utan tidsmaskin";
		}

		# Start time can't be after end time
		if(strtotime($this->start_timestamp) > strtotime($this->end_timestamp)) {
			$this->errors["dates_overlapping"] = "Datumen överlappar varandra";
		}

		if(!in_array($this->location, self::$allowed_locations)) {
			$this->errors['location_not_allowed'] = "Lokalen finns inte: $this->location.";
		}

		$startHr = intval($this->startHr/3600);
		$endHr = intval($this->endHr/3600);
		if($this->location == "Hubben") {
			for ($i = $startHr; $i < $endHr ; $i++) { 
				$hr = ($i%168)-96;
				if (in_array($hr, $forbidden_hubben)) {			
					$this->errors["too_early_weekday_booking"] = "Du kan inte boka ".$this->location." innan $this->booking_time_weekday på vardagar";
					break;
				}
			}
		}
		if($this->location == "Grupprummet") {
			for ($i=$startHr; $i < $endHr; $i++) { 
				$hr = ($i%168)-96;
				if (in_array($hr, $forbidden_groupRoom)) {
					$this->errors['lunchroom_overlap'] = "Du kan inte boka ".$this->location." utanför tiderna ".
						$this->lunchroom_booking_start."-".$this->lunchroom_booking_end. ", ".$this->booking_time_weekday."-";
					break;
				}
			}
		}

		# Check group constraints on rooms

		if( !empty(self::$room_constraints)) {

			$groups = self::$room_constraints[$this->location];

			if($this->location == "Hubben" && $this->booker_group == "") {
				$this->errors['private_hubben'] = "Du kan inte boka Hubben som privatperson";
			}

			# -1 signifies no restriction on the room
			if($groups != -1) {
				$res = array_intersect($this->user_groups, $groups);

				if(empty($res)) {
					$this->errors['privileges'] = "Du har inte behörighet att boka $this->location";
				}
			}
		}

		if($this->getBookingsInRange($this->start_timestamp, $this->end_timestamp, $this->location) != null) {
			$this->errors['colliding_dates'] = "Datumet du försökte boka är upptaget för $this->location";
		}

		if(count($this->errors) > 0) {
			return false;
		}

		return true;
	}

	public function errors() {
		return $this->errors;
	}

}

?>