<?php
	// Determine Unique "Real Date" for schedule day buttons
	$read_date_output = array();
	if ($real_date_query = $mysqli->query("SELECT DISTINCT real_date FROM schedule WHERE event_id = $event_id ORDER BY real_date ASC")) {
		while($row = mysqli_fetch_assoc($real_date_query)) {
            $real_date_output[] = $row['real_date'];
		}
		$real_date_query->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for EVENT ID SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}	
	
	// Print Event Day Navigation
	echo '<div id="event-day-nav" class="flex-box-1 event-info-instance">';
	echo '<center><b>Click a button to switch days of our schedule, Click a link for a drop down with more detail on a section!</b></center><br/>';
	$real_date_count = count($real_date_output);
	for($day_counter = 0; $day_counter < $real_date_count; $day_counter++) {
		$day_button = date("l", strtotime($real_date_output[$day_counter]));
		echo '<div class="event-page-submenu"><a class="ss-no-ajax brown-button" href="#" value="'.$day_button.'" ng-click="setScheduleDay(\''.$day_button.'\')" target="_top">'.$day_button.'</a></div>';
	}
    echo '</div>';

?>