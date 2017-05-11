<?php
	// Lindy and Blues Price Dropdown script
	// Version 1.0.1
	// Date Created:  12-26-2013 by Marc Longhenry
	// 
	// The purpose of this script is to populate the Edit Price field once Event has been selected
	//
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-26-13 Marc Longhenry  		File Created, modified to use the database tables and files for Price selection
	// 11-11-14		"					Modified from a drop down to radio buttons with a default checked option to
	//									alleviate issues with prices not being selected.
	
	// Receive Event ID from Javascript submission and GET
	$eventNum = intval($_GET['q']);
	if($event_id) {
		$eventNum = $event_id;
	}
	
	// Include Database Information and begin Database Connection
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/backend/dbstatic/db2.php');
	$filename = " price-dropdown.php";  // Space added in front for alignment and spacing purposes
										// Must be after database file for proper error logging
	
	// Connect to database table, generate SQL query based on Event ID
	mysqli_select_db($mysqli,"events");
	$sql="SELECT prices.price, prices.price_id, prices.title, prices.description, prices.follow_num, prices.lead_num, prices.general_num, prices.reg_start, COUNT(registration.price_id) AS current_reg FROM prices LEFT JOIN registration ON prices.price_id = registration.price_id WHERE prices.event_id = '".$eventNum."' AND prices.display_only = 'no' GROUP BY prices.price_id";
	$result = mysqli_query($mysqli,$sql);

	/* Commented out on 11/11/14

	// Generate Edit form pre-populated with existing Event information
	echo '<label for="price_id">Price:</label><select name="price_id"  onchange="fillEventForm(this.value, \'edit-price\'';
	if($purpose = $_GET['purpose']) { echo ', \''.$purpose.'\''; }
	echo ')" required><option value="">Please Select a Price</option>';
	$curtime = time();
	$rightnow = date('Y-m-d H:i:s', $curtime);
	while($row = mysqli_fetch_array($result)) {
		if($row['reg_start'] < $rightnow) {
			if($row['current_reg'] < $row['general_num'] or $row['general_num'] == 0) {
				echo '<option value="'.$row['price_id'].'">'.$row['title'].' - '.$row['price'].'</option>';
			}
		}
	}
	echo '</select>'; */
	
	// Generate Edit form pre-populated with existing Event information
	echo '<label for="price_id">Price:</label><br />';
	$curtime = time();
	$rightnow = date('Y-m-d H:i:s', $curtime);
	$price_checked = 0;
	while($row = mysqli_fetch_array($result)) {
		if($vip == 1) {
			if($row['current_reg'] < $row['general_num'] or $row['general_num'] == 0) {
				echo '<label class="event-price-label" for = "'.$row['title'].'"><input type="radio" name="price_id" id="price_radio" onclick="fillEventForm(this.value, \'edit-price\'';
					if($purpose = $_GET['purpose']) { echo ', \''.$purpose.'\''; }
				echo ')" value="'.$row['price_id'].'" ';
					if($price_checked == 0) { echo 'checked '; $price_checked = 1; }
				echo '/>'.$row['title'].' - $'.$row['price'].'</label><br />';
			}
		} else {
			if($row['reg_start'] < $rightnow) {
				if($row['current_reg'] < $row['general_num'] or $row['general_num'] == 0) {
					echo '<label class="event-price-label" for = "'.$row['title'].'"><input type="radio" name="price_id" id="price_radio" onclick="fillEventForm(this.value, \'edit-price\'';
						if($purpose = $_GET['purpose']) { echo ', \''.$purpose.'\''; }
					echo ')" value="'.$row['price_id'].'" ';
						if($price_checked == 0) { echo 'checked '; $price_checked = 1; }
					echo '/>'.$row['title'].' - $'.$row['price'].'</label><br />';
				}
			}
		}
	}
	
	mysqli_close($mysqli);
?>