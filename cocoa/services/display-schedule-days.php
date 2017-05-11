<?php
	/** 
	* File generated to handle Schedule Display Days for events
	* 
	* Date		Developer				Modification Made
	* --------- ----------------------- --------------------------------------------------------------
	* 01-02-14	Marc Longhenry			Original File made to support tabular display of available weekend scheduling
	* 08-01-15		"					Modified error logging to use Services directory
	*/
	$timestamp = date("d-m-y H:i:s");
	$filename = " display-schedule-days.php";  // Space added in front for alignment and spacing purposes
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
	
	// Retrieve Schedule Items with associated Event ID to be displayed.
	if ($result = $mysqli->query("SELECT s_time, e_time FROM events WHERE event_id = $event_id")) {
		while($row = $result->fetch_assoc()) {
			$s_time = $row['s_time'];
			$e_time = $row['e_time'];
		}
		$result->close();

		// Determine and Return array of days inbetween date range
		$current = strtotime($s_time);
		$last = strtotime($e_time);
		$step = '+1 day';
		$first = 0;
		$dates = '[';
		while( $current <= $last ) {
			if($first === 1) { $dates .= ','; }
			$dates .= '{"dow":"'.date('l', $current).'","real_date":"'.date('Y-m-d', $current).'"}';
			$current = strtotime($step, $current);
			$first = 1;
		}
		$dates .= ']';
		print $dates;
		
	// Error Handling	
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for EVENT ID SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
?>