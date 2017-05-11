<?php
	/** 
	* File generated to handle User Information and ensure a User_id is available for transaction processing.
	* This will create a new user in our system or retrieve user information based on e-mail address.
	* 
	* Requires
	* ---------------
	* db2.php
	*
	*/

	// Include Database Information  ----------------------------------------------------------------------------
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/backend/dbstatic/db2.php');
	$filename = " process_volunteer_info.php";  // Space added in front for alignment and spacing purposes

	// Receive POST data and assign to variables  ----------------------------------------------------------------------------------------------------------
	$code = $_POST['volunteer_radio'];
	$registration_id = $_POST['registration_id'];
	$user_id = $_POST['user_id'];
	$event_id = $_POST['event_id'];
    $message_box = '';
    $error_msg = $timestamp.$filename." [1] POST Data - comp_code: ".$code." | reg_id: ".$registration_id." | user_id: ".$user_id." | event_id: ".$event_id." \n";
    error_log($error_msg, 3, $root."/backend/processing/_transaction.log");


    // Find a valid discount code if available  -----------------------------------------------------------------------------------
    $valid_code = 0;
    if($code) {			
        if ($stmt = $mysqli->prepare("SELECT codes.code_id, codes.price, codes.price_id, codes.summary, codes.uses, COUNT(registration.code_id) FROM codes LEFT JOIN registration ON codes.code_id = registration.code_id WHERE code = ? AND expiration >= CURRENT_TIMESTAMP")) {
            if($stmt->bind_param("s", $code)) {
                if($stmt->execute()) {
                    if($stmt->bind_result($code_id, $code_price, $code_price_id, $summary, $uses, $code_count)) {
                        $stmt->fetch();
                        $stmt->close();

    $error_msg = $timestamp.$filename." [2] code_id: ".$code_id." | code_price: ".$code_price." | code_price_id: ".$code_price_id." | summary: ".$summary." | uses: ".$uses." | code_count: ".$code_count." \n";
    error_log($error_msg, 3, $root."/backend/processing/_transaction.log");

    // Assign Code Price if applicable  -------------------------------------------------------------------------------------------
                        if($code_count < $uses or $uses == 0) {
                            $price = 0;
                            $price = $code_price;
                            $valid_code = $code_id;
                            if($code_price_id != 0) { // If a price_id is set up in the payment code, change the price id so the registration does not count towards limits
                                $price_id = $code_price_id;
                            }
    // If Code is Valid and Price is 0.00 i.e. Free then insert Payment and retrieve ID for registration entry  --------------------
                            if($price == 0 && $valid_code != null && $valid_code != 0) {
                                $comp = 'Comp';
                                $item = $summary.' Comp for given event ('.$event_id.').';
                                if($shirt_style != 'none' && $shirt_style != null){
                                    $status = 'Not Yet Paid';
                                } else {
                                    $status = 'Comped-'.$summary;
                                }

    $error_msg = $timestamp.$filename." [3] price: ".$price." | valid_code: ".$valid_code." | item: ".$item." | status: ".$status." \n";
    error_log($error_msg, 3, $root."/backend/processing/_transaction.log");

    // Insert Payment for Comp  ----------------------------------------------------------------------------------------------------
                                if ($stmt = $mysqli->prepare("INSERT INTO payments (user_id, event_id, method, checkout_id, amount, item, status) VALUES (?, ?, ?, ?, ?, ?, ?)")) {
                                    if($stmt->bind_param("sssssss", $user_id, $event_id, $comp, $status, $price, $item, $status)) {
                                        if($stmt->execute()) {
                                            $stmt->close();
                                        } else {
                                            $error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for CODE PAYMENT with code-".$code." ui".$user_id." - ei".$event_id." - co".$comp." - pr".$price." - it".$item." - st".$status."\n";
                                            error_log($error_msg, 3, $root."/backend/processing/_error.log");
                                        }
                                    } else {
                                        $error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for CODE PAYMENT with code-".$code." ui".$user_id." - ei".$event_id." - co".$comp." - pr".$price." - it".$item." - st".$status."\n";
                                        error_log($error_msg, 3, $root."/backend/processing/_error.log");
                                    }
                                } else {
                                    $error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for CODE PAYMENT with code-".$code." ui".$user_id." - ei".$event_id." - co".$comp." - pr".$price." - it".$item." - st".$status."\n";
                                    error_log($error_msg, 3, $root."/backend/processing/_error.log");
                                }
                            } // End if price is free!
                        } // End if code is valid by expiration and uses.
    // Retrieve Payment_ID for Comp to put in with Registration  ---------------------------------------------------------------------
                        if ($stmt = $mysqli->prepare("SELECT payment_id FROM payments WHERE user_id = ? AND event_id = ? AND item = ?")) {
                            if($stmt->bind_param("sss", $user_id, $event_id, $item)) {
                                if($stmt->execute()) {
                                    /* bind result variables */
                                    if($stmt->bind_result($payment_id)) {
                                        /* fetch value */
                                        $stmt->fetch();
                                        $stmt->close();
    // Assign Price based on code  --------------------------------------------------------------------------------------------------
                                        if($payment_id == null) { 
                                            $payment_id = 0; 
                                            $error_msg = $timestamp.$filename." Did not RETRIEVE PAYMENT ID with error ".$mysqli->error.".  Likely associated with a code error: code-".$code." ui".$user_id." - ei".$event_id." - co".$comp." - pr".$price." - it".$item." - st".$status."\n";
                                            error_log($error_msg, 3, $root."/backend/processing/_error.log");
                                        } else {
                                            if($stmt = $mysqli->prepare("UPDATE registration SET payment_id = ?, price_id = ?, code_id = ? WHERE registration_id =?")) {
                                                if($stmt->bind_param("ssss", $payment_id, $price_id, $code_id, $registration_id)) {
                                                    if($stmt->execute()) {
                                                        $udpate_msg = "Valid Code applied to registration";
                                                        $error_msg = $timestamp.$filename." [4] Valid Code applied to registration \n";
                                                        error_log($error_msg, 3, $root."/backend/processing/_transaction.log");
                                                        
								                        // Update Housing Guaranteed
								                        include("housing-guaranteed-processing.php");
                                                    } else {
                                                        $error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for PAYMENT ID UPDATE with PID:".$payment_id." - RID:".$registration_id."\n";
                                                        error_log($error_msg, 3, $root."/backend/processing/_error.log");
                                                    }
                                                } else {
                                                    $error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for PAYMENT ID UPDATE with PID:".$payment_id." - RID:".$registration_id."\n";
                                                    error_log($error_msg, 3, $root."/backend/processing/_error.log");		
                                                }
                                            } else {
                                                $error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for PAYMENT ID UPDATE with PID:".$payment_id." - RID:".$registration_id."\n";
                                                error_log($error_msg, 3, $root."/backend/processing/_error.log");
                                            } // End Payment ID select
                                        }
                                        if($valid_code == 0) { // No valid code entered
                                            $price = $default_price;
                                        } // Else price already determined by Code selection
                                    } else {
                                        $error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for PAYMENT ID SELECT with ".$user_id." - ".$event_id." - ".$status."\n";
                                        error_log($error_msg, 3, $root."/backend/processing/_error.log");
                                    }
                                } else {
                                    $error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for PAYMENT ID SELECT with ".$user_id." - ".$event_id." - ".$status."\n";
                                    error_log($error_msg, 3, $root."/backend/processing/_error.log");
                                }
                            } else {
                                $error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for PAYMENT ID SELECT with ".$user_id." - ".$event_id." - ".$status."\n";
                                error_log($error_msg, 3, $root."/backend/processing/_error.log");		
                            }
                        } else {
                            $error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for PAYMENT ID SELECT with ".$user_id." - ".$event_id." - ".$status."\n";
                            error_log($error_msg, 3, $root."/backend/processing/_error.log");
                        } // End Payment ID select
                    } else {
                        $error_msg = "Comp code entered was invalid.";
                        $error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for CODE ID SELECT with ".$code."\n";
                        error_log($error_msg, 3, $root."/backend/processing/_error.log");
                    }
                } else {
                    $error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for CODE ID SELECT with ".$code."\n";
                    error_log($error_msg, 3, $root."/backend/processing/_error.log");
                }
            } else {
                $error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for CODE ID SELECT with ".$code."\n";
                error_log($error_msg, 3, $root."/backend/processing/_error.log");
            }
        } else {
            $error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for CODE ID SELECT with ".$code."\n";
            error_log($error_msg, 3, $root."/backend/processing/_error.log");
        }
    } else { // End If Code is filled
        $error_msg = "No comp code was provided.";
    }

    
    // Assign Price based on code  --------------------------------------------------------------------------------------------------

    // Post URL & information
    $url = "http://www.lindyandblues.com/events/pay-for-events?registration_id=".$registration_id;
    $fields = array(
        $error_msg => $error_box,
    	$update_msg => $message_box,
    );

    // build the urlencoded data
    $postvars = http_build_query($fields);

    // open connection
    $ch = curl_init();

    // set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

    // execute post
    $result = curl_exec($ch);

    // close connection
    curl_close($ch);
?>