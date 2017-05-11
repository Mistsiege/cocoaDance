<?php
	// Lindy and Blues Get Payment Info Script
	// Version 1.0.1
	// Date Created:  06-26-2014 by Marc Longhenry
	//
	// This file is designed to retrieve payment (and associated) information for a given registration number selected
	// from a drop down on the Payment page.
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 06-26-14 Marc Longhenry  		File Created, modified to use the database tables and files for Payment Info retrieval

	// Set Variables for Error Logging  --------------------------------------------------------------------------------------------------------------------
	$timestamp = date("d-m-y H:i:s");
	$filename = " get-payment-info.php";  // Space added in front for alignment and spacing purposes
	
	// Receive Price ID from Javascript submission and GET
	$registration_id = intval($_GET['q']);

	// Include database information and create connection
	include("db.php");
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." MySQLi Connection Error: ".$mysqli->connect_errno." - ".$mysqli->connect_error." \n";
		error_log($error_msg, 3, "scripts/event-system/error.log");
	}
	
	// Select Registration Information based on the Registration_ID
	if ($stmt = $mysqli->prepare("SELECT registration.user_id, registration.payment_id, registration.event_id, registration.price_id, users.fname, users.lname, users.email, payments.method, payments.checkout_id, payments.amount, payments.item, payments.timestamp, payments.status, payments.note FROM registration, users, payments WHERE registration.registration_id = ? AND registration.user_id = users.user_id AND registration.payment_id = payments.payment_id")) {
		if($stmt->bind_param("s", $registration_id)) {
			if($stmt->execute()) {
				/* bind result variables */
				if($stmt->bind_result($user_id, $payment_id, $event_id, $price_id, $fname, $lname, $email, $method, $checkout_id, $amount, $item, $timestamp, $status, $note)) {
				/* fetch value */
				$stmt->fetch();
				$stmt->close();
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry for REGISTRANT PAYMENT SELECT with ".$registration_id."\n";
					error_log($error_msg, 3, "error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry for REGISTRANT PAYMENT SELECT with ".$registration_id."\n";
				error_log($error_msg, 3, "error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry for REGISTRANT PAYMENT SELECT with ".$registration_id."\n";
			error_log($error_msg, 3, "error.log");
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry for REGISTRANT PAYMENT SELECT with ".$registration_id."\n";
		error_log($error_msg, 3, "error.log");
	}

		// Generate Payment Info Display  ---------------------------------------------------------------------------------------------------------------------
	echo '<div id="payment-user-info">
		User Information is not changeable, but is display to identify the person who\'s payment you are changing.<br />
		<label for="fname">First</label><input type="text" name="fname" id="fname" value="'.$fname.'" disabled>
		<label for="lname">Last</label><input type="text" name="lname" id="lname" value="'.$lname.'" disabled><br />
		<label for="reg_id">Registration ID</label><input type="text" name="registration_id" id="registration_id" value="'.$registration_id.'" disabled>
		<label for="email">E-Mail</label><input type="text" name="email" id="email" value="'.$email.'" disabled>
	</div>
	<div id="payment-details">';
		include('get-payment-method.php');
	echo '<label for="checkout_id" title="Checkout ID for GW/PP, who received the Check, or the reason for being Comped">Checkout ID</label><input type="text" name="checkout_id" id="checkout_id" value="'.$checkout_id.'"><br />
		
		<label for="amount" title="Amount Paid (may include GW/PP fees)">Amount</label><input type="text" name="amount" id="amount" value="'.$amount.'">';
		include('get-payment-price.php');

// Need to change the timestamp to work with SQL formatting
	$timestamp = date("Y-m-d H:i:s");
	
	echo '<label for="timestamp" title="Timestamp of Payment Entry, yyyy-mm-dd hh:mm:ss, will set the current time if entry is blank">Timestamp</label><input type="text" name="timestamp" id="timestamp" value="'.$timestamp.'">';
		include('get-payment-status.php');
	echo '<br /><label for="note" title="Notes for Payment or Reasons for Comped">Notes</label><input type="text" name="note" id="note" value="'.$note.'"><br />
	</div>
	<div id="payment-change-submit">
		<input type="hidden" name="user_id" id="user_id" value="'.$user_id.'">
		<input type="hidden" name="payment_id" id="payment_id" value="'.$payment_id.'">
		<input type="hidden" name="event_id" id="event_id" value="'.$event_id.'">
		<label for="notice" title="Send an E-mail Notification to the User of this change">E-mail User about Change in Payment Info</label><input type="checkbox" name="notice" id="notice" checked>
		<input type="submit" value="Edit Payment">
	</div>';
?>