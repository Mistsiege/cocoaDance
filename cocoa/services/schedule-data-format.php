<?php
	// Establish Database Connection, Datetime, and Error Logging locations
	$timestamp = date("d-m-y H:i:s");
	$filename = " schedule-data-format.php";  // Space added in front for alignment and spacing purposes
	$root = $_SERVER['DOCUMENT_ROOT'];
	
	// Connect to the database
	include($root.'/backend/dbstatic/db2.php');

	if($_POST['event_id']) {
		$event_id = $_POST['event_id'];
	} elseif ($_GET['event_id']) {
		$event_id = $_GET['event_id'];
	}
	
	// Retrieve and Process Schedule JSON
	include('schedule-to-json.php');

    foreach ($schedule_json->values as $schedule) {
        echo $schedule->title."\n";     
    } 
?>