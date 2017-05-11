<?php
	/** 
	* File generated to handle Venue Display for events
	* 
	* Date		Developer				Modification Made
	* --------- ----------------------- --------------------------------------------------------------
	* 04-05-15	Marc Longhenry			Original File copied from display-teacher.php to allow for displaying venues
	*									dynamically, modified to use Venue database, etc.
	* 08-01-15		"					Modified error logging to use Services directory, added newline characters
	*
	*/
	$timestamp = date("d-m-y H:i:s");
	$filename = " display-venues.php";  // Space added in front for alignment and spacing purposes
	$root = $_SERVER['DOCUMENT_ROOT'];
	
	// Search Users using e-mail (assumed to be unique) go retrieve User_id for transaction processing.
	include($root.'/backend/dbstatic/db.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
	
	if($_POST['event_id']) {
		$event_id = $_POST['event_id'];
	} elseif ($_GET['event_id']) {
		$event_id = $_GET['event_id'];
	}
	
	// Generate top text
	if ($result = $mysqli->query("SELECT name FROM events WHERE event_id = $event_id")) {
		$row = $result->fetch_assoc();
		echo '<div id="top" class="section-title">'.$event_name.' Venues</div>';
		if ($update_text) {
			echo '<p class="update-box event-info-instance">'.$update_text.'</p>';
		}
	} else {
		$error_msg = $timestamp.$filename.' Failed to retrieve Event Name for Event ID = '.$event_id.' \n';
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
	
	// Search for teachers with associated Event ID to be displayed.
	if ($result = $mysqli->query("SELECT * FROM venues WHERE event_id = $event_id ORDER BY name ASC")) {
		$numRow = $result->num_rows;
		if($numRow > 0) {			
			while($row = $result->fetch_assoc()) {
				// Instance Container
				echo '<div class="event-info-instance">';
				
				echo '<div id="'.$row['abbreviation'].'" class="title-bar-text">'.$row['name'];
				if($row['address']) {
					echo '<br><div class="title-bar-location">'.$row['address'].'</div>';
				}
				echo '</div>';
				
				// Venue Image/Google Map
				echo '<div id="venue-map-image">';
				if($row['gmap']) {
					echo '<div id="venue-gmap">'.$row['gmap'].'</div>';
				}			
				if($row['image']) {
					echo '<img id="venue-image" class="lab-photo" src="'.$row['image'].'" alt="'.$row['name'].'">';
				}
				echo '</div>';
				
				// Venue Information
				if($row['notes']) {
					echo '<div class="title02">Notes</div><p>'.$row['notes'].'</p>';
				}
				if($row['parking']) {
					echo '<div class="title02">Parking</div><p>'.$row['parking'].'</p>';
				}
				if($row['housing']) {
					echo '<div class="title02">Housing</div><p>'.$row['housing'].'</p>';
				}
				
				// Back To Top
				echo '<br /><a href="#top">back to top</a></div>';	
			}
		} else {
			echo '<p>We don&#39;t have any information on our venue plans just as yet.  Keep an eye out on the Facebook Event, LaB&#39;s Facebook Page, or our Website Blog for information on when we&#39;ll be announcing them!</p>';
		}
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for EVENT ID SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}	
?>