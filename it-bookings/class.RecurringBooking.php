<?php
require_once "functions.php";
require_once "class.Booking.php";

class RecurringBooking extends Booking {

	private static $table_recurring_name;

	private $start_date;
	private $end_date;
	private $recurring_interval;

	function __construct($params = array()) {
		parent::__construct($params);

		$this->start_date = $this->stringToMysqlDatetime($params["start_recurring_date"]);
		$this->end_date = $this->stringToMysqlDatetime($params["end_recurring_date"]);
		$this->recurring_interval = $params['interval'];

	}

	public static function setupDB($rname) {
		self::$table_recurring_name = $rname;
	}

	public function save() {
		global $wpdb;

		if(self::$table_recurring_name == null || empty(self::$table_recurring_name)) {
			error_log("Bookings: Recurring table name cannot be empty");
			return false;
		}

		$id = parent::save();

		if(!$this->validate() || $id === false) {
			return false;
		}
		else {
			return $wpdb->insert(self::$table_recurring_name, array(
				"booking_id" => $id, 
				"repeat_interval" => $this->recurring_interval,
				"start_date" => $this->start_date,
				"end_date" => $this->end_date
			));
		}
	}
}

?>