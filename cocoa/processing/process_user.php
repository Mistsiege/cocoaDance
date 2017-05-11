<?php
	/** 
	* File generated to handle User Information and ensure a User_id is available for transaction processing.
	* This will create a new user in our system or retrieve user information based on e-mail address.
	* 
	* Requires
	* ---------------
	* db2.php
	* registration-email.php
	*
	* Date		Developer				Modification Made
	* --------- ----------------------- --------------------------------------------------------------
	* 11-21-13	Marc Longhenry			Original Download Made.
	*									Edited file to include variable use instead of static information.
	* 11-22-13  	"          			Modified file to include MySQLi create and search functionality to create user and return User_id.
	* 01-02-14  	"           		Added fields to create Registration entry, also included some error tracking
	* 01-14-14  	"					Added Housing information and Registration Confirmation e-mails
	* 04-24-14		"					Added $user_id variable for comparision to create new user.  
	*									Modified Housing to check for an existing housing_id (for user and event) before creating a new one.
	*									Changed payment Code section to allow for a Price_ID to be set.
	* 08-01-15		"					Modified code section to check for a valid code first before processing
	*/

	// Include Database Information  ----------------------------------------------------------------------------
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/backend/dbstatic/db2.php');
	$filename = " process_user.php";  // Space added in front for alignment and spacing purposes
	
	// Receive POST data and assign to variables  ----------------------------------------------------------------------------------------------------------
	$user_id = 0; // Set User_ID to be compared (to create user), unless replaced by existing User_ID
	$fname = $_POST['fname'];
	$lname = $_POST['lname'];
	$email = $_POST['email'];
	$phone = $_POST['phone'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$country = $_POST['country'];
	$code = $_POST['code'];
	$role = $_POST['role'];
	$event_id = $_POST['event_id'];
	$price_id = $_POST['price_id'];
	$housing_option = $_POST['housing_option'];
	$capacity = 'na';
	if($_POST['capacity']) {
		$capacity = $_POST['capacity'];
	}
	if($_POST['capacity'] == null) {
		$capacity = 1;	
	}
	$driving = 'na';
	if($_POST['driving']) {
		$driving = $_POST['driving'];
	}
	$car_space = 'na';
	if($_POST['carspace']) {
		$car_space = $_POST['carspace'];
	}
	$latenight = 'na';
	if($_POST['latenight']) {
		$latenight = $_POST['latenight'];
	}
	$dogs = 'na';
	if($_POST['dogs']) {
		$dogs = $_POST['dogs'];
	}
	$cats = 'na';
	if($_POST['cats']) {
		$cats = $_POST['cats'];
	}
	$smoking = 'na';
	if($_POST['smoking']) {
		$smoking = $_POST['smoking'];
	}
	$share_name = $_POST['share_name'];
	if($share_name == null) {
		$share_name = 'na';
	}
	$comments = $_POST['comments'];
	$shirt_style = $_POST['shirt_style'];
	if($_POST['shirt_style'] == null) {
	   $shirt_style = 'none';
	}
	$shirt_size = $_POST['shirt_size'];
	 if($_POST['shirt_size'] == null) {
	   $shirt_size = 'none';
	}
	date_default_timezone_set('EST');
	$payment_id = 0;
	
	// Determine if User already exists in Database  -------------------------------------------------------------------------------------------------------
	if ($stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email = ?")) {
		if($stmt->bind_param("s", $email)) {
			if($stmt->execute()) {
				/* bind result variables */
				if($stmt->bind_result($user_id)) {
				/* fetch value */
				$stmt->fetch();
				$stmt->close();
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
		error_log($error_msg, 3, $root."/backend/processing/_error.log");
	}	

	// If User does not exist in Database, Create User -------------------------------------------------------------------------------
	if($user_id == 0) {  
		// Create a new user with given information.
		// Create a prepared statement.  Avoids SQL injection.
		if ($stmt = $mysqli->prepare("INSERT INTO users (fname, lname, phone, email, city, state, country)
		VALUES (?, ?, ?, ?, ?, ?, ?)")) {
			if($stmt->bind_param("sssssss", $fname, $lname, $phone, $email, $city, $state, $country)) {
				if($stmt->execute()) {
					$stmt->close();
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for USER CREATION with ".$fname."-".$lname."-".$email."-".$city."-".$state."-".$country."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			}  else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETERS entry with ".$mysqli->error." for USER CREATION with ".$fname."-".$lname."-".$email."-".$city."-".$state."-".$country."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for USER CREATION with ".$fname."-".$lname."-".$email."-".$city."-".$state."-".$country."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}

		// Search Users using e-mail (assumed to be unique) go retrieve User_id for transaction processing.
		if ($stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email = ?")) {
			if($stmt->bind_param("s", $email)) {
				if($stmt->execute()) {
					/* bind result variables */
					if($stmt->bind_result($user_id)) {
					/* fetch value */
					$stmt->fetch();
					$stmt->close();
					} else {
						$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
						error_log($error_msg, 3, $root."/backend/processing/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for USER ID SELECT with ".$email."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
	}  // End of Check for User ID

	// Retrieve Registration ID if it exists already for given User and Event  -----------------------------------------------------
	$registration_id = 0;
	if ($stmt = $mysqli->prepare("SELECT registration.registration_id FROM registration, prices WHERE registration.user_id = ? AND registration.event_id = ?")) {
		if($stmt->bind_param("ss", $user_id, $event_id)) {
			if($stmt->execute()) {
				/* bind result variables */
				if($stmt->bind_result($registration_id)) {
					/* fetch value */
					$stmt->fetch();
					$stmt->close();
				} else {
					$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for PRE-REGISTRATION ID SELECT with ".$user_id." - ".$event_id." - ".$price_id."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for PRE-REGISTRATION ID SELECT with ".$user_id." - ".$event_id." - ".$price_id."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for PRE-REGISTRATION ID SELECT with ".$user_id." - ".$event_id." - ".$price_id."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
	} else {
		$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for PRE-REGISTRATION ID SELECT with ".$user_id." - ".$event_id." - ".$price_id."\n";
		error_log($error_msg, 3, $root."/backend/processing/_error.log");
	}

	// Check if Registration ID Exists for User and Event  ------------------------------------------------------------------------
	if($registration_id == 0) {
		
		// Insert Housing Information  ----------------------------------------------------------------------------------------------------------------------------
		$housing_id = 0;
		if($housing_option) {
			// Check for existing Housing ID for User at given Event  -----------------------------------------------------------------------------------------
			if ($stmt = $mysqli->prepare("SELECT housing_id FROM housing WHERE user_id = ? AND event_id = ?")) {
				if($stmt->bind_param("ss", $user_id, $event_id)) {
					if($stmt->execute()) {
						/* bind result variables */
						if($stmt->bind_result($housing_id)) {
						/* fetch value */
						$stmt->fetch();
						$stmt->close();
						} else {						
							$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for HOUSING ID PRE-EXISTING with ".$user_id." - ".$event_id."\n";
							error_log($error_msg, 3, $root."/backend/processing/_error.log");
						}
					} else {					
						$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for HOUSING ID PRE-EXISTING with ".$user_id." - ".$event_id."\n";
						error_log($error_msg, 3, $root."/backend/processing/_error.log");
					}
				} else {				
					$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for HOUSING ID PRE-EXISTING with ".$user_id." - ".$event_id."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for HOUSING ID PRE-EXISTING with ".$user_id." - ".$event_id."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
			
			// If housing_id does not already exist for current user and event, create a housing entry and grab the housing_id  ------------------------------
			if($housing_id == 0) {
				$guaranteed = 'no';  // Housing is not guaranteed until registered and paid
				if ($stmt = $mysqli->prepare("INSERT INTO housing (event_id, user_id, housing_option, guaranteed, capacity, driving, car_space, latenight, dogs, cats, smoking, comments)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
					if($stmt->bind_param("ssssssssssss", $event_id, $user_id, $housing_option, $guaranteed, $capacity, $driving, $car_space, $latenight, $dogs, $cats, $smoking, $comments)) {
						if($stmt->execute()) {
							$stmt->close();
						} else {
							$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for INSERT HOUSING with ei.".$event_id." - ui.".$user_id." - ho.".$housing_option." - cap.".$capacity." - dr.".$driving." - cs.".$car_space." - ln.".$latenight." - dog.".$dogs." - cat.".$cats." - smk.".$smoking." - com.".$comments."\n";
							error_log($error_msg, 3, $root."/backend/processing/_error.log");
						}
					} else {
						$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for INSERT HOUSING with ei.".$event_id." - ui.".$user_id." - ho.".$housing_option." - cap.".$capacity." - dr.".$driving." - cs.".$car_space." - ln.".$latenight." - dog.".$dogs." - cat.".$cats." - smk.".$smoking." - com.".$comments."\n";
						error_log($error_msg, 3, $root."/backend/processing/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for INSERT HOUSING with ei.".$event_id." - ui.".$user_id." - ho.".$housing_option." - cap.".$capacity." - dr.".$driving." - cs.".$car_space." - ln.".$latenight." - dog.".$dogs." - cat.".$cats." - smk.".$smoking." - com.".$comments."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
				// Select Housing ID to insert with Registration  --------------------------------------------------------------------------------------------------------
				if ($stmt = $mysqli->prepare("SELECT housing_id FROM housing WHERE user_id = ? AND event_id = ?")) {
					if($stmt->bind_param("ss", $user_id, $event_id)) {
						if($stmt->execute()) {
							/* bind result variables */
							if($stmt->bind_result($housing_id)) {
							/* fetch value */
							$stmt->fetch();
							$stmt->close();
							} else {						
								$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for HOUSING ID SELECT with ".$user_id." - ".$event_id."\n";
								error_log($error_msg, 3, $root."/backend/processing/_error.log");
							}
						} else {					
							$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for HOUSING ID SELECT with ".$user_id." - ".$event_id."\n";
							error_log($error_msg, 3, $root."/backend/processing/_error.log");
						}
					} else {				
						$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for HOUSING ID SELECT with ".$user_id." - ".$event_id."\n";
						error_log($error_msg, 3, $root."/backend/processing/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for HOUSING ID SELECT with ".$user_id." - ".$event_id."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			}  // End Housing ID check
		}  // End Housing Option Check
		
		// Determine if Price selected by User is for housing only  -------------------------------------------------------------------
		if ($stmt = $mysqli->prepare("SELECT type, title FROM prices WHERE price_id = ?")) {
			if($stmt->bind_param("s", $price_id)) {
				if($stmt->execute()) {
					if($stmt->bind_result($price_type, $title)) {
						$stmt->fetch();
						$stmt->close();
						if($price_type == 'housing') {
							$method = 'Housing';
							$status = 'Offered Housing';
							$price = 0.00;
							if ($stmt = $mysqli->prepare("INSERT INTO payments (user_id, event_id, method, checkout_id, amount, item, status) VALUES (?, ?, ?, ?, ?, ?, ?)")) {
								if($stmt->bind_param("sssssss", $user_id, $event_id, $method, $status, $price, $title, $status)) {
									if($stmt->execute()) {
										$stmt->close();
			// Retrieve Payment_ID for Comp to put in with Registration  ---------------------------------------------------------------------
										if ($stmt = $mysqli->prepare("SELECT payment_id FROM payments WHERE user_id = ? AND event_id = ? AND item = ?")) {
											if($stmt->bind_param("sss", $user_id, $event_id, $title)) {
												if($stmt->execute()) {
													/* bind result variables */
													if($stmt->bind_result($payment_id)) {
														/* fetch value */
														$stmt->fetch();
														$stmt->close();
					// Assign Price based on code  --------------------------------------------------------------------------------------------------
														if($payment_id == null) { 
															$payment_id = 0; 
															$error_msg = $timestamp.$filename." Did not RETRIEVE PAYMENT ID with error ".$mysqli->error.". ui".$user_id." - ei".$event_id." - title".$title."\n";
															error_log($error_msg, 3, $root."/backend/processing/_error.log");
														}
													} else {
														$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for HOUSING PAYMENT ID SELECT with ".$user_id." - ".$event_id." - ".$status."\n";
														error_log($error_msg, 3, $root."/backend/processing/_error.log");
													}
												} else {
													$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for HOUSING PAYMENT ID SELECT with ".$user_id." - ".$event_id." - ".$status."\n";
													error_log($error_msg, 3, $root."/backend/processing/_error.log");
												}
											} else {
												$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for HOUSING PAYMENT ID SELECT with ".$user_id." - ".$event_id." - ".$status."\n";
												error_log($error_msg, 3, $root."/backend/processing/_error.log");		
											}
										} else {
											$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for HOUSING PAYMENT ID SELECT with ".$user_id." - ".$event_id." - ".$status."\n";
											error_log($error_msg, 3, $root."/backend/processing/_error.log");
										} // End Payment ID select
									} else {
										$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for HOUSING ONLY PAYMENT INSERT with ui".$user_id." - ei".$event_id." - meth".$method." - pr".$price." - it".$title." - st".$status."\n";
										error_log($error_msg, 3, $root."/backend/processing/_error.log");
									}
								} else {
									$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for HOUSING ONLY PAYMENT INSERT with ui".$user_id." - ei".$event_id." - meth".$method." - pr".$price." - it".$title." - st".$status."\n";
									error_log($error_msg, 3, $root."/backend/processing/_error.log");
								}
							} else {
								$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for HOUSING ONLY PAYMENT INSERT with ui".$user_id." - ei".$event_id." - meth".$method." - pr".$price." - it".$title." - st".$status."\n";
								error_log($error_msg, 3, $root."/backend/processing/_error.log");
							}
						} // End Price Type check
					} else {
						$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for PRICE ID TYPE SELECT with ".$price_id."\n";
						error_log($error_msg, 3, $root."/backend/processing/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for PRICE ID TYPE SELECT with ".$price_id."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for PRICE ID TYPE SELECT with ".$price_id."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for PRICE ID TYPE SELECT with ".$price_id."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
		
		// Find a valid discount code if available  -----------------------------------------------------------------------------------
		$valid_code = 0;
		if($code) {			
			if ($stmt = $mysqli->prepare("SELECT codes.code_id, codes.price, codes.price_id, codes.summary, codes.uses, COUNT(registration.code_id) FROM codes LEFT JOIN registration ON codes.code_id = registration.code_id WHERE code = ? AND expiration >= CURRENT_TIMESTAMP")) {
				if($stmt->bind_param("s", $code)) {
					if($stmt->execute()) {
						if($stmt->bind_result($code_id, $code_price, $code_price_id, $summary, $uses, $code_count)) {
						$stmt->fetch();
						$stmt->close();
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
		} // End If Code is filled
		
		// Stopgap Valid Code fix	
		if($valid_code <= 0 or $valid_code == null) {
			$valid_code = 0;
		}
		
		// Indicate in the log that a user is being processed
		$error_msg = $timestamp.$filename." File used to process a user".$user_id." \n";
		error_log($error_msg, 3, $root."/backend/processing/_transaction.log");
		
		// Use User ID, Event ID, and Price ID to create a Registration Entry  ---------------------------------------------------------------------------------
		if($price_id == 0 || $price_id == null) {
			$error_msg = $timestamp.$filename." before Registration Insertion, Price ID is not set ei".$event_id." - pri".$price_id." - ui".$user_id." - pai".$payment_id." - vc".$valid_code." - hi".$housing_id." - sn".$share_name."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
		if ($stmt = $mysqli->prepare("INSERT INTO registration (event_id, price_id, user_id, payment_id, code_id, housing_id, share_name, shirt_style, shirt_size, role)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
			if($stmt->bind_param("ssssssssss", $event_id, $price_id, $user_id, $payment_id, $valid_code, $housing_id, $share_name, $shirt_style, $shirt_size, $role)) {
				if($stmt->execute()) {
					$stmt->close();
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for REGISTRATION CREATION with ei".$event_id." - pri".$price_id." - ui".$user_id." - pai".$payment_id." - vc".$valid_code." - hi".$housing_id." - sn".$share_name."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for REGISTRATION CREATION with ei".$event_id." - pri".$price_id." - ui".$user_id." - pai".$payment_id." - vc".$valid_code." - hi".$housing_id." - sn".$share_name."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for REGISTRATION CREATION with ei".$event_id." - pri".$price_id." - ui".$user_id." - pai".$payment_id." - vc".$valid_code." - hi".$housing_id." - sn".$share_name."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
		
		// Retrieve Registration ID  -----------------------------------------------------------------------------------------------------------------------------
		if ($stmt = $mysqli->prepare("SELECT registration.registration_id, prices.price FROM registration, prices WHERE registration.user_id = ? AND registration.event_id = ? AND registration.price_id = ? and registration.price_id = prices.price_id")) {
			if($stmt->bind_param("sss", $user_id, $event_id, $price_id)) {
				if($stmt->execute()) {
					/* bind result variables */
					if($stmt->bind_result($registration_id, $default_price)) {
						/* fetch value */
						$stmt->fetch();
						$stmt->close();
		// Assign Price based on code  --------------------------------------------------------------------------------------------------
						if($valid_code == 0) { // No valid code entered
							$price = $default_price;
						} // Else price already determined by Code selection
		// Send Registration E-Mail  ----------------------------------------------------------------------------------------------------
						include('registration-email.php');
					} else {
						$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry with ".$mysqli->error." for REGISTRATION ID SELECT with ".$user_id." - ".$event_id." - ".$price_id."\n";
						error_log($error_msg, 3, $root."/backend/processing/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry with ".$mysqli->error." for REGISTRATION ID SELECT with ".$user_id." - ".$event_id." - ".$price_id."\n";
					error_log($error_msg, 3, $root."/backend/processing/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry with ".$mysqli->error." for REGISTRATION ID SELECT with ".$user_id." - ".$event_id." - ".$price_id."\n";
				error_log($error_msg, 3, $root."/backend/processing/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE STATEMENT entry with ".$mysqli->error." for REGISTRATION ID SELECT with ".$user_id." - ".$event_id." - ".$price_id."\n";
			error_log($error_msg, 3, $root."/backend/processing/_error.log");
		}
	} // End "Registration ID already exists in system for this event and user" Check  ----------------------------------------------
	
	/* close connection */
	$mysqli->close();
?>