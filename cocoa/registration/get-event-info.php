<?php
	// Lindy and Blues Event Get Event Info Script
	// Version 1.0.1
	// Date Created:  12-26-2013 by Marc Longhenry
	//
	// This file is designed to work with the Create/Edit Event page to take an Event ID Number from a dropdown
	// box, SELECT all information associated with it in the 'events' database table, and create a form with
	// the information about that event already populated.
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-26-13 Marc Longhenry  		File Created, modified to use the database tables and files for Event Info retrieval

	// Receive Event ID from Javascript submission and GET
	$eventNum = intval($_GET['q']);

	// Include database information and create connection
	include("db.php");
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
	// Connect to database table, generate SQL query based on Event ID
	mysqli_select_db($mysqli,"events");
	$sql="SELECT * FROM events WHERE event_id = '".$eventNum."'";
	$result = mysqli_query($mysqli,$sql);

	// Generate Edit form pre-populated with existing Event information
	while($row = mysqli_fetch_array($result)) {
		echo '<label for="s_time">Start Date/Time:</label><input type="datetime" name="s_time" value="'.$row['s_time'].'">';
		echo '<label for="e_time">End Date/Time:</label><input type="datetime" name="e_time" value="'.$row['e_time'].'">';
		echo '<label for="teachers">Teachers:</label><input type="text" name="teachers" maxlength="160" value="'.$row['teachers'].'">
			<p>This field is optional.  If you have more than one teacher, separate them by commas (,).  If it is a teaching pair, separate by dashes (-).  Larger weekend events will have a separate page for this and do not require it.</p>';
		echo '<label for="music">Music:</label><input type="text" name="music" maxlength="160" value="'.$row['music'].'">
			<p>This field is optional.  If you have DJs or Bands you can list them here.  Same separation as above.</p>';
		echo '<label for="location">Location:</label><input type="text" name="location" maxlength="160" value="'.$row['location'].'">
			<p>Please be as accurate as possible.  Example: 1906 Rittenhouse Square, Philadelphia, PA Second Floor.</p>';			
		echo '<label for="description">Description:</label><input type="textarea" name="description" maxlength="500" value="'.$row['description'].'">
			<p>Describe the event in as much detail as you can.  Example:  We are having a Crash Course the third weekend of March on Charleston!  We will be working on the basics steps and posture up through tips, tricks, and variations to make your dancing fun and fancy!  Come out and get your learn on!</p>';
		echo '<label for="housing">Housing:</label><select name="housing">';
		If($row['housing']) { '<option value="1">Yes</option><option value="0">No</option></select>"'; }
		else { echo '<option value="0">No</option><option value="1">Yes</option></select>"'; }
		echo '<p>Does this event offer housing?  Check for yes, uncheck for no.</p>';
		echo '<label for="contact_name">Contact Name:</label><input type="text" name="contact_name" maxlength="80" value="'.$row['contact_name'].'">';
		echo '<label for="contact_email">Contact E-mail:</label><input type="text" name="contact_email" maxlength="80" value="'.$row['contact_email'].'">';
	}

	mysqli_close($mysqli);
?>