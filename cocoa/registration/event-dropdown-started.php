<?php
	// Lindy and Blues Event Dropdown (Started) script
	// Date Created:  12-07-2013 by Marc Longhenry
	//
	// This script will create a dropdown of its own with a list of currently available events to register into.
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-07-13 Marc Longhenry  		File Created, modified to use the database tables and files for Price Submission
	// 08-10-14		"					File taken from Event Dropdown and modified to the above.

	// Set Variables for Error Logging  ------------------------------------------------------------------------
	$timestamp = date("d-m-y H:i:s");
	$filename = " event-dropdown-started.php ";  // Space added in front for alignment and spacing purposes
	
	// Search Users using e-mail (assumed to be unique) go retrieve User_id for transaction processing.
	include('db.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		error_log($error_msg, 3, "error.log");
	}
	
	// Search for active events.  Approved events after current date and time.
	// Create a prepared statement.  Avoids SQL injection.
	if ($result = $mysqli->query("SELECT event_id, name FROM events WHERE reg_end >= CURRENT_TIMESTAMP AND reg_start <= CURRENT_TIMESTAMP")) {
?>
		<label for="event_id">List of Current Events:</label><select name="event_id" onchange="fillEventForm(this.value, 'registration', 'display')" required><option value="">Please Select an Event</option>
		<?php
		while($row = $result->fetch_assoc()) {
			echo '<option value="'.$row['event_id'].'">'.$row['name'].'</option>';
		}
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete QUERY with ".$mysqli->error." for EVENT (STARTED) SELECTION \n";
		error_log($error_msg, 3, $root."/scripts/pay-system/error.log");
	}
	$mysqli->close();
?>
	</select>
