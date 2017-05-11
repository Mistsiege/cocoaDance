<?php
	// Lindy and Blues Event Submission script
	// Version 1.0.3
	// Date Created:  12-05-2013 by Marc Longhenry
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-05-13 Marc Longhenry  		File Created, modified to use the database tables and files for Event Submission
	// 12-07-13	"						Modified to assign POST data to variables and use MySQLI statments with error messages
	// 03-26-14 "						Modified to utilize error.log error reporting, added values created in the even database
	//									during the LaBLove registration period.
	
	// Set Variables for Error Logging  ------------------------------------------------------------------------
	$timestamp = date("d-m-y H:i:s");
	$filename = " create-event.php ";  // Space added in front for alignment and spacing purposes	
	
	// Get Database Connection Info and Set database table
	include('db.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." Error: Failed to connect to MySQL. (".$mysqli->connect_errno.") ".$mysqli->connect_error." \n";
		error_log($error_msg, 3, "error.log");
	}
	
	// Receive POST data and assign to variables.
	$name = $_POST['name'];
	$s_time = $_POST['s_time'];
	$e_time = $_POST['e_time'];
	$location = $_POST['location'];
	$event_type = $_POST['event_type'];
	$description = $_POST['description'];
	$reg_start = $_POST['reg_start'];
	$reg_end = $_POST['reg_end'];
	$housing = $_POST['housing'];
	$housing_end = $_POST['housing_end'];
	$follow_flag = $_POST['follow_flag'];
	$lead_flag = $_POST['lead_flag'];
	$reg_flag = $_POST['reg_flag'];
	$extra_flag = $_POST['extra_flag'];
	$whois_flag = $_POST['whois_flag'];
	$contact_name = $_POST['contact_name'];
	$contact_email = $_POST['contact_email'];
	$website = $_POST['website'];
	$lab_event = $_POST['lab_event'];
	$lab_registration = $_POST['lab_registration'];

	// Create a New Event with given information.
	// Create a prepared statement.  Avoids SQL injection.
	if ($stmt = $mysqli->prepare("INSERT INTO events (name, s_time, e_time, location, event_type, description, reg_start, reg_end, housing, housing_end, follow_flag, lead_flag, reg_flag, extra_flag, whois_flag, contact_name, contact_email, website, lab_event, lab_registration)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
		if (!$stmt->bind_param("ssssssssssssssssssss", $name, $s_time, $e_time, $location, $event_type, $description, $reg_start, $reg_end, $housing, $housing_end, $follow_flag, $lead_flag, $reg_flag, $extra_flag, $whois_flag, $contact_name, $contact_email, $website, $lab_event, $lab_registration)) {
			$error_msg = $timestamp.$filename."Error: Binding parameters failed: (".$stmt->errno.") ".$stmt->error." \n";
			error_log($error_msg, 3, "error.log");
		}
		if (!$stmt->execute()) {
			$error_msg = $timestamp.$filename." Error: Execute failed: (".$stmt->errno.") ".$stmt->error." \n";
			error_log($error_msg, 3, "error.log");
   		}
		$stmt->close();
	} else {
		$error_msg = $timestamp.$filename." Error: Unable to create MySQLi Statment. (".$stmt->errno.") ".$stmt->error." \n";
		error_log($error_msg, 3, "error.log");	
	}
	
	// Redirect Submiter to Create/Edit page
	header("Location: /create-edit-event");

?>