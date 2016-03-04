<?php
	/** 
	* Payment Change script
	*
	* 
	*/
	
	// Establish Error Logging Details  ----------------------------------------------------------------------------------------------------------------------
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/cocoa/settings/db.php');
	$filename = " edit-payments.php";  // Space added in front for alignment and spacing purposes
		
	// Receive expected POST data and assign to variables.  ----------------------------------------------------
	$event_id = $_POST['event_id'];
	$registration_id = $_POST['registration_id'];
	$user_id = $_POST['user_id'];
	$payment_id = $_POST['payment_id'];
	$method = $_POST['method'];
	$checkout_id = $_POST['checkout_id'];
	$amount = $_POST['amount'];
	$item = $_POST['item'];
	$timestamp = $_POST['timestamp'];
	$status = $_POST['status'];
	$note = $_POST['note'];
	$notice = $_POST['notice'];

	// Check if Payment ID exists  ------------------------------------------------------------------------------
	if ($payment_id == 0) {  // If ID = 0, create a new payment  ------------------------------------------------
		if ($stmt = $mysqli->prepare("INSERT INTO payments (user_id, event_id, method, checkout_id, amount, item, timestamp, status, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
			if($stmt->bind_param("sssssssss", $user_id, $event_id, $method, $checkout_id, $amount, $item, $timestamp, $status, $note)) {
				if($stmt->execute()) {
					$stmt->close();
	// Once INSERT is successful, retrieve new Payment ID  ------------------------------------------------------
					if ($stmt = $mysqli->prepare("SELECT payment_id FROM payments WHERE user_id = ? AND event_id = ?")) {
						if($stmt->bind_param("ss", $user_id, $event_id)) {
							if($stmt->execute()) {
								/* bind result variables */
								if($stmt->bind_result($payment_id)) {
									/* fetch value */
									$stmt->fetch();
									$stmt->close();
	// Once SELECT is successful, Update Registration with new Payment ID  --------------------------------------  								
									if ($stmt = $mysqli->prepare("UPDATE registration SET payment_id = ? WHERE registration_id = ?")) {
										if($stmt->bind_param("ss", $payment_id, $registration_id)) {
											if($stmt->execute()) {
												$stmt->close();
											} else {
												$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for REGISTRANT PAYMENT UPDATE (After Insert) with ".$payment_id."\n";
												error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
											}
										} else {
											$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for REGISTRANT PAYMENT UPDATE (After Insert) with ".$payment_id."\n";
											error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
										}
									} else {
										$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for REGISTRANT PAYMENT UPDATE (After Insert) with ".$payment_id."\n";
										error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
									}
	// Finishing Payment ID SELECT Error Logging  ---------------------------------------------------------------								
								} else {
									$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for PAYMENT ID SELECT with ui".$user_id." - ei".$event_id." \n";
									error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
								}
							} else {
								$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for PAYMENT ID SELECT with ui".$user_id." - ei".$event_id." \n";
								error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
							}
						} else {
							$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for PAYMENT ID SELECT with ui".$user_id." - ei".$event_id." \n";
							error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
						}
					} else {
						$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for PAYMENT ID SELECT with ui".$user_id." - ei".$event_id." \n";
						error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
					}
	// Finishing Registration INSERT Error Logging  -------------------------------------------------------------				
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for REGISTRANT PAYMENT INSERT with ".$payment_id."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for REGISTRANT PAYMENT INSERT with ".$payment_id."\n";
				error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for REGISTRANT PAYMENT INSERT with ".$payment_id."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		}
	} else {  // If ID != 0, Update Payment Information based on the Registration_ID  --------------------------
		if ($stmt = $mysqli->prepare("UPDATE payments SET method = ?, checkout_id = ?, amount = ?, item = ?, timestamp = ?, status = ? WHERE payment_id = ? AND user_id = ? AND event_id = ?")) {
			if($stmt->bind_param("sssssssss", $method, $checkout_id, $amount, $item, $timestamp, $status, $payment_id, $user_id, $event_id)) {
				if($stmt->execute()) {
					$stmt->close();
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for REGISTRANT PAYMENT UPDATE with ".$payment_id."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for REGISTRANT PAYMENT UPDATE with ".$payment_id."\n";
				error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for REGISTRANT PAYMENT UPDATE with ".$payment_id."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		}
	}
	
	// Send Notice to Registrant if box is checked  ----------------------------------------------------
	if($notice != null and $notice == 'on') {
		require_once('email-change-payment.php');
	}
	
	// Redirect Submiter to Payment page  --------------------------------------------------------------
	header('Location: '.$root.'/admin/event-payments?event_id='.$event_id);

?>