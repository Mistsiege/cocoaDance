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
	$filename = " display-prices.php";  // Space added in front for alignment and spacing purposes
										// Must be after database file for proper error logging
	
	// Grab Event ID to be used
	if($_POST['event_id']) {
		$event_id = $_POST['event_id'];
	} elseif ($_GET['event_id']) {
		$event_id = $_GET['event_id'];
	}
	
	// Search for prices with associated Event ID to be displayed.
	if ($result = $mysqli->query("SELECT prices.price, prices.price_id, prices.title, prices.description, prices.follow_num, prices.lead_num, prices.general_num, prices.reg_start, prices.reg_end, COUNT(registration.price_id) AS current_reg FROM prices LEFT JOIN registration ON prices.price_id = registration.price_id WHERE prices.event_id = $event_id AND (prices.display_only = 'yes' OR prices.display_only = 'no') GROUP BY prices.price_id")) {
		echo '<div class="event-info-instance"><table id="event-pricing-table"><th title="Label for Pricing Item">Title</th><th title="Price for item">Price</th><th title="Registration available for everyone">Available</th><th title="Registration is open on this date/time">Opens</th><th title="Registration closes on this date/time">Closes</th>';
		while($row = $result->fetch_assoc()) {
			if(
				($row['current_reg'] >= $row['general_num'] && $row['general_num'] != 0) ||
				($row['current_reg'] >= $row['follow_num'] && $row['follow_num'] != 0 && $row['lead_num'] == 0) ||
				($row['current_reg'] >= $row['lead_num'] && $row['lead_num'] != 0 && $row['follow_num'] == 0) ||
				(strtotime('now') > strtotime($row['reg_end']))
			) {
				echo '<tr class="unavailable-price">';
			} else {
				echo '<tr>';
			}
			echo '<td><b>'.$row['title'].'</b></td><td>$'.$row['price'].'</td><td>';
			if($row['general_num'] == 0 and $row['follow_num'] == 0 and $row['lead_num'] == 0) {
			   echo 'Unlimited';
			}
			if($row['general_num']) {
				if(($row['general_num']-$row['current_reg']) <= 0) {
					$calc = 0;
				} else {
					$calc = ($row['general_num']-$row['current_reg']);
				}
				echo '('.$calc.' General Passes)';
			}
			if($row['follow_num']) {
			   echo '('.$row['follow_num'].' Follow Passes)';
			}
			if($row['lead_num']) {
			   echo '('.$row['lead_num'].' Lead Passes)';
			}
			echo '</td><td>'.date('F jS, Y - g:i A', strtotime($row['reg_start'])).'</td><td>'.date('F jS, Y - g:i A', strtotime($row['reg_end'])).'</td></tr>';
		}
		echo '</table></div>';
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for EVENT ID SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/backend/services/_error.log");
	}
?>