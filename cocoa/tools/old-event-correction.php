<?php
	// Set Variables for Error Logging  ------------------------------------------------------------------------
	// Include Database Information and begin Database Connection
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/cocoa/settings/db.php');
	$filename = " database-correction.php ";  // Space added in front for alignment and spacing purposes
	
	// Search for active registration IDs and associated user_ids and roles
	$input = 0;
	$user_id;
	$timestamp;
	$item;
	$amount;
	$event_id = 23;
	$method = 'UK';
	$checkout_id = 'Unknown';
	$status = 'Paid';
	$note = 'No payment information is known this far back, and is assumed to be paid';
	if ($result = $mysqli->query("SELECT users.user_id, bluesmuse_2009.date_registered, prices.title, prices.price FROM bluesmuse_2009, users, prices WHERE bluesmuse_2009.email = users.email AND prices.price_id = 108")) {
		while($row = $result->fetch_assoc()) {
			$user_id[$input] = $row['user_id'];
			$timestamp[$input] = $row['date_registered'];
			$item[$input] = $row['title'];
			$amount[$input] = $row['price'];
			echo 'Muse 2009 User-'.$input.': '.$user_id[$input].' *** '.$timestamp[$input].'-'.$row['date_registered'].' found with information: <br>';
			$input++;
		}
		$result->close();		
	} else {
		$error_msg = $timestamp.$filename." Did not complete QUERY with ".$mysqli->error." for INITIAL QUERY \n";
		error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
	}
	
	// Start Update Process
	$output = 0;
	while($output <= $input) {
		
		// Check for existing User by e-mail
		/* $user_id = 0;
		if ($stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email = ?")) {
			if($stmt->bind_param("s", $email[$output])) {
				if($stmt->execute()) {
					if($stmt->bind_result($user_id)) {
						$stmt->fetch();
						$stmt->close();
					} else {
						$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
						error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
				error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		} */
		
		// If user found, ignore entry, otherwise begin updating
		// if($user_id != null || $user_id != 0) {
		//	echo "User-".$user_id." Found with email ".$email[$output].".  Not updated. <br>";
		// } else {
			$country = '';
			if ($stmt = $mysqli->prepare("INSERT INTO payments (user_id, event_id, method, checkout_id, amount, item, timestamp, status, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
				if($stmt->bind_param("sssssssss", $user_id[$output], $event_id, $method, $checkout_id, $amount[$output], $item[$output], $timestamp[$output], $status, $note)) {
					if($stmt->execute()) {
						echo "User ".$user_id[$output]." ".$timestamp[$output]." Inserted. <br>";
						$stmt->close();
					} else {
						$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for NEW USER ENTRY with ".$email[$output]."\n";
						error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for NEW USER ENTRY with ".$email[$output]."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for NEW USER ENTRY with ".$email[$output]."\n";
				error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
			}
		// }
		$output++;
		
	} // End Update Process
	
	$mysqli->close();
?>