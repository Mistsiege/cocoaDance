<?php
	/** 
	* Database and Timezone Information
	*
	* 
	*/

	$root = $_SERVER['DOCUMENT_ROOT'];
	
	// Set Default Timezone to EST in PHP
		if (!(date_default_timezone_set("America/New_York"))) {
			$error_msg1 = $timestamp.$filename." Failed to connect to set Timezone to EST PHP \n";
			error_log($error_msg, 3, $root."/cocoa/settings/_error.log");
		} 
	
	// Error Logging Information
		$timestamp = date("m-d-y H:i:s");  // 08-04-15 12:44:37
		$timedisplay = date("F jS, Y - g:i A");  // December 31st, 1969 - 7:00 PM
		$filename = " db.php";  // Space added in front for alignment and spacing purposes
	
	// Database Connection Information
		$hostname 	= "";	// host name
		$dbname 	= "";					// database name
		$username 	= "";					// username 
		$password 	= "";					// password	
	
	// Begin SQL Connection
		$mysqli = new mysqli($hostname, $username, $password, $dbname);
		if ($mysqli->connect_errno) {
			$error_msg = $timestamp.$filename." Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error."\n";
			error_log($error_msg, 3, $root."/cocoa/settings/_error.log");
		}
?>