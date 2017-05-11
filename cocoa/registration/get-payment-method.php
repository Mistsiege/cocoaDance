<?php
	// Lindy and Blues Get Payment Method Script
	// Date Created:  09-24-2014 by Marc Longhenry
	//
	// This file is designed to retrieve payment method information.  Looks for $method from parent file to pre-select dropdown display.
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 09-24-14 Marc Longhenry  		File Created, modified to use the database tables and files for Payment Method retrieval

	// Set Variables for Error Logging  --------------------------------------------------------------------------------------------------------------------
	$timestamp = date("d-m-y H:i:s");
	$filename = " get-payment-method.php";  // Space added in front for alignment and spacing purposes
	$root = $_SERVER['DOCUMENT_ROOT'];
	
	// Include database information and create connection
	include("db.php");
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." MySQLi Connection Error: ".$mysqli->connect_errno." - ".$mysqli->connect_error." \n";
		error_log($error_msg, 3, $root."/scripts/event-system/error.log");
	}
	
	// Start Payment Method Display  ---------------------------------------------------------------------------------------------------------------------
	echo '<label for="method" title="Payment Method">Method</label><select name="method" id="method">';
	
	// Select available Payment Methods
	if ($result = $mysqli->query("SELECT * FROM payment_method WHERE 1 ORDER BY method_id ASC")) {
		echo '<option value="">Please Select a Method</option>';
		while($row = $result->fetch_assoc()) {
			echo '<option value="'.$row['payment_method'].'"';
			if($method == $row['payment_method'] || $method == $row['method_id'] || $method == $row['abreviation']) {
				echo ' selected';
			}
			echo '>'.$row['payment_method'].'</option>';
		}
		echo '</select>';
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." MySQLi Error: ".$mysqli->errno." - ".$mysqli->error." \n";
		error_log($error_msg, 3, $root."/scripts/event-system/error.log");
	}

	mysqli_close($mysqli);
?>