<?php
	// Lindy and Blues Who Is Coming Script
	// Version 1.0.1
	// Date Created:  12-26-2013 by Marc Longhenry
	// 
	// The purpose of this script is to search the Registration table for active registrations with the Event ID
	// set in the page content where this script is used.  It then retrieves information associated with the
	// User ID for that registration and prints it in a tabular format.
	//
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 01-20-14 Marc Longhenry  		File Created, modified to use the database tables and files for retrieving Registration Information.
	// 04-02-14		"					Modified to not create the table/div unless results were returned.  Added working error reporting.
	// 04-16-14		"					Modified code to allow only leads to show up if only one lead has signed up.
	// 12-04-14		"					Modified code to use Registration.Role instead of the old Users.Role
	// 04-05-15		"					Modified code to include POST/GET for use with Angular
	// 07-02-15		"					Modified code to grab data, then process it separately instead of all at once.
	//									Also changed Database File and Query to grab follow information first.
	// 08-12-15		"					Added 'Refunded' and 'Canceled' to the query
	
	// Set Variables for Error Logging  ------------------------------------------------------------------------
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/backend/dbstatic/db2.php');
	$filename = " who-is-coming.php ";  // Space added in front for alignment and spacing purposes	

	// Prep display variables.
	if($_POST['event_id']) {
		$event_id = $_POST['event_id'];
	} elseif ($_GET['event_id']) {
		$event_id = $_GET['event_id'];
		
	}
	
	// Retrieve Registrants for Event  ------------------------------------------------------------------------------------------------------------------------
	$fname;
	$lname;
	$city;
	$state;
	$role;
	$share_name;
	$input = 0;
	if ($result = $mysqli->query("SELECT users.fname, users.lname, users.city, users.state, registration.role, registration.share_name FROM registration, users, payments WHERE registration.user_id = users.user_id AND registration.payment_id = payments.payment_id AND registration.event_id = $event_id AND payments.status != 'Refunded' AND payments.status != 'Cancelled' ORDER BY registration.role DESC")) {
		while($row = $result->fetch_assoc()) {
			$input++;
			$fname[$input] = $row['fname'];
			$lname[$input] = $row['lname'];
			$city[$input] = $row['city'];
			$state[$input] = $row['state'];
			$role[$input] = $row['role'];
			$share_name[$input] = $row['share_name'];
			//echo 'Found '.$fname[$input].' '.$lname[$input].' from '.$city[$input].', '.$state[$input].'. Share Name? '.$share_name[$input].'. Role? '.$role[$input].'.<br>';
		}
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry for WHO IS COMING SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/services/_error.log");		
	}	
	
	// Count the number of entries
	$count = count($fname);
	echo '<div id="whoiscoming" class="flex-box-1">';
	if($count > 0) {
		$output = 1;
		$hidden = 0;
		$lead_flag = 0;
		$follow_flag = 0;
		
		// Process List of Registrants
		while($output <= $input) {
			
			// Determine if a Follow is Registered and start their section
			if($follow_flag == 0) {
				// Prep the section for follows
				$follow_flag = 1;
				echo '<div id="whoiscoming-follows" class="labFb2ColItem"><h2>Follows</h2><hr><table>';
			} 
			
			// Determine if a Lead is registered, end Follow section, and start Lead section
			if($role[$output] == 'Lead' and $lead_flag == 0) {  // Once results transition to Leads, prep section for Lead results
				$lead_flag = 1;
				// Close Follow Section, Create Lead Section
				echo '</table>';
				if($hidden > 0) {  // If there are Follows who don't want their name displayed
					echo '.. And '.$hidden.' other ';
					if($hidden == 1) {
						echo 'Follow!';
					} else {
						echo 'Follows!';
					}
					$hidden = 0;
				}
				if($follow_flag == 0) {
					echo '<div id="whoiscoming-leads" class="labFb2ColItem"><h2>Leads</h2><hr><table>';
				} else {
					echo '</div> <!-- WhoIsComing Follows --><div id="whoiscoming-leads" class="labFb2ColItem"><h2>Leads</h2><hr><table>';
				}
			}
			
			// Process Registrant Information
			if($share_name[$output] == 'yes') {
				// Print Line Information for Registrant
				echo '<tr><td>'.$fname[$output].' '.$lname[$output].'</td><td>'.$city[$output].', '.$state[$output].'</td></tr>';
			} else {
				$hidden++;  // Add Hidden Registrant to total
			}
			$output++;
		}
		
		// Close Lead Section and WhoIsComing Section
		echo '</table>';
		if($hidden > 0) {  // If there are Leads who don't want their name displayed
			echo '.. And '.$hidden.' other ';
			if($hidden == 1) {
				echo 'Lead!';
			} else {
				echo 'Leads!';
			}
			$hidden = 0;
		}
		echo '</div> <!-- WhoIsComing Leads --> </div> <!-- WhoIsComing -->';
	} else {
		echo 'We do not have anyone registered for this event yet!</div> <!-- WhoIsComing -->';
	}
?>