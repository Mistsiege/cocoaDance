<?php
	// Lindy and Blues Get Payment Status Script
	// Date Created:  09-24-2014 by Marc Longhenry
	//
	// This file is designed to retrieve payment status information.  Looks for $status from parent file to pre-select dropdown display.
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 09-24-14 Marc Longhenry  		File Created, modified to use the database tables and files for Payment Status retrieval

	// Set Variables for Error Logging  --------------------------------------------------------------------------------------------------------------------
	$timestamp = date("d-m-y H:i:s");
	$filename = " get-payment-status.php";  // Space added in front for alignment and spacing purposes
	$root = $_SERVER['DOCUMENT_ROOT'];
	
	// Include database information and create connection
	include("db.php");
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." MySQLi Connection Error: ".$mysqli->connect_errno." - ".$mysqli->connect_error." \n";
		error_log($error_msg, 3, $root."/scripts/event-system/error.log");
	}
	
	// Start Payment Status Display  ---------------------------------------------------------------------------------------------------------------------
	echo '<label for="status" title="Payment Status">Status</label><select name="status" id="status">';
	
	// Select available Payment Status
	if ($result = $mysqli->query("SELECT * FROM payment_status WHERE 1 ORDER BY status_id ASC")) {
		echo '<option value="">Please Select a Status</option>';
		while($row = $result->fetch_assoc()) {
			echo '<option value="'.$row['payment_status'].'"';
			if($status == $row['payment_status'] || status == $row['status_id']) {
				echo ' selected';
			}
			echo '>'.$row['payment_status'].'</option>';
		}
		echo '</select>';
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." MySQLi Error: ".$mysqli->errno." - ".$mysqli->error." \n";
		error_log($error_msg, 3, $root."/scripts/event-system/error.log");
	}

	mysqli_close($mysqli);
?>