<?php
	/**
	* Download Registration Script
	*
	* The purpose of this script is to collect all information related to the download type (Registration, Housing, etc.), execute queries to retrieve the
	* information from all the related databases, and put it into a CSV to be downloaded to the client.
	*/

	// Establish Error Logging Details  ----------------------------------------------------------------------------------------------------------------------
		$root = $_SERVER['DOCUMENT_ROOT'];
		include($root.'/cocoa/settings/db.php');
		$filename = " download-registration.php";  // Space added in front for alignment and spacing purposes

	// POSTed information to be used
		$event_title = $_POST['event_title'];
		$event_id = $_POST['event_id'];
		$download_type = $_POST['download_type'];
		
	// Determine information to be downloaded based on Download Type
		switch ($download_type) {
			case 'housing':
				if($result = $mysqli->query("SELECT housing.housing_option, housing.guaranteed, housing.capacity, housing.driving, housing.latenight, housing.dogs, housing.cats, housing.smoking, housing.comments, users.fname, users.lname, users.email, users.phone, users.city, users.state, users.country, registration.role, registration.payment_id, payments.status, prices.title FROM housing, users, registration, prices, payments WHERE housing.event_id = $event_id AND housing.user_id = users.user_id AND registration.price_id = prices.price_id And housing.housing_id = registration.housing_id AND registration.payment_id = payments.payment_id AND(housing.housing_option = 'need' OR housing.housing_option = 'offer') ORDER BY housing.housing_option ASC, housing.guaranteed DESC, payments.status DESC")) {
					break;
				} else {
					$error_msg = $timestamp.$filename." Housing Information CSV Download failed.".$mysqli->error."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
					break;			
				}
			case 'registration':
				if($result = $mysqli->query("SELECT registration.user_id, users.fname, users.lname, users.email, users.role, registration.price_id, prices.price, prices.title, registration.payment_id, payments.method, payments.amount, payments.status, registration.code_id, codes.price AS code_price FROM registration, users, prices, payments, codes WHERE registration.user_id = users.user_id AND registration.price_id = prices.price_id AND registration.payment_id = payments.payment_id AND registration.event_id = '".$event_id."' AND registration.code_id = codes.code_id ORDER BY registration.code_id, registration.price_id ASC")) {
					break;
				} else {
					$error_msg = $timestamp.$filename." Registration Information CSV Download failed.".$mysqli->error."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
					break;			
				}
			default:
				$error_msg = $timestamp.$filename." Download Type (".$download_type.") is invalid.\n";
				error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				break;
		}
		
	// File Formatting
		$date = date("Y-m-d_Hms");
		$num_fields = $result->field_count; 
		$headers = array();
		$field_names = $result->fetch_fields();
		$fn = 0;
		foreach ($field_names as $val) {
			$headers[$fn] = $val->name;
			$fn++;
		}
		$fp = fopen('php://output', 'w'); 
		if ($fp && $result) {     
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="'.$event_title.'-Registration-'.$date.'.csv"'); 
			header('Pragma: no-cache');    
			header('Expires: 0');
			fputcsv($fp, $headers); 
			while ($row = $result->fetch_assoc()) {
				fputcsv($fp, array_values($row)); 
			}
			die; 
		} 
    
	$result->close();
	$mysqli->close();
?>
