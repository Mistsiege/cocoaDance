<?php
	/** 
	* File generated to handle Music Display for events
	* 
	* Date		Developer				Modification Made
	* --------- ----------------------- --------------------------------------------------------------
	* 01-02-15	Marc Longhenry			Original File copied from display-teachers.php to allow for displaying bands and DJs
	*									dynamically, modified to use Teacher database, etc.
	* 07-28-15		"					Modified to use Visible_When functionality to set when music is visible
	*/
	$timestamp = date("d-m-y H:i:s");
	$filename = " display-music.php";  // Space added in front for alignment and spacing purposes
	$root = $_SERVER['DOCUMENT_ROOT'];
	
	// Determine VIP Status  -----------------------------------------------------
	if($_GET['vip']) { $vip = $_GET['vip']; }
	if($_POST['vip']) { $vip = $_POST['vip']; }

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
		echo '<div id="top" class="section-title">'.$row['name'].' Music!</div>';		
		if ($update_text) {
			echo '<p class="update-box event-info-instance">'.$update_text.'</p>';
		}
	} else {
		$error_msg = $timestamp.$filename.' Failed to retrieve Event Name for Event ID = '.$event_id;
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
	
	// Search for teachers with associated Event ID to be displayed.
	if($vip == 1) { 
		$musicQuery = "SELECT * FROM music WHERE event_id = $event_id AND active = 1 ORDER BY type ASC, name ASC";
	} else {
		$musicQuery = "SELECT * FROM music WHERE event_id = $event_id AND active = 1 AND visible_when <= CONVERT_TZ(NOW(),'+00:00','+03:00') ORDER BY type ASC, name ASC";
	}
	if ($result = $mysqli->query($musicQuery)) {
		$name;
		$type;
		$city;
		$state;
		$country;
		$bio;
		$type;
		$image;
		$video;
		$load = 0; // Variable to count return values as we load arrays
		while($row = $result->fetch_assoc()) {
			$name[$load] = $row['name'];
			$location_flag[$load] = $row['location_flag'];
			$city[$load] = $row['city'];
			$state[$load] = $row['state'];
			$country[$load] = $row['country'];
			$bio[$load] = $row['bio'];
			$type[$load] = $row['type'];
			$image[$load] = $row['image'];
			$video[$load] = $row['video'];			
			$load++;
		}
		if($load == 0){
			echo '<p>We don&#39;t have any information on our musical items just as yet.  Keep an eye out on the Facebook Event, LaB&#39;s Facebook Page, or our Website Blog for information on when we&#39;ll be announcing them!</p>';
		}
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for EVENT ID SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
	
	// Place holder for Musician navigation
	
	
	// Move 
	
	
	// Display individual Musician information
	$unload = 0;  // Variable to count Musicians as displayed
	$float = 0;  // Variable to change float sides
	$band = 0;
	$headliner = 0;
	$dj = 0;
	$floatclass;
	while($unload < $load) {		
		// Music Type Bars
		if($band == 0) {
			if($type[$unload] == '1band') {
				echo '<div class="title-bar-type">Bands</div>';
				$band = 1;
			}
		}
		if($headliner == 0) {
			if($type[$unload] == '2headliner') {
				echo '<div class="title-bar-type">Headliners</div>';
				$headliner = 1;
			}
		}if($dj == 0) {
			if($type[$unload] == '3dj') {
				echo '<div class="title-bar-type">DJs</div>';
				$dj = 1;
			}
		}
		
		// Instance Container
		echo '<div class="event-info-instance">';
		
		// Title Bar
		echo '<div id="'.$name[$unload].'" class="title-bar-text">'.$name[$unload];
		if($location_flag[$unload] != 0) {
			echo '<br><div class="title-bar-location">'.$city[$unload].', '.$state[$unload];
			if($country) {
				echo ', '.$country[$unload];
			}
			echo '</div>';
		}
		echo '</div>';
		
		// Musician Bio/Image
		if($float == 0) {
			$float = 1;
			$floatclass = 'align="right"';
		} else {
			$float = 0;
			$floatclass = 'align="left"';
		}
		if($image[$unload]) {
		echo '<p><img class="lab-photo" src="'.$image[$unload].'" alt="'.$name[$unload].'" '.$floatclass.'>';
		}
		echo $bio[$unload].'</p>';
		
		// Musician Video
		if($video[$unload]) {
			echo '<br><a href="'.$video[$unload].'">Here is a video of '.$name[$unload].' playing music!</a><br>';
		}
		
		// Back To Top, also closes Music-Teacher-Instance
		echo '<br /><a href="#top">back to top</a></div>';		
		$unload++;
	}
?>