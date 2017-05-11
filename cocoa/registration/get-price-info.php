<?php
	// Lindy and Blues Get Price Info Script
	// Version 1.0.1
	// Date Created:  12-26-2013 by Marc Longhenry
	//
	// This file is designed to work with the Create/Edit Event page to take an Price ID Number from a dropdown
	// box, SELECT all information associated with it in the 'prices' database table, and create a form with
	// the information about that event already populated.
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-26-13 Marc Longhenry  		File Created, modified to use the database tables and files for Price Info retrieval

	// Receive Price ID from Javascript submission and GET
	$priceNum = intval($_GET['q']);

	// Include database information and create connection
	include("db.php");
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
	// Connect to database table, generate SQL query based on Price ID
	mysqli_select_db($mysqli,"events");
	$sql="SELECT * FROM prices WHERE price_id = '".$priceNum."'";
	$result = mysqli_query($mysqli,$sql);

	// Generate Edit Price form pre-populated with existing Price information
	while($row = mysqli_fetch_array($result)) {
		echo '<label for="description">Description:</label><input type="textarea" name="description" maxlength="160" value="'.$row['description'].'">
			<p>Expanded area for further description of item.  Ex1:  Virtual Pass to Blues Muse 2014 at the Tier 1 Price.  Includes access to all dances and Workshops.  Ex2:  Women\'s Medium-Size Blue T-Shirt with LaB\'s 2013 Shirt Design.</p>';
		echo '<label for="price">Price:</label><input type="text" name="price" value="'.$row['price'].'">';
		echo '<label for="general_num">General Participation:</label><input type="text" name="general_num" value="'.$row['general_num'].'">';
		echo '<label for="follow_num">Follower Participation:</label><input type="text" name="follow_num" value="'.$row['follow_num'].'">';				
		echo '<label for="lead_num">Lead Participation:</label><input type="text" name="lead_num" value="'.$row['lead_num'].'">
			<p><b><u><i>FOR PARTICIPATION PLEASE READ</i></u></b> - This determines who, and how many, people this is available to.  If anyone can buy it, but you only have 40 available (such as 40 Tier 1 passes) then ONLY enter 40 in the General Participation.  If you have specifics (such as 25 Follow participants for Blues Muse\'s Jill Competition) then you can enter 25 in the Follow Participation and leave the rest blank.  You may also use 25 leads and 25 follows (Such as a lead/follow Jack and Jill Competition) to allow for 50 total, but only 25 of each.  If all fields are set to 0, it will count as unlimited.</p>';
		echo '<label for="reg_start">Registration Start:</label><input type="datetime" name="reg_start" value="'.$row['reg_start'].'">';
		echo '<label for="reg_end">Registration End:</label><input type="datetime" name="reg_end" value="'.$row['reg_end'].'">
            <p>For Registration Start/End - This is when the pricing will be available.  If you plan to use numbers, i.e. 25 follows, just use today\'s date through the start of the event or when you would like registration to end.  The system will cut-off registration when numbers are hit, and not allow further registrants beyond that.  If you plan to use time frames, i.e. unlimited registration, then set the dates you would like the pricing to be available.</p>';
	}

	mysqli_close($mysqli);
?>