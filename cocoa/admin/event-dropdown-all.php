<?php
	/**
	* All Events Dropdown Script
	*
	* 
	*/

	// Set Variables for Error Logging  ------------------------------------------------------------------------
	$timestamp = date("d-m-y H:i:s");
	$filename = " event-dropdown-all.php ";  // Space added in front for alignment and spacing purposes
	
	// Get Database Connection Info and Set database table
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/cocoa/settings/db.php');
	
	// Search for active events.  Approved events after current date and time.
	// Create a prepared statement.  Avoids SQL injection.
	if ($result = $mysqli->query("SELECT event_id, name FROM events WHERE lab_registration = 'yes' ORDER BY s_time desc")) {
		echo '<option value="">Please Select an Event</option>';
		while($row = $result->fetch_assoc()) {
			echo '<option value="'.$row['event_id'].'">'.$row['name'].'</option>';
		}
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete QUERY with ".$mysqli->error." for EVENT SELECTION \n";
		error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
	}
	
	$mysqli->close();
?>