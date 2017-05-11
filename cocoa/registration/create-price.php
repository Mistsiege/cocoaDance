<?php
	// Lindy and Blues Price Creation script
	// Version 1.0.1
	// Date Created:  12-07-2013 by Marc Longhenry
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-07-13 Marc Longhenry  		File Created, modified to use the database tables and files for Price Submission
	// 08-08-14		"					Added Display_Only and Type fields to the submission
	
	// Get Database Connection Info and Set database table
	include('db.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
	// Receive POST data and assign to variables.
	$event_id = $_POST['event_id'];
	$price = $_POST['price'];
	$follow_num = $_POST['follow_num'];
	$lead_num = $_POST['lead_num'];
	$general_num = $_POST['general_num'];
	$reg_start = $_POST['reg_start'];
	$reg_end = $_POST['reg_end'];
	$title = $_POST['title'];
	$description = $_POST['description'];
	$display_only = $_POST['display_only'];
	$type = $_POST['type'];

	// Create a New Price Level with given information.
	// Create a prepared statement.  Avoids SQL injection.
	if ($stmt = $mysqli->prepare("INSERT INTO prices (event_id, price, follow_num, lead_num, general_num, reg_start, reg_end, title, description, display_only, type)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
		if (!$stmt->bind_param("sssssssssss", $event_id, $price, 	$follow_num, $lead_num, $general_num, $reg_start, $reg_end, $title, $description, $display_only, $type)) {
   		echo "Binding parameters filed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!$stmt->execute()) {
    		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
   		}
		$stmt->close();
	} else {
		echo "Error, unable to create mysqli statement";	
	}
	
	// Redirect Submiter to Create/Edit page
	header("Location: /create-edit-event");

?>