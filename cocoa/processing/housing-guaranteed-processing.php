<?php
	// Lindy and Blues Housing Guaranteed Processing Script
	// 
	// The purpose of this script is to be called after payment processing has been confirmed by Paypal or Google Wallet.  After we have confirmed payment
	// this script will determine how many people have guaranteed housing versus how many the event promises.  If there are additional guaranteed housing
	// spaces available then this script will update the user's housing information to give them guaranteed housing.
	//
	// Date		Developer��				Modified
	// --------�----------------------� --------------------------------------------------------------------------
	// 11-11-14�Marc Longhenry��		File Created, modified to use the database tables and files for Housing Information
	// 07-11-15 	"					Changed the UPDATE query to only do housing guaranteed for those who selected 'need'

	// Include Database Information  ----------------------------------------------------------------------------
	$root = $_SERVER['DOCUMENT_ROOT'];	
	include($root.'/backend/dbstatic/db2.php');
	$filename = " housing-guaranteed-processing.php";  // Space added in front for alignment and spacing purposes

	// Retrieve Current Housing information for stated event  ---------------------------------------------------
	if ($stmt = $mysqli->prepare("SELECT events.housing, COUNT(housing.guaranteed) AS current_guaranteed FROM events LEFT JOIN housing ON events.event_id = housing.event_id WHERE events.event_id = ? AND housing.housing_option = 'need' AND housing.guaranteed = 'yes'")) {
		if($stmt->bind_param("s", $event_id)) {
			if($stmt->execute()) {
				// bind result variables
				if($stmt->bind_result($housing_cap, $current_guaranteed)) {
					// fetch value 
					$stmt->fetch();
					$stmt->close();
					$error_msg = $timestamp.$filename." Completed current housing check with Event: ".$event_id." | Cap: ".$housing_cap." | Current: ".$current_guaranteed." \n";
					error_log($error_msg, 3, $root."/backend/processing/_housing.log");
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry for HOUSING INFORMATION SELECT with ".$event_id."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry for HOUSING INFORMATION SELECT with ".$event_id."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry for HOUSING INFORMATION SELECT with ".$event_id."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry for HOUSING INFORMATION SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/processing/_error.log");
	}

	// If current guaranteed does not exceed housing cap, modify the payer's housing to be guaranteed
	if($current_guaranteed < $housing_cap) {
		
		// Grab Housing ID for this user
		if ($stmt = $mysqli->prepare("SELECT housing_id, housing_option FROM housing WHERE event_id = ? AND user_id = ?")) {
			if($stmt->bind_param("ss", $event_id, $user_id)) {
				if($stmt->execute()) {
					if($stmt->bind_result($housing_id, $housing_option)) {
						$stmt->fetch();
						$stmt->close();
						
						// Set update value based on housing option
						switch($housing_option) {
							case 'offer':
								$guaranteed = 'host';
								break;
							case 'need':
								$guaranteed = 'yes';
								break;
							case 'neither':
								$guaranteed = 'na';
								break;
							default:
								$error_msg = $timestamp.$filename." invalid input for housing option - ".$housing_option."\n";
								error_log($error_msg, 3, $root."/backend/processing/_error.log");
						}
						
						$error_msg = $timestamp.$filename." Completed housing request check with Event: ".$event_id." | User: ".$user_id." | H ID: ".$housing_id." | H Opt: ".$housing_option." | Guaranteed: ".$guaranteed." \n";
						error_log($error_msg, 3, $root."/backend/processing/_process.log");

						// Update Housing Guaranteed information
						if($stmt = $mysqli->prepare("UPDATE housing SET guaranteed = ? WHERE housing_id = ?")) {
							if($stmt->bind_param("ss", $guaranteed, $housing_id)) {
								$stmt->execute();
								$stmt->close();
							} else {
								$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry for HOUSING GUARANTEED UPDATE with Housing ID - ".$housing_id." \n";
								error_log($error_msg, 3, $root."/backend/processing/_error.log");
							}
						} else {
							$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry for HOUSING GUARANTEED UPDATE with Housing ID - ".$housing_id." \n";
							error_log($error_msg, 3, $root."/backend/processing/_error.log");
						}
					} else {
						$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry for HOUSING GUARANTEED SELECT with Housing ID - ".$housing_id." \n";
						error_log($error_msg, 3, $root."/backend/processing/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry for HOUSING GUARANTEED SELECT with Housing ID - ".$housing_id." \n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry for HOUSING GUARANTEED SELECT with Housing ID - ".$housing_id." \n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry for HOUSING GUARANTEED SELECT with Housing ID - ".$housing_id." \n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}	
	}
	
	// No need to close the connection, being used by Paypal-Processing.php parent file
?>