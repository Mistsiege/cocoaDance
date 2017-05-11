<?php
	/** 
	* File generated to handle Schedule Display Venues for events
	* 
	* Date		Developer				Modification Made
	* --------- ----------------------- --------------------------------------------------------------
	* 04-08-15	Marc Longhenry			Original File made to support tabular display of available weekend scheduling venues
	*/
	$timestamp = date("d-m-y H:i:s");
	$filename = " display-schedule-venues.php";  // Space added in front for alignment and spacing purposes
	$root = $_SERVER['DOCUMENT_ROOT'];
	
	// Connect to the database and retrieve Event ID
	include($root.'/backend/dbstatic/db.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
	if($_POST['event_id']) {
		$event_id = $_POST['event_id'];
	} elseif ($_GET['event_id']) {
		$event_id = $_GET['event_id'];
		
	}	
	
	// Retrieve Venue Information for associated Event ID to be displayed.
	if ($result = $mysqli->query("SELECT DISTINCT venues.abbreviation, venues.name, venues.venue_id, schedule.real_date FROM venues, schedule WHERE schedule.event_id = 20 AND schedule.venue_id = venues.venue_id ORDER BY name ASC")) {
		while($row = $result->fetch_assoc()) {
			$results[] = $row;
		}
		$result->close();
		print json_encode($results);
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for EVENT ID SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
?>