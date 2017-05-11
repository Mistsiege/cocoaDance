<?php
	$timestamp = date("d-m-y H:i:s");
	$filename = " schedule-to-json.php";  // Space added in front for alignment and spacing purposes
	$root = $_SERVER['DOCUMENT_ROOT'];
    
	// Get Database Info and Set Event ID
	include($root.'/backend/dbstatic/db2.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);

	if($_POST['event_id']) {
		$event_id = $_POST['event_id'];
	} elseif ($_GET['event_id']) {
		$event_id = $_GET['event_id'];
	}
	
	// Retrieve Schedule Items with associated Event ID to be displayed.
    $schedule_output = array();
	if ($schedule_query = $mysqli->query("SELECT * FROM schedule WHERE event_id = $event_id ORDER BY s_time ASC")) {
		while($row = mysqli_fetch_assoc($schedule_query)) {
            $schedule_output[] = $row;
		}
		$schedule_query->close();
		$schedule_json = json_encode($schedule_output, JSON_PRETTY_PRINT);
		echo $schedule_json;
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for EVENT ID SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}

?>