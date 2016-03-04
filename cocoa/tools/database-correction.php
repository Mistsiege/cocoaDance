<?php
	// Set Variables for Error Logging  ------------------------------------------------------------------------
	// Include Database Information and begin Database Connection
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/cocoa/settings/db.php');
	$filename = " database-correction.php ";  // Space added in front for alignment and spacing purposes
	
	// Search for active registration IDs and associated user_ids and roles
	$housing;
	$input = 0;
	if ($result = $mysqli->query("SELECT registration.registration_id, registration.housing_id, payments.payment_id, payments.status, housing.housing_option FROM registration, payments, housing WHERE registration.payment_id = payments.payment_id AND registration.housing_id = housing.housing_id AND registration.event_id = 18 AND (payments.status = 'Paid' OR payments.status LIKE 'Comped%') AND housing.housing_option = 'need'")) {
		while($row = $result->fetch_assoc()) {
			$housing[$input] = $row['housing_id'];
			echo "Housing_ID: ".$housing[$input]." - ";
			echo "Option: ".$row['housing_option']." - ";
			echo "Payment Status: ".$row['status']." - Found Successfully.\n";
			$input++;
		}
		$result->close();		
	} else {
		$error_msg = $timestamp.$filename." Did not complete QUERY with ".$mysqli->error." for EVENT SELECTION \n";
		error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
	}
	
	$output = 0;
	while($output <= $input) {
		if ($stmt = $mysqli->prepare("UPDATE housing SET guaranteed = 'yes' WHERE housing_id = ?")) {
			$stmt->bind_param("s", $housing[$output]);
			$stmt->execute();
			$stmt->close();
		}
		echo "Housing ".$housing[$output]." updated to Guaranteed Successfully.\n";
		$output++;
	}
	
	$mysqli->close();
?>