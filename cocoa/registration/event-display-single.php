<?php
	$db_name = "labevents";
	$db_host = "mysql.lindyandblues.com";
	$db_user = "phillylab";
	$db_pwd = "1234qwer";
	 	// Connect to Events Database 
			mysql_connect($db_host, $db_user, $db_pwd) or die(mysql_error()); 
			mysql_select_db($db_name) or die(mysql_error()); 
		// Retrieve Events with Date From's after today
			$data = mysql_query("SELECT * FROM events WHERE s_time <= CURDATE( ) AND lab_event = 'yes' ORDER BY s_time ASC ") or die(mysql_error());
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