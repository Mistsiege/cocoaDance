<?php
	// Lindy and Blues Get Purchase Info Script
	// Version 1.0.1
	// Date Created:  12-27-2013 by Marc Longhenry
	//
	// This file is designed to work with the Create/Edit Event page to take an Price ID Number from a dropdown
	// box, SELECT price and description to put into hidden fields to be submitted to the Google Wallet system
	// 
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-27-13 Marc Longhenry  		File Created, modified to use the database tables and files for Price Info retrieval

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
	$sql="SELECT price, title, description FROM prices WHERE price_id = '".$priceNum."'";
	$result = mysqli_query($mysqli,$sql);

	// Generate Price Item Purchase Information in hidden inputs in submission form
	while($row = mysqli_fetch_array($result)) {
		echo '<input type="hidden" name="price" value="'.$row['price'].'">';
		echo '<input type="hidden" name="title" value="'.$row['title'].'">';
		echo '<input type="hidden" name="description" value="'.$row['description'].'">';
	}

	mysqli_close($mysqli);
?>