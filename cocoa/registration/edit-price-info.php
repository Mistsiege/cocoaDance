<?php
	// Lindy and Blues Event Submission script
	// Version 1.0.1
	// Date Created:  12-05-2013 by Marc Longhenry
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-05-13 Marc Longhenry  		File Created, modified to use the database tables and files for Event Submission
	// 12-07-13	"						Modified to assign POST data to variables and use MySQLI statments with error messages
	
	// Get Database Connection Info and Set database table
	include('db.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
	// Receive POST data and assign to variables.
	$name = $_POST['name'];
	$s_time = $_POST['s_time'];
	$e_time = $_POST['e_time'];
	$teachers = $_POST['teachers'];
	$music = $_POST['music'];
	$location = $_POST['location'];
	$description = $_POST['description'];
	$housing = $_POST['housing'];
	$contact_name = $_POST['contact_name'];
	$contact_email = $_POST['contact_email'];

	// Create a New Event with given information.
	// Create a prepared statement.  Avoids SQL injection.
	if ($stmt = $mysqli->prepare("INSERT INTO events (name, s_time, e_time, teachers, music, location, description, housing, contact_name, contact_email)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
		if (!$stmt->bind_param("ssssssssss", $name, $s_time, $e_time, $teachers, $music, $location, $description, $housing, $contact_name, $contact_email)) {
   		echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!$stmt->execute()) {
    		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
   		}
		$stmt->close();
	} else {
		echo "Error, unable to create mysqli statement";	
	}
	
	// Redirect Submiter to Create/Edit page
	header("Location: /create-edit-event");

?>