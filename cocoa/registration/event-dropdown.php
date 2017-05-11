<?php
	// Lindy and Blues Event Dropdown script
	// Version 1.0.1
	// Date Created:  12-07-2013 by Marc Longhenry
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-07-13 Marc Longhenry  		File Created, modified to use the database tables and files for Price Submission
	// 08-12-14		"					Added Error Logging
	
	// Set Variables for Error Logging  ------------------------------------------------------------------------
	$timestamp = date("d-m-y H:i:s");
	$filename = " event-dropdown.php ";  // Space added in front for alignment and spacing purposes
	
	// Get Database Connection Info and Set database table
	include('db.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
	// Search for active events.  Approved events after current date and time.
	// Create a prepared statement.  Avoids SQL injection.
	if ($result = $mysqli->query("SELECT event_id, name FROM events WHERE s_time >= CURRENT_TIMESTAMP")) {
		echo '<option value="">Please Select an Event</option>';
		while($row = $result->fetch_assoc()) {
			echo '<option value="'.$row['event_id'].'">'.$row['name'].'</option>';
		}
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete QUERY with ".$mysqli->error." for EVENT SELECTION \n";
		error_log($error_msg, 3, $root."/scripts/pay-system/error.log");
	}
	
	$mysqli->close();
?>