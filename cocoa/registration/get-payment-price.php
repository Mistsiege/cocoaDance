<?php
	// Lindy and Blues Get Payment Price Script
	// Date Created:  09-24-2014 by Marc Longhenry
	//
	// This file is designed to retrieve payment price information.  Looks for $item and $event_id from parent file to pre-select dropdown display.
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 09-24-14 Marc Longhenry  		File Created, modified to use the database tables and files for Payment Price retrieval

	// Set Variables for Error Logging  --------------------------------------------------------------------------------------------------------------------
	$timestamp = date("d-m-y H:i:s");
	$filename = " get-payment-price.php";  // Space added in front for alignment and spacing purposes
	$root = $_SERVER['DOCUMENT_ROOT'];
	
	// Include database information and create connection
	include("db.php");
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." MySQLi Connection Error: ".$mysqli->connect_errno." - ".$mysqli->connect_error." \n";
		error_log($error_msg, 3, $root."/scripts/event-system/error.log");
	}
	
	// Start Payment Price Display  ---------------------------------------------------------------------------------------------------------------------
	echo '<label for="item" title="Payment Item">Item</label><select name="item" id="item">';
	
	// Select available Payment Items
	if ($result = $mysqli->query("SELECT price_id, title FROM `prices` WHERE event_id = $event_id")) {
		echo '<option value="">Please Select an Item</option>';
		while($row = $result->fetch_assoc()) {
			echo '<option value="'.$row['price_id'].'"';
			if($price_id == $row['price_id']) {
				echo ' selected';
			}
			echo '>'.$row['title'].'</option>';
		}
		echo '</select>';
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." MySQLi Error: ".$mysqli->errno." - ".$mysqli->error." \n";
		error_log($error_msg, 3, $root."/scripts/event-system/error.log");
		echo '<option value="">Error, Price ID not valid for Event</option></select>';
	}

	mysqli_close($mysqli);
?>