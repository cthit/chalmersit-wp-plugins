<?php
require_once "functions.php";
require_once "class.Booking.php";

class PartyBooking extends Booking {

	private static $table_parties;

	private $responsible_name;
	private $responsible_phone;
	private $has_warrant;

	function __construct($params = array()) {
		parent::__construct($params);

		$this->responsible_name = $params['responsible_name'];
		$this->responsible_phone = $params['responsible_phone'];
		$this->has_warrant = $params['has_warrant'];
	}

	public static function setupDB($rname) {
		self::$table_parties = $rname;
	}

	public function hasWarrant() {
		return $this->has_warrant;
	}

	public function getResponsibleName() {
		return $this->responsible_name;
	}

	public function getResponsiblePhone() {
		return $this->responsible_phone;
	}

	public function save() {
		global $wpdb;

		if(self::$table_parties == null || empty(self::$table_parties)) {
			error_log("Bookings: party table name cannot be empty");
			return false;
		}

		$id = parent::save();

		if($id === false) {
			return false;
		}
		else {
			if($wpdb->insert(self::$table_parties, array(
				"booking_id" => $id, 
				"contact_person_name" => $this->responsible_name,
				"contact_person_phone" => $this->responsible_phone,
				"has_warrant" => $this->has_warrant
			))) {
				return $wpdb->insert_id;
			}
		}

		return false;
	}
}

?>