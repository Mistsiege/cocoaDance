<?php
	/*
	* Housing Management Script
	*
	* The purpose of this script is to display all information for an event in regards to housing.  It will also allow for the information to be downloaded
	* in CSV or other possible formats.
	*/
	
	// If Event ID is sent with a URL, POST, or otherwise generated, produce payment information and interface  ----------------------------------------------	
	if($_GET['event_id'] or $_POST['event_id'] or $event_id) { // If an Event ID is sent.. Assign Event ID
		if($_GET['event_id']) {
			$event_id = $_GET['event_id'];
		}
		if($_POST['event_id']) {
			$event_id = $_POST['event_id'];
		}
		
		// Establish Error Logging Details  ----------------------------------------------------------------------------------------------------------------------
		$root = $_SERVER['DOCUMENT_ROOT'];
		include($root.'/cocoa/settings/db.php');
		$filename = " admin-housing.php";  // Space added in front for alignment and spacing purposes
		
		// Display Event Information  ------------------------------------------------------------------------------------------------------------------------
		if ($stmt = $mysqli->prepare("SELECT name FROM events WHERE event_id = ?")) {
			if($stmt->bind_param("s", $event_id)) {
				if($stmt->execute()) {
					/* bind result variables */
					if($stmt->bind_result($event_name)) {
						/* fetch value */
						$stmt->fetch();
						$stmt->close();
						echo "<div class='title02' id='download-registration'>Housing Registration for ".$event_name."</div>";
					} else {
						$error_msg = $timestamp.$filename." Failed to BIND RESULT for EVENT NAME SELECT with ".$event_id."\n";
						error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Failed to EXECUTE for EVENT NAME SELECT with ".$event_id."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Failed to BIND PARAMETER for EVENT NAME SELECT with ".$event_id."\n";
				error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Failed to PREPARE STATEMENT for EVENT NAME SELECT with ".$event_id."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		}
		
		
		// Select Housing Information based on Event ID  ------------------------------------------------------------------------------------------------
		$records = 0;
		$total_need = 0;
		$total_offering = 0;
		$total_difference = 0;
		$fname = array();
		$lname = array();
		$email = array();
		$phone = array();
		$city  = array();
		$state = array();
		$country = array();
		$role = array();
		$status = array();
		$housing_option = array();
		$capacity = array();
		$driving = array();
		$latenight = array();
		$dogs = array();
		$cats = array();
		$smoking = array();
		$comments = array();
		
		if ($result = $mysqli->query("SELECT housing.housing_option, housing.capacity, housing.driving, housing.car_space, housing.latenight, housing.dogs, housing.cats, housing.smoking, housing.comments, users.fname, users.lname, users.email, users.phone, users.city, users.state, users.country, registration.role, registration.payment_id, payments.status FROM housing, users, registration, payments WHERE housing.event_id = $event_id AND housing.user_id = users.user_id AND housing.housing_id = registration.housing_id AND registration.payment_id = payments.payment_id AND(housing.housing_option = 'need' OR housing.housing_option = 'offer') ORDER BY housing.housing_option ASC, payments.status DESC")) {
			while($row = $result->fetch_assoc()) {
				$records++;
				// Store all the data to be displayed in a table later
				$fname[$records] = $row['fname'];
				$lname[$records] = $row['lname'];
				$email[$records] = $row['email'];
				$phone[$records] = $row['phone'];
				$city[$records]  = $row['city'];
				$state[$records] = $row['state'];
				$country[$records] = $row['country'];
				$role[$records] = $row['role'];
				$status[$records] = $row['status'];
				$housing_option[$records] = $row['housing_option'];
				$capacity[$records] = $row['capacity'];
				$driving[$records] = $row['driving'];
				$car_space[$records] = $row['car_space'];
				$latenight[$records] = $row['latenight'];
				$dogs[$records] = $row['dogs'];
				$cats[$records] = $row['cats'];
				$smoking[$records] = $row['smoking'];
				$comments[$records] = $row['comments'];
				// Perform counts
				if($row['housing_option']=='need') {
					$total_need++;
				} elseif($row['housing_option']=='offer') {
					$total_offering = $total_offering + $row['capacity'];
				} else {
					$error_msg = $timestamp.$filename." Housing Option does not fit Need/Offering. -- ".$row['housing_option']."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
			}  /* fetch value */
			$result->close();
		} else {
			$error_msg = $timestamp.$filename." Failed to QUERY for HOUSING INFORMATION SELECT using EVENT ID ".$event_id.".  Error is ".$mysqli->error."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		}
	// Generate Display for Totals Information  --------------------------------------------------------------------------------------------------------------	
		$total_difference = $total_offering - $total_need;
		echo "<div id='event-housing-totals'>
				<table>
					<tr>
						<th>Housing Option</th>
						<th>Total</th>
					</tr>
					<tr>
						<td>Need</td>
						<td>".$total_need."</td>
					</tr>
					<tr>
						<td>Offering</td>
						<td>".$total_offering."</td>
					</tr>
					<tr>
						<td>Difference</td>
						";
						if($total_difference > 0) {
							echo "<td class='event-housing-good'>";
						} elseif($total_difference < 0) {
							echo "<td class='event-housing-bad'>";
						} else {
							echo "<td>";
						}
						echo $total_difference."</td>
					</tr>
					<tr>
						<td>Total Entries</td>
						<td>".$records."</td>
					</tr>
				</table>
			</div> <!-- Event Housing -->
			<div id='event-housing-download-link'>
				<a href='#event-download'>Jump to Download Housing Information</a>
			</div> <!-- Event Housing Download Link -->";
			
	// Generate Display for Registrant Information  ----------------------------------------------------------------------------------------------------------
		$display = 1;
		echo "<div id='event-housing-list'>
				<table>
					<tr>
						<th title='Housing Option Selected'>H.O.</th>
						<th title='Payment Status, Comped and Paid registrants should take priority'>Status</th>
						<th title='Need should always have a capacity of 1 unless stated in comments.  Offering Capacity will be the amount of people they can reasonably house.'>Cap</th>
						<th title='Will this person have a car available for the weekend.'>DR</th>						
						<th title='With Car and existing passengers, This person has this may spaces available'>CS</th>
						<th title='Will this person try to stay for the Late Night Dances.'>LN</th>
						<th title='Will someone Needing Housing BE OKAY with dogs?  Or someone Offering Housing HAVE Dogs?'>DG</th>
						<th title='Will someone Needing Housing BE OKAY with cats?  Or someone Offering Housing HAVE cats?'>CT</th>
						<th title='Will someone Needing Housing BE OKAY with smoking?  Or someone Offering Housing BE A smoker?  Anyone who IS A SMOKER will be marked as SK'>SK</th>
						<th title='If there is something in this column, mouse-over it and view the Comment.'>Comment</th>					
						<th>Name</th>
						<th>Location</th>
						<th>E-Mail</th>
						<th>Role</th>
					</tr>";
		while($display <= $records) {
			echo "<tr>
					<td>".$housing_option[$display]."</td>
					<td>".$status[$display]."</td>
					<td>".$capacity[$display]."</td>";
			if($driving[$display] == 'yescar') {
				echo "<td class='event-housing-good'>Y</td>";
			} else {
				echo "<td class='event-housing-bad'>N</td>";
			}
			echo "<td>".$car_space[$display]."</td>";
			if($latenight[$display] == 'yes') {
				echo "<td class='event-housing-good'>Y</td>";
			} else {
				echo "<td class='event-housing-bad'>N</td>";
			}
			if($dogs[$display] == 'likedogs') {
				echo "<td class='event-housing-good'>Y</td>";
			} else {
				echo "<td class='event-housing-bad'>N</td>";
			}
			if($cats[$display] == 'likecats') {
				echo "<td class='event-housing-good'>Y</td>";
			} else {
				echo "<td class='event-housing-bad'>N</td>";
			}
			if($smoking[$display] == 'dontmind') {
				echo "<td class='event-housing-good'>Y</td>";
			} elseif ($smoking[$display] == 'mindsmoking') {
				echo "<td class='event-housing-bad'>N</td>";
			} elseif ($smoking[$display] == 'smoke') {
				echo "<td>SK</td>";
			}
			if($comments[$display] != null) {
				echo "<td class='event-housing-good' title='".$comments[$display]."'>Comment</td>";
			} else {
				echo "<td></td>";
			}
			echo 	"<td>".$fname[$display]." ".$lname[$display]."</td>
					<td>".$city[$display].", ".$state[$display]." ".$country[$display]."</td>
					<td>".$email[$display]."</td>
					<td>".$role[$display]."</td>
				</tr>";
			$display++;
		}	
		echo "</table>
			</div> <!-- Event Registration List -->
			
			<div id='event-download'>
				<div class='title02' id='download-registration'>Download Registration</div>
				This section will allow you to download the housing information given above for a the event you are viewing into a CSV file which can be opened directly by Excel.<br /> 
				<form action='/cocoa/admin/download-registration.php' method='post'>
					<label for='event_title'>Event Title:</label> <input type='text' name='event_title'><br />
					Please enter the Event Title as you would a file name.  I.E. - LaBlove2013 wil download as LaBLove2013 Registration-todaysdate.csv.
					<input type='hidden' name='event_id' value='".$event_id."'>
					<input type='hidden' name='download_type' value='housing'>
					<br /><input type='submit' id='submit'>
				</form>
			</div> <!-- Event Download -->
			";
			
			
	// If Event ID is NOT sent with a URL, POST, or otherwise generated, produce payment information and interface  ------------------------------------------	
	} else {
		echo '<div id="switch-event">	
				<form>
					<label for="event_id">Payment Information for Event:</label><select name="event_id">';
					include("cocoa/admin/event-dropdown-all.php");
		echo '		</select>
					<input type="submit" value="Grab Event Housing Information">
				</form>
			</div> <!-- Event ID Request -->';
	}		
?>