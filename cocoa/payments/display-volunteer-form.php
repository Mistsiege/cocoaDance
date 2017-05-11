<?php
	/** 
	* File generated to handle Price Display for events
	* 
	* Date		Developer				Modification Made
	* --------- ----------------------- --------------------------------------------------------------
	* 01-02-14	Marc Longhenry			Original File made to support tabular display of available weekend pricing
	* 04-10-14		"					Removed Description field, unnecessary and cluttered table
	* 04-24-14		"					Modified query to take 'yes' or 'no' for display_only, allowding 'hid' for hidden prices
	* 07-01-14		"					Modified query to use Event ID instead of a hardcoded value.
	* 03-26-15		"					Moved to Backend folder and updated error file locations
	* 06-06-15		"					Modified to use external DB/SQL connection
	* 
	*/
	
	// Include Database Information and begin Database Connection
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/backend/dbstatic/db2.php');
	$filename = " display-volunteer-form.php";  // Space added in front for alignment and spacing purposes
										// Must be after database file for proper error logging
	
	// Grab registration ID to be used
	if($_POST['registration_id']) {
		$registration_id = $_POST['registration_id'];
	} elseif ($_GET['registration_id']) {
		$registration_id = $_GET['registration_id'];
	}
	
	echo $registration_id;
	// Search for prices with associated Event ID to be displayed.
	if ($result = $mysqli->prepare('SELECT * FROM volunteers, registration WHERE registration.registration_id = ? AND registration.volunteer_id = volunteers.volunteer_id')) {
		if($stmt->bind_param('s', $registration_id)) {
			if($stmt->execute()) {
				/* bind result variables */
				if($stmt->bind_result($volunteer_id, $event_id, $used_id, $hours, $arrival, $departure, $vehicle, $comments)) {
					/* fetch value */
					$stmt->fetch();
					$stmt->close();
					echo "<form method='post' action='../../backend/processing/process-volunteer-info.php'>
							<label>How many hours of volunteering would you like?:</label><input type='text' name='hours' value='$hours' required><br />
							<label>When will you arrive for the weekend?:</label><input type='text' name='arrival' value='$arrival' required><br />
							<label>When will you leave for the weekend?:</label><input type='text' name='departure' value='$departure' required><br />
							<label>Will you have a car over the weekend?</label>
							<select name='vehicle' required>
								<option value=''>Please Choose One</option>
								<option value='yes' ";
								if($vehicle == 'yes') { echo 'selected'; }
					echo							">Yes, I will have my own car</option>
								<option value='no' ";								
								if($vehicle == 'no') { echo 'selected'; }
					echo							"No, I will be relying on carpooling/public transport</option>
							</select>
							<label>Any additional information about volunteering?:</label><input type='text' name='comments' value='$comments'><br /><br />
							<input class='event-submit-button brown-button' type='submit' value='Update'>
						</form><br /><br />";
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry for REGISTRATION SELECT with ".$registration_id."\n";
					error_log($error_msg, 3, $root."/backend/payments/_error.log");
				echo "no results result".$registration_id;
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry for REGISTRATION SELECT with ".$registration_id."\n";
				error_log($error_msg, 3, $root."/backend/payments/_error.log");

				echo "no results execute".$registration_id;
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry for REGISTRATION SELECT with ".$registration_id."\n";
			error_log($error_msg, 3, $root."/backend/payments/_error.log");
				echo "no results parameter".$registration_id;
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE entry for REGISTRATION SELECT with ".$registration_id."\n";
		error_log($error_msg, 3, $root."/backend/payments/_error.log");
				echo "no results prepare".$registration_id;
				printf("Error Message: %s\n", $mysqli->error);
	}

			echo "<form method='post' action='../../backend/processing/process-volunteer-info.php'>
				<label>How many hours of volunteering would you like?:</label><input type='text' name='hours' placeholder='example - 2' required><br />
				<label>When will you arrive for the weekend?:</label><input type='text' name='arrival' placeholder='example - Friday Night at 10pm' required><br />
				<label>When will you leave for the weekend?:</label><input type='text' name='departure' placeholder='example - NEVER!!!' required><br />
				<label>Will you have a car over the weekend?</label>
				<select name='vehicle' required>
					<option value=''>Please Choose One</option>
					<option value='yes'>Yes, I will have my own car</option>
					<option value='no'>No, I will be relying on carpooling/public transport</option>
				</select>
				<label>Any additional information about volunteering?:</label><input type='text' name='comments' placeholder='I like Pina Coladas, and getting caught in the rain'><br /><br />
				<input class='event-submit-button brown-button' type='submit' value='Submit'>
			</form><br /><br />";
?>