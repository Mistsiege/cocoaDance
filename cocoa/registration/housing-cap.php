<?php
	// Lindy and Blues Housing Cap Script
	// Version 1.0.1
	// Date Created:  01-27-2014 by Marc Longhenry
	// 
	// The purpose of this script is to populate housing information on the Registration Posting Form for Weekend Events.
	// It assumes that Event ID has been set in the Wordpress post and that this file and db.php are in the same folder.
	// It produces a stated registration cap and if the cap has been surpassed in the system then it displays an error
	// message letting people know that anything after the first amount listed as a housing cap may not get housing.
	//
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 01-27-14 Marc Longhenry  		File Created, modified to use the database tables and files for Housing Information
	// 02-16-14	Marc Longhenry			Modified the file to include a more accurate error logging system, properly display
	//									up-to-date housing information, and display a 'full' message if needed.
	// 12-03-15 	-					Updated Error Messages for the correct directory from $root
	
	// Include Database Information and begin Database Connection
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/backend/dbstatic/db2.php');
	$filename = " housing-cap.php";  // Space added in front for alignment and spacing purposes
										// Must be after database file for proper error logging

	// Retrieve Housing Information for stated Event_ID  ---------------------------------------------------------------------------------------------------
	if ($stmt = $mysqli->prepare("SELECT events.event_id, events.housing, events.housing_end, COUNT(housing.housing_option) AS current_reg FROM events LEFT JOIN housing ON events.event_id = housing.event_id WHERE events.event_id = ? AND housing.housing_option = 'need'")) {
		if($stmt->bind_param("s", $event_id)) {
			if($stmt->execute()) {
				/* bind result variables */
				if($stmt->bind_result($temp, $housing_cap, $housing_end, $current_housing)) {
					/* fetch value */
					$stmt->fetch();
					$stmt->close();
					if ($stmt = $mysqli->prepare("SELECT events.event_id, COUNT(housing.guaranteed) AS current_guaranteed FROM events LEFT JOIN housing ON events.event_id = housing.event_id WHERE events.event_id = ? AND housing.guaranteed = 'yes' AND housing.housing_option = 'need'")) {
						if($stmt->bind_param("s", $event_id)) {
							if($stmt->execute()) {
								/* bind result variables */
								if($stmt->bind_result($temp, $current_guaranteed)) {
									/* fetch value */
									$stmt->fetch();
									$stmt->close();
									if($temp == $event_id) {
										echo '<div id="registration-housing-cap">Our current Housing Cap for this event is: <b>'.$housing_cap.'</b>.';
										echo '<br />Our current Housing Registration is: <b>'.$current_housing.'</b>.';
										echo '<br />Our current Guaranteed Housing is: <b>'.$current_guaranteed.'</b>.';
										echo '<br />We will do our best to house everyone who registers to be housed, and usually end up housing more than our cap, but only guarantee up to the Housing Cap.  <b>Housing IS guaranteed for Full Weekend Pass Holders once you have registered (requesting housing) and have paid (up to our housing cap), housing is NOT guaranteed for any other pass level.  Housing will close on '.date('F jS, Y - g:i A', strtotime($housing_end)).'</b>.';
										
										if($current_guaranteed >= $housing_cap) {
											echo '<div id="registration-housing-full"><b>Housing Registration is FULL</b>:<br />We have reached our Housing Cap for this event.  We will still allow for housing registration, but only the first '.$housing_cap.' are guaranteed to have housing available.  We will do our best to find housing for everyone who needs it, but please understand we may not be able to and please make other arrangements if you are able.</div>  <!-- End Registration Housing Full -->';
										}
										echo '</div> <!-- End Registration Housing Cap -->';
									} else {
										$error_msg = $timestamp.$filename." Returned Event does not match Given Event with ".$event_id." - ".$temp."\n";
										error_log($error_msg, 3, $root."/registration/registration/error.log");
									}
								} else {
									$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry for HOUSING GUARANTEED SELECT with ".$event_id."\n";
									error_log($error_msg, 3, $root."/registration/registration/error.log");
								}
							} else {
								$error_msg = $timestamp.$filename." Did not complete EXECUTE entry for HOUSING GUARANTEED SELECT with ".$event_id."\n";
								error_log($error_msg, 3, $root."/registration/registration/error.log");
							}
						} else {
							$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry for HOUSING GUARANTEED SELECT with ".$event_id."\n";
							error_log($error_msg, 3, $root."/registration/registration/error.log");
						}
					} else {
						$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry for HOUSING GUARANTEED SELECT with ".$event_id."\n";
						error_log($error_msg, 3, $root."/registration/registration/error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry for HOUSING INFORMATION SELECT with ".$event_id."\n";
					error_log($error_msg, 3, $root."/registration/registration/error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry for HOUSING INFORMATION SELECT with ".$event_id."\n";
				error_log($error_msg, 3, $root."/registration/registration/error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry for HOUSING INFORMATION SELECT with ".$event_id."\n";
			error_log($error_msg, 3, $root."/registration/registration/error.log");
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry for HOUSING INFORMATION SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/registration/registration/error.log");
	}

	mysqli_close($mysqli);
?>