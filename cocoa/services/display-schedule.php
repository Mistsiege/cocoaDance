<?php
	// Establish Database Connection, Datetime, and Error Logging locations
	$timestamp = date("d-m-y H:i:s");
	$filename = " display-schedule.php";  // Space added in front for alignment and spacing purposes
	$root = $_SERVER['DOCUMENT_ROOT'];
	
	// Connect to the database
	include($root.'/backend/dbstatic/db2.php');

	if($_POST['event_id']) {
		$event_id = $_POST['event_id'];
	} elseif ($_GET['event_id']) {
		$event_id = $_GET['event_id'];
	}
	
	// Generate top text
	if ($result = $mysqli->query("SELECT name FROM events WHERE event_id = $event_id")) {
		$row = $result->fetch_assoc();
		echo '<div id="top" class="section-title">'.$event_name.' Schedule</div>';
		if ($update_text) {
			echo '<p class="update-box event-info-instance">'.$update_text.'</p>';
		}
	} else {
		$error_msg = $timestamp.$filename.' Failed to retrieve Event Name for Event ID = '.$event_id.' \n';
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}

	// Set Initial Schedule Day Buttons
	include('schedule-day-buttons.php');

	// Retrieve and Process Schedule JSON
	//include('schedule-data-format.php');
	
?>