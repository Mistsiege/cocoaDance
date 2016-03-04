<?php
	/* 
	* Change Payment E-mail Script
	*
	* This file works with the Admin Payments and Edit Payment files in our system to change a user's payment
	* information without having to do it manually in the database.  This file will take information submitted
	* to the Database and e-mail the user, notifying them that we have changed it (such as "hey we got your
	* check").
	*/

// Establish Error Logging Details  ----------------------------------------------------------------------------------------------------------------------
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/cocoa/settings/db.php');
	$filename = " email-change-payment.php";  // Space added in front for alignment and spacing purposes	
	
// Get Event Name/Contact E-mail from Database  ---------------------------------------------------------------------------------------------------------------------
	if ($stmt = $mysqli->prepare("SELECT name, contact_email FROM events WHERE event_id = ?")) {
		if($stmt->bind_param("s", $event_id)) {
			if($stmt->execute()) {
				/* bind result variables */
				if($stmt->bind_result($event_title, $contact_email)) {
				/* fetch value */
				$stmt->fetch();
				$stmt->close();
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for EVENT NAME SELECT with ".$event_id."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for EVENT NAME SELECT with ".$event_id."\n";
				error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for EVENT NAME SELECT with ".$event_id."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for EVENT NAME SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
	}  // End Select Event Name If
	
// Get Price Title from Database  ---------------------------------------------------------------------------------------------------------------------
	if ($stmt = $mysqli->prepare("SELECT title FROM prices WHERE price_id = ?")) {
		if($stmt->bind_param("s", $item)) {
			if($stmt->execute()) {
				/* bind result variables */
				if($stmt->bind_result($item_name)) {
				/* fetch value */
				$stmt->fetch();
				$stmt->close();
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for PRICE TITLE SELECT with ".$item."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for PRICE TITLE SELECT with ".$item."\n";
				error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for PRICE TITLE SELECT with ".$item."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for PRICE TITLE SELECT with ".$item."\n";
		error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
	}  // End Select Event Name If
	
// Get User e-mail from database  ---------------------------------------------------------------------
	if ($stmt = $mysqli->prepare("SELECT fname, lname, email FROM users WHERE user_id = ?")) {
		if($stmt->bind_param("s", $user_id)) {
			if($stmt->execute()) {
				/* bind result variables */
				if($stmt->bind_result($fname, $lname, $email)) {
				/* fetch value */
				$stmt->fetch();
				$stmt->close();
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for USER DATA SELECT with ".$user_id."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for EVENT USER DATA SELECT with ".$user_id."\n";
				error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for USER DATA SELECT with ".$user_id."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for USER DATA SELECT with ".$user_id."\n";
		error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
	}  // End Select User Data If

// Compose Registration E-mail  --------------------------------------------------------------------------------------------------------------------------
	$from = "phillylab@gmail.com";
	$subject = $event_title.": Payment Detail Change";
	$body = $fname." ".$lname.",<br /><br />We are notifying you that the payment details for your registration
		to ".$event_title." were changed.<br /><br />As of ".$timedisplay." your payment details were updated to ".$amount."
		paid via ".$method." for ".$item_name.".  Your new payment status is ".$status.".  If this is incorrect,
		or there is some information missing, please e-mail the committee at ".$contact_email.".<br /><br />";
	$body .= "If you would like to see any of your registration information, click <a href=\"http://www.lindyandblues.com/events/pay-for-event?registration_id=".$registration_id."\">here</a>.<br /><br />";
	$body .= "We're looking forward to seeing you!<br /><br />The ".$event_title." Organizers";
	require_once($root."/cocoa/services/email-function.php");
	$loginEmail = "phillylab@gmail.com";
	$email_password = "1212Lantern";
	$connection = connectEmail($loginEmail, $email_password);
	if($connection != null) { 
		$email_sent = sendEmail($connection, $from, $email, $subject, $body);
		$subject = "ORIGINALLY TO $email - " . $subject;
		$email_sent = sendEmail($connection, $from, "phillylab@gmail.com", $subject, $body);
		if(!$email_sent){
			echo "<br /><br />There was a problem sending your confirmation email! Send an email to <a href=\"mailto:lab@lindyandblues.com\">lab(at)lindyandblues(dot)com</a> and let us know!<br /><br />";
			$error_msg = $timestamp.$filename." Did not complete EMAIL SEND entry for USER DATA SELECT with ".$user_id."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		} else {
			$error_msg = $timestamp.$filename." Completed Payment Change for ".$fname." ".$lname." UID-".$user_id." EID-".$event_id." Amt-".$amount." Method-".$method." Item-".$item." Status-".$status." RegID-".$registration_id." \n";
			error_log($error_msg, 3, $root."/cocoa/admin/_payment-change.log");
		}
	} else {
		echo "<br /><br />We had a problem sending you a confirmation email! Send an email to <a href=\"mailto:lab@lindyandblues.com\">lab(at)lindyandblues(dot)com</a> and let us know!<br /><br />";
		$error_msg = $timestamp.$filename." Did not complete EMAIL CONNECTION entry for USER DATA SELECT with ".$user_id."\n";
		error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
    }
    
?>