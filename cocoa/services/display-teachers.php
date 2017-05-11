<?php
	/** 
	* File generated to handle Teacher Display for events
	* 
	* Date		Developer				Modification Made
	* --------- ----------------------- --------------------------------------------------------------
	* 01-02-15	Marc Longhenry			Original File copied from display-price.php to allow for displaying teachers
	*									dynamically, modified to use Teacher database, etc.
	* 05-19-15		"					Moved to Backend/Services, included VisibleWhen attribute to
	*									SQL Query, and modified error logging.
	* 06-09-15		"					Modified query to include time difference
	* 08-01-15		"					Added missing newline character
	*
	*/
	$timestamp = date("d-m-y H:i:s");
	$filename = " display-teachers.php";  // Space added in front for alignment and spacing purposes
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
		$event_name = $row['name'];
	} else {
		$error_msg = $timestamp.$filename.' Failed to retrieve Event Name for Event ID = '.$event_id.'\n';
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
	


	// Search for teachers with associated Event ID to be displayed.
	// ***** There is a conversion included to shift time between the MySQL System time (that we can't control) to local EST time) *****
	if($vip == 1) { 
		$teacherQuery = "SELECT * FROM teachers WHERE event_id = $event_id ORDER BY type, pairing, role, lname ASC";
	} else {
		$teacherQuery = "SELECT * FROM teachers WHERE event_id = $event_id AND visible_when <= CONVERT_TZ(NOW(),'+00:00','+03:00') ORDER BY type, pairing, role, lname ASC";
	}
	if ($result = $mysqli->query($teacherQuery)) {
		$fname;
		$lname;
		$city;
		$state;
		$country;
		$description;
		$image;
		$video;
		$type;
		$load = 0; // Variable to count return values as we load arrays
		while($row = $result->fetch_assoc()) {
			$fname[$load] = $row['fname'];
			$lname[$load] = $row['lname'];
			$location_flag[$load] = $row['location_flag'];
			$city[$load] = $row['city'];
			$state[$load] = $row['state'];
			$country[$load] = $row['country'];
			$description[$load] = $row['description'];
			$image[$load] = $row['image'];
			$video[$load] = $row['video'];
			$type[$load] = $row['type'];
			$load++;
		}
		if($load == 0){
			echo '<div id="top" class="section-title">'.$event_name.' Instructors</div>
			<p>We don&#39;t have any information on the instructors just as yet.  Keep an eye out on the Facebook Event, LaB&#39;s Facebook Page, or our Website Blog for information on when we&#39;ll be announcing them!</p>';
		} else if($load == 1) {
			echo '<div id="top" class="section-title">'.$event_name.' Instructor</div>';
		} else if($load > 1) {
			echo '<div id="top" class="section-title">'.$event_name.' Instructors</div>';
		}
		$result->close();
		if ($update_text) {
			echo '<p class="update-box event-info-instance">'.$update_text.'</p>';
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for EVENT ID SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
	
	// Place holder for teacher navigation
	
	
	// Display individual Teacher information
	$unload = 0;  // Variable to count Teachers as displayed
	$float = 0;  // Variable to change float sides
	$mt = 0;
	$sg = 0;
	$mi = 0;
	$floatclass;
	while($unload < $load) {
		// Music Type Bars
		if($type[$unload] === 'mainTeacher' && $mt === 0) {
			echo '<div class="title-bar-type">Main Teachers</div>';
			$mt = 1;
		}
		if($type[$unload] === 'specialGuest'&& $sg === 0) {
			echo '<div class="title-bar-type">Special Guest Teachers</div>';
			$sg = 1;
		}if($type[$unload] === 'museInstructor' && $mi === 0) {
			echo '<div class="title-bar-type">Muses</div>';
			$mi = 1;
		}
		
		// Instance Container
		echo '<div class="event-info-instance">';
		
		// Title Bar
		echo '<div id="'.$fname[$unload].$lname[$unload].'" class="title-bar-text">'.$fname[$unload].' '.$lname[$unload];
		if($location_flag[$unload] != 0) {
			echo '<br><div class="title-bar-location">';
			if($city[$unload] != null) {
				echo $city[$unload];
			}
			if($state[$unload] != null) {
				if($city[$unload] != null) { echo ', ';}
				echo $state[$unload];
			}
			if($country[$unload] != null) {
				if($state[$unload] != null || ($state[$unload] === null && $city[$unload] != null)) { echo ', ';}
				echo $country[$unload];
			}
			echo '</div>';
		}
		echo '</div>';
		
		// Teacher Bio/Image
		if($float == 0) {
			$float = 1;
			$floatclass = 'align="right"';
		} else {
			$float = 0;
			$floatclass = 'align="left"';
		}
		if($image[$unload]) {
			echo '<img class="lab-photo" src="'.$image[$unload].'" alt="'.$fname[$unload].' '.$lname[$unload].'" '.$floatclass.'>';
		}
		echo $description[$unload];
		
		// Teacher Video
		if($video[$unload]) {
			echo '<br><br><a href="'.$video[$unload].'">Here is a video of '.$fname[$unload].' dancing!</a>';
		}
		
		// Back To Top, also closes Music-Teacher-Instance
		echo '<br><br><a href="#top">back to top</a></div>';		
		$unload++;
	}
?>