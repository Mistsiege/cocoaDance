<?php
// Lindy and Blues Registration Email Script
// Version 1.0.1
// Date Created:  06-26-2014 by Marc Longhenry
//
// This file works with the Process User function to generate an e-mail with registration information and
// a registration link to the new user.
// 
// Date		Developer				Modification Made
// --------- ----------------------- --------------------------------------------------------------
// 02-21-14	Marc Longhenry			Original Download Made.
// 07-11-14		"					Modifications made to include DOCUMENT_ROOT and added $root and mysqli->error to logging
// 08-12-15		"					Added in housing information from Pay-For-Events, did not activate it as it needs to be formatted properly
// 08-16-15		"					Removed housing information, made a mention to use the link to access the registration page where everything is properly formatted.

// Include Database Information  ----------------------------------------------------------------------------
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/backend/dbstatic/db2.php');
	$filename = " registration_email.php";  // Space added in front for alignment and spacing purposes

// Get Event Name from Database  ---------------------------------------------------------------------------------------------------------------------
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
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for EVENT NAME SELECT with ".$event_id."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for EVENT NAME SELECT with ".$event_id."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for EVENT NAME SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/processing/_error.log");
	}  // End Select Event Name If

// Compose Registration E-mail  --------------------------------------------------------------------------------------------------------------------------
	$from = "muse@lindyandblues.com";
	$subject = $event_title.": Thanks for Registering!";
	$body = $fname." ".$lname.",<p>Thanks for registering for ".$event_title." as a ".$role."!</p><p>The total cost of your registration is $".$price." (plus checkout fee if applicable).  This email is to confirm that you are registered, but does <b>NOT</b> confirm whether you have paid.  You should receive a separate e-mail regarding payment confirmation (from Paypal or other sources).  If you are unsure whether you have paid, want to pay by check, or have other questions, send an email to <a href=\"mailto:".$contact_email."\">".$contact_email."</a>.</p>";

	$body .= "<p>To see your registration page, check out housing information, or to pay for your registration, please click <a href=\"http://www.lindyandblues.com/events/pay-for-event?registration_id=".$registration_id."\">here</a> or look for the 'Pay For Events' page underneath our 'Events' menu on our website and use Registration ID (".$registration_id.")!</p>";
    
	$body .= "We're looking forward to seeing you!<br /><br />The ".$event_title." Organizers";
	require_once($root.'/backend/services/email-function.php');
	$loginEmail = "phillylab@gmail.com";
	$email_password = "2030Sansom";
	$connection = connectEmail($loginEmail, $email_password);
	if($connection != null) { 
		$email_sent = sendEmail($connection, $from, $email, $subject, $body);
		$subject = "ORIGINALLY TO $email - " . $subject;
		$email_sent = sendEmail($connection, $from, "phillylab@gmail.com", $subject, $body);
		if(!$email_sent){
			echo "<br /><br />There was a problem sending your confirmation email! Send an email to <a href=\"mailto:lab@lindyandblues.com\">lab(at)lindyandblues(dot)com</a> and let us know!<br /><br />";
			$error_msg = $timestamp.$filename." E-mail did not send.\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
	} else {
		echo "<br /><br />We had a problem sending you a confirmation email! Send an email to <a href=\"mailto:lab@lindyandblues.com\">lab(at)lindyandblues(dot)com</a> and let us know!<br /><br />";
		$error_msg = $timestamp.$filename." Connection returned as null for sending e-mail.\n";
		error_log($error_msg, 3, $root."/backend/processing/_error.log");
    }
    
?>