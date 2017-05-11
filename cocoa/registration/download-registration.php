<?php
// Lindy and Blues Registration Download Script
// Version 1.0.1
// Date Created:  02-12-2014 by Marc Longhenry
// 
// The purpose of this script is to collect all information related to the download type (Registration, Housing, etc.), execute queries to retrieve the
// information from all the related databases, and put it into a CSV to be downloaded to the client.
//
// Date		Developer  				Modified
// -------- ----------------------  ----------------------------------------------------------------------------------------------------------------------
// 02-12-14 Marc Longhenry  		File Created, modified to use the database tables to retrieve registration information and put it into a file.
// 12-03-15 	-					Updated Error Messages for the correct directory from $root

// Establish Error Logging Details  ----------------------------------------------------------------------------------------------------------------------
$timestamp = date("d-m-y H:i:s");
$filename = " download-registration.php";  // Space added in front for alignment and spacing purposes	
$root = $_SERVER['DOCUMENT_ROOT'];

// POSTed information to be used
	$event_title = $_POST['event_title'];
	$event_id = $_POST['event_id'];
	$download_type = $_POST['download_type'];
	
// Database Information and Connection
	ini_set("include_path", "/home/phillylab/lindyandblues.com/blog/scripts/event-system:".ini_get("include_path"));
	include("db.php");
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error."\n";
		error_log($error_msg, 3, $root."/registration/registration/error.log");
	}
	
// Determine information to be downloaded based on Download Type
	switch ($download_type) {
		case 'housing':
			if($result = $mysqli->query("SELECT housing.housing_option, housing.capacity, housing.driving, housing.latenight, housing.dogs, housing.cats, housing.smoking, housing.comments, users.fname, users.lname, users.email, users.phone, users.city, users.state, users.country, registration.role, registration.payment_id, payments.status, prices.title, housing.guaranteed FROM housing, users, registration, prices, payments WHERE housing.event_id = $event_id AND housing.user_id = users.user_id AND registration.price_id = prices.price_id And housing.housing_id = registration.housing_id AND registration.payment_id = payments.payment_id AND(housing.housing_option = 'need' OR housing.housing_option = 'offer') ORDER BY housing.housing_option ASC, payments.status DESC")) {
				break;
			} else {
				$error_msg = $timestamp.$filename." Housing Information CSV Download failed.".$mysqli->error."\n";
				error_log($error_msg, 3, $root."/registration/registration/error.log");
				break;			
			}
		case 'registration':
			if($result = $mysqli->query("SELECT registration.user_id, users.fname, users.lname, users.email, users.role, registration.price_id, prices.price, prices.title, registration.payment_id, payments.method, payments.amount, payments.status, registration.code_id, codes.price AS code_price FROM registration, users, prices, payments, codes WHERE registration.user_id = users.user_id AND registration.price_id = prices.price_id AND registration.payment_id = payments.payment_id AND registration.event_id = '".$event_id."' AND registration.code_id = codes.code_id ORDER BY registration.code_id, registration.price_id ASC")) {
				break;
			} else {
				$error_msg = $timestamp.$filename." Registration Information CSV Download failed.".$mysqli->error."\n";
				error_log($error_msg, 3, $root."/registration/registration/error.log");
				break;			
			}
		default:
			$error_msg = $timestamp.$filename." Download Type (".$download_type.") is invalid.\n";
			error_log($error_msg, 3, $root."/registration/registration/error.log");
			break;
	}
	
	if($result) {
		$num = $result->num_rows;
	} else {
		$error_msg = $timestamp.$filename." Error with Results\n";
		error_log($error_msg, 3, $root."/registration/registration/error.log");
	}
	
	for ($i = 0; $i < $count; $i++){ 
		$header .= mysql_field_name($result, $i)."\t"; 
	} 
	
	$header_done = false;
	while($row = $result->fetch_assoc()) {
	/*  $line = "";
	  $delim = "";
	  foreach (array_keys($row) as $key) {
		if(!$header_done) {
		  $registration .= $delim.$key;
		}
		$value = $row[$key];
		$value = str_replace("\r\n", "<br />", $value);
		$line .= $delim.$value;
		$delim = ",";
	  }
	  if(!$header_done) {
		$registration .= "\n";
		$header_done = true;
	  }
	  $registration .= $line."\n"; */
		$line = ''; 
		foreach($row as $value){ 
			if(!isset($value) || $value == ""){ 
				$value = "\t"; 
			} else { 
	# important to escape any quotes to preserve them in the data. 
				$value = str_replace('"', '""', $value); 
	# needed to encapsulate data in quotes because some data might be multi line. 
	# the good news is that numbers remain numbers in Excel even though quoted. 
				$value = '"' . $value . '"' . "\t"; 
			} 
			$line .= $value; 
		} 
		$registration .= trim($line)."\n"; 
	}
	
	$date = date("Y-m-d_Hms");
	//header('Content-Type: application/vnd.ms-excel');
	header("Content-type: application/octet-stream"); 
	//header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
	header('Content-Disposition: attachment; filename="'.$event_title.'-Registration-'.$date.'.xls"'); 
	//header('Content-Length: '.strlen($registration));
	header("Pragma: no-cache"); 
	header("Expires: 0"); 
	
	echo $header."\n".$registration; 
?>
