<?php
	// Lindy and Blues Event Event Display LaB script
	// Version 1.0.1
	// Date Created:  03-12-2014 by Marc Longhenry
	// 
	// The purpose of this script is to display the information inserted by the Create/Edit events process in our
	// events page so everyone can see what we have coming up.  This script relates specifically to the events
	// that LaB runs in our local scene.
	//
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 03-12-2014 Marc Longhenry  		File Created, modified to use the database tables and files for LaB Event Display

	// Get Database Connection Info and Set database table
		ini_set("include_path", "/home/phillylab/lindyandblues.com/blog/scripts/event-system:".ini_get("include_path"));
		include("db.php");

 	// Connect to Events Database 
		mysql_connect($hostname, $username, $password) or die(mysql_error()); 
		mysql_select_db($dbname) or die(mysql_error()); 
	// Retrieve Events with Date From's after today
		$data = mysql_query("SELECT * FROM events WHERE e_time >= CURDATE( ) AND lab_event = 'yes' AND event_type != 'TESTEVENT' ORDER BY s_time ASC ") or die(mysql_error());
	// Print data in a logical manner
		while($events = mysql_fetch_array( $data )) {
	// Recreate Date's as Textual versus Numeric
			$start_date = strtotime($events['s_time']);
			if($events['e_time'] != null) { $end_date = strtotime($events['e_time']); }
			echo '<div class="event-block"><span class="event-name">' . 
				$events['name'] . 
				'</span><span class="event-time">' . 
				date('F d, Y', $start_date);
 			if($events['e_time'] != null and $events['e_time'] != $events['s_time']) {
				echo ' to ' . date('F d, Y', $end_date);
 			}
			echo '</span>';
 			echo '<br /><span class="event-type">A ' . $events['event_type'] . ' in ' . $events['location'] . '</span>';
 			echo '<br /><br /><span class="event-desc">' . $events['description'] . '</span>';
 			echo '<br /><br /><span class="event-site"><a href="' . $events['website'] . '">Website</a></span>';
 			if($events['contact_name'] != null or $events['contact_email'] != null) {
 				echo '<span class="event-contact">Contact: ' . $events['contact_name'] . ' via ' . $events['contact_email'];
 			}
 			echo '</div><hr>';
		}
?>