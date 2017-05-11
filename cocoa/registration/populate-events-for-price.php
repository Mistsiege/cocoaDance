<?php
	// Lindy and Blues Event Population for Price Creation script
	// Version 1.0.1
	// Date Created:  12-07-2013 by Marc Longhenry
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-07-13 Marc Longhenry  		File Created, modified to use the database tables and files for populating events to create prices
	
	// Get Database Connection Info and Set database table
	include('db.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
		if ($stmt = $mysqli->prepare("INSERT INTO prices (event_id, price, follow_num, lead_num, general_num, reg_start, reg_end, title, description)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
		if (!$stmt->bind_param("ssssssssss", $event_id, $price,	$follow_num, $lead_num, $general_num, $reg_start, $reg_end, $title, $description)) {
   		echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!$stmt->execute()) {
    		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
   		}
		$stmt->close();
	} else {
		echo "Error, unable to create mysqli statement";	
	}
?>