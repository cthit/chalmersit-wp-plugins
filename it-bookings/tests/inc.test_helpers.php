<?php

function assertEquals($expected, $actual) {
	$test = get_caller_function();

	if($expected == $actual) {
		echo "PASSED: $test | $actual\n";
	}
	else {
		echo "FAILED: $test | $actual\n";
	}
}


function assertTrue($value) {
	$test = get_caller_function();

	if($value === true) {
		echo "PASSED: $test | $value\n";
	}
	else {
		echo "FAILED: $test | $value\n";
	}
}

function assertFalse($value) {
	$test = get_caller_function();

	if($value !== true) {
		echo "PASSED: $test | $value\n";
	}
	else {
		echo "FAILED: $test | $value\n";
	}
}

function get_caller_function($level = 2) {
	$trace = debug_backtrace();
	return $trace[$level]['function'];
}

?>