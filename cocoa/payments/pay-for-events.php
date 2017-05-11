<?php
	// Lindy and Blues Pay For Events Script
	// Version 1.0.1
	// Date Created:  01-06-2014 by Marc Longhenry
	// 
	// The purpose of this script is to be a universal end-point for Event Registration transactions.  This script will be
	// embedded in the Pay For Events wordpress page.  If navigated to, it will ask for a Registration ID.  If redirected to
	// that page, a Registration ID will be sent along and a payment page or confirmation page will be displayed.
	//
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 01-06-14 Marc Longhenry  		File Created, modified to use the database tables and files for Registration information
	// 04-24-14		"					Modified payment options display to display based on price != 0 instead of payment status.
	//									Included case for Payment Method for Comp and proper display for it.
	// 08-10-14		"					Updated Error Logging
	// 08-01-15		"					Modified to display housing information only if it doesn't have housing_id = 0 (null or not used housing)

	// If POSTed from an Event Registration Page, all User Information (including fname) will be sent.  ------------------------------------------------------
	// Process User
	$root = $_SERVER['DOCUMENT_ROOT'];
	if($_POST['fname'] or $_GET['fname']) {
		include($root.'/backend/processing/process_user.php');
	}
	
	// Include Database Information  ----------------------------------------------------------------------------
	include($root.'/backend/dbstatic/db2.php');
	$filename = " pay-for-events.php";  // Space added in front for alignment and spacing purposes

	// Retrieve Message values as needed ------------------------------------------------------------------------
	$message_box = $_POST['message_box'];
	$error_box = $_POST['error_box'];
	
	// If Registration ID is sent with a URL, POST, or otherwise generated, produce payment information and interface  ---------------------------------------	
	if($_GET['registration_id'] or $_POST['registration_id'] or $registration_id) { // If a Registration ID is sent.. Assign Registration ID
		if($_GET['registration_id']) {
			$registration_id = $_GET['registration_id'];
		}
		if($_POST['registration_id']) {
			$registration_id = $_POST['registration_id'];
		}
		
		// Collect Registration Information from all associated databases
		if ($stmt = $mysqli->prepare("SELECT events.event_id, events.name, events.s_time, events.contact_name, events.contact_email, prices.price_id, prices.price, prices.title, prices.description, users.user_id, users.fname, users.lname, users.email, payments.payment_id, payments.status, payments.amount, payments.timestamp, payments.method, registration.shirt_style, registration.shirt_size, registration.housing_id, registration.role FROM registration, users, prices, events, payments WHERE registration_id = ? AND registration.user_id = users.user_id AND registration.price_id = prices.price_id AND registration.event_id = events.event_id AND registration.payment_id = payments.payment_id")) {
			if($stmt->bind_param("s", $registration_id)) {
				if($stmt->execute()) {
					/* bind result variables */
					if($stmt->bind_result($event_id, $event_name, $event_start, $contact_name, $contact_email, $price_id, $price, $title, $description, $user_id, $fname, $lname, $email, $payment_id, $payment_status, $payment_amount, $payment_timestamp, $payment_method, $shirt_style, $shirt_size, $housing_id, $role)) {
						/* fetch value */
						$stmt->fetch();
						$stmt->close();
						
//--------------------- Pre-output Processing
						if($shirt_style != null) {
							if($event_id === 15 || $event_id === 14) {
								$shirt_flag = 0;
							} elseif($shirt_style != 'none' && $shirt_size != 'none') {
								$price = $price + 15;
								$shirt_flag = 1;
							} else {
								$shirt_flag = 0;
							}
						}
						
						$event_begins = date("F jS, Y", strtotime($event_start));
						
					    $paypal_percent = ($price * 1.029 + .3) * .029;			// Calculate PP % fee as 2.9% of Price
						$paypal_price = $price + $paypal_percent + .3; 			// Price + PP % fee and $0.30 Transaction fee						
						$paypal_price = round($paypal_price, 2);				// Round to two digits
						
//--------------------- Create User Response Text 
						echo '<div id="registration-display">';
						echo '<p>Hello '.$fname.' '.$lname.',</p>';
						echo '<p>Thank you for registering for '.$event_name.' as a '.$role.' due to begin on '.$event_begins.'.  We hope to make it a fantastic experience for you!</p>';
						
//--------------------- Create Registration Section
						if($message_box || $error_box) {
							echo '<div class="event-info-instance" id="messages">';
							if($message_box) {
								echo '<div class="update-box">'.$message_box.'</div>';
							}			
							if($error_box) {
								echo '<div class="warning-box">'.$error_box.'</div>';
							}			
							echo '</div>';
						}

//--------------------- Create Registration Section
						echo '<div class="event-info-instance pay-for-events-component" id="payment">
									<div class="title-bar-text">Payment Information</div>
										<p>Your registration item is <b>'.$title.'</b> ';
						if($shirt_flag) {
							echo '(with a shirt: style = '.$shirt_style.', and size = '.$shirt_size.') ';
						}
						echo 'at <b>$'.$price.'</b>.  Your payment status is <b>'.$payment_status.'</b>.';
						if($payment_status == 'Not Yet Paid' || $paypal_price == 0.00) {
							echo '  If you have not paid yet, payment options will be available below.  If you have paid and are listed as Not Yet Paid, please contact lab(at)lindyandblues(dot)com with your Registration ID ('.$registration_id.').  <b>(If you came here right from Registration you can leave this page and pay later through the link in the confirmation e-mail sent to you.)</b></p>';

//--------------------- Being payment section					
							echo '<p><div class="payment-option-header"><label for="Paypal Checkout">Option 1 (<b>$'.$paypal_price.'</b>):  Paypal Checkout</label>
									<form action="https://secure.lindyandblues.com/paypal/paypal_ec_redirect.php" method="POST">
									  <input type="hidden" name="PAYMENTREQUEST_0_AMT" value="'.$paypal_price.'"></input>
									  <input type="hidden" name="PAYMENTREQUEST_0_ITEMAMT" value="'.$paypal_price.'"></input>
									  <input type="hidden" name="currencyCodeType" value="USD"></input>
									  <input type="hidden" name="paymentType" value="Sale"></input>
									  <input type="hidden" name="L_PAYMENTREQUEST_0_NAME0" value="'.$title.'">
									  <input type="hidden" name="L_PAYMENTREQUEST_0_NUMBER0" value="'.$registration_id.'"></input>
									  <input type="hidden" name="L_PAYMENTREQUEST_0_DESC0" value="'.$description.'"></input></td></tr>
									  <input type="image" id="paypal_submit" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_checkout_pp_142x27.png" alt="Check out with PayPal"></input>
									</form></div> <!-- Payment Option Header 1 -->';		
							echo '	Paypal charges us 2.9% + $.30 for processing the payments based on their stated fees <a href="https://www.paypal.com/webapps/mpp/merchant-fees">here</a>.  If the link does not work, or has different prices, please contact lab(at)lindyandblues(dot)com about it immediately.  Total price with fees is notated next to the Option label.</p>';
							echo '<hr><p><div class="payment-option-header">
											<label for="Pay with Check">Option 2 (<b>$'.$price.'</b>):  Pay by Check</label>
										 </div> <!-- Payment Option Header 2 -->
										 We are able to receive Checks by mail as payment for our events.  If you choose to pay by check, please bear in mind it will take longer to process this type of payment as it takes time to send it, receive it, deposit it, and update our system marking you as Paid.  Please e-mail the committee at '.$contact_email.' and we will let you know who to write the check out to and where to send it.  Please make sure to send the check at LEAST a week in advance to ensure you have paid before the event starts.</p>';
							echo '<hr><p><div class="payment-option-header">
											<label for="Comp Code">Option 3 (<b>Pricing Varies</b>):  Comp Code</label>
											<form action="../../backend/processing/process_comp_code.php" method="POST">
												<input type="hidden" name="registration_id" value="'.$registration_id.'"></input>
												<input type="hidden" name="user_id" value="'.$user_id.'"></input>
												<input type="hidden" name="event_id" value="'.$event_id.'"></input>
												<input type="text" name="comp_code" id="comp-code-box"></input>
												<input type="submit" value="Submit" id="comp-code-submit"></input>
											</form>
										 </div>	<!-- Payment Option Header 3 -->
										 If you\'re a winner of a competition, teacher, DJ, or other contributor to the event, we have sent you a code for the event!  Please enter it here.</p>';

						} else {
							echo '</p>';
							echo '<p>You paid <b>$'.$payment_amount.'</b> (May be different from amount above due to checkout fees) on '.date('F jS, Y - g:i A', strtotime($payment_timestamp)).' through ';
							switch ($payment_method) {
								case 'GW':
									echo 'Google Checkout.';
									break;
								case 'PP':
									echo 'Paypal.';
									break;
								case 'CH':
									echo 'Check mailed to us.';
									break;
								case 'Comp':
									echo 'Comped to Event.';
									break;
								case 'Housing':
									echo 'Offering Housing';
									break;
								default:
									echo $payment_method.'.';
									break;
							}
						}
						echo '</div> <!-- Payment Section -->';
						
//--------------------- Retrieve User Housing Information
						if($housing_id != 0 && $housing_id != null) {
							if ($stmt = $mysqli->prepare("SELECT housing_option, guaranteed, capacity, driving, latenight, dogs, cats, smoking, comments FROM housing WHERE housing_id = ?")) {
								if($stmt->bind_param("s", $housing_id)) {
									if($stmt->execute()) {
										if($stmt->bind_result($housing_option, $guaranteed, $capacity, $driving, $latenight, $dogs, $cats, $smoking, $comments)) {
											$stmt->fetch();
											$stmt->close();
										} else {
											$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry for REGISTRATION SELECT with ".$registration_id."\n";
											error_log($error_msg, 3, $root."/backend/payments/_error.log");
										}
									} else {
										$error_msg = $timestamp.$filename." Did not complete EXECUTE entry for REGISTRATION SELECT with ".$registration_id."\n";
										error_log($error_msg, 3, $root."/backend/payments/_error.log");
									}
								} else {
									$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry for REGISTRATION SELECT with ".$registration_id."\n";
									error_log($error_msg, 3, $root."/backend/payments/_error.log");
								}
							} else {
								$error_msg = $timestamp.$filename." Did not complete PREPARE entry for REGISTRATION SELECT with ".$registration_id."\n";
								error_log($error_msg, 3, $root."/backend/payments/_error.log");
							}
						
						
//--------------------- Determine Housing Option, Print Appropriate Information
							if($housing_option == 'neither') {
								echo '<div class="event-info-instance pay-for-events-component" id="payment">
										<div class="title-bar-text">Housing Information</div>
											<p>You have selected that you do not need housing and/or cannot offer housing to others.  If this is incorrect or your situation changes, please contact '.$contact_name.' via '.$contact_email.' as soon as possible so we can attempt to resolve any issues.</p></div>';
							} else if($housing_option == 'need') {
								echo '<div class="event-info-instance pay-for-events-component" id="payment">
										<div class="title-bar-text">Housing Information</div><p>
									<label>Housing Option:</label>  <b>'.$housing_option.'</b></br>
									<label>Is your housing guaranteed?:</label>  <b>'.$guaranteed.'</b></br>
									<label>Driving:</label>  <b>'.$driving.'</b></br>
									<label>Late Night:</label>  <b>'.$latenight.'</b></br>
									<label>Dogs:</label>  <b>'.$dogs.'</b></br>
									<label>Cats:</label>  <b>'.$cats.'</b></br>
									<label>Smoking:</label>  <b>'.$smoking.'</b></br>
									<label>Comments:</label>  <b>'.$comments.'</b></p>
									<p>Housing is guaranteed once you have registered and paid for certain passes (For large weekend events it will be Full Weekend Passes, check with the website for other events).  Our housing coordinator will be in touch via e-mail close to the event, please read through all of the related e-mails to ensure you have a place to stay that works for you!  If any information above is incorrect or your situation changes, please contact '.$contact_name.' via '.$contact_email.' as soon as possible so we can attempt to resolve any issues.</p></div>';
							} else if($housing_option == 'offer') {
								echo '<div class="event-info-instance pay-for-events-component" id="payment">
										<div class="title-bar-text">Housing Information</div><p>
									<label>Housing Option:</label>  <b>'.$housing_option.'</b></br>
									<label>Capacity:</label>  <b>'.$capacity.'</b></br>
									<label>Driving:</label>  <b>'.$driving.'</b></br>
									<label>Late Night:</label>  <b>'.$latenight.'</b></br>
									<label>Dogs:</label>  <b>'.$dogs.'</b></br>
									<label>Cats:</label>  <b>'.$cats.'</b></br>
									<label>Smoking:</label>  <b>'.$smoking.'</b></br>
									<label>Comments:</label>  <b>'.$comments.'</b></p>
									<p>THANK YOU SO VERY MUCH FOR HOSTING.  You are a truly incredible human being who is contributing to the success of this event and the peace of mind of the attendees by providing a place to stay for them.  Our housing coordinator will be in touch via e-mail close to the event, please read through all of the related e-mails to ensure you have a place to stay that works for you!  If any information above is incorrect or your situation changes, please contact '.$contact_name.' via '.$contact_email.' as soon as possible so we can attempt to resolve any issues.</p></div>';
							} else {
								echo '<div class="event-info-instance pay-for-events-component" id="payment">
										<div class="title-bar-text">Housing Information</div><p>Your housing information has an error.  This has been logged in our system so that our Web Administrator can address it.  Please contact '.$contact_name.' via '.$contact_email.' as soon as possible so we can attempt to resolve any issues.</p></div>';
								$error_msg = $timestamp.$filename." Housing Info Display did not work for HousingOption-".$housing_option." for Registration-".$registration_id."\n";
								error_log($error_msg, 3, $root."/backend/payments/_error.log");
							}
						} elseif($event_id === 25) {	
							echo '<hr><p><div class="payment-option-header"><label for="Housing Information">Housing Information</label></div>We are offering housing through a Google Form for this event, and all the details for housing will be handled by our Housing Coordinator!  Please fill out all of your information on the <a href="http://goo.gl/forms/pId7sCoa9N">Housing Request Form</a> so we can ensure you have an adequate place to stay for LaBLove 2016!!';
						} // End Housing ID check
						
//--------------------- End User Response Text ----------------------------------------------------------------------------------
						echo '	</div> <!-- Registration Display -->';								
						
					} else {
						$error_msg = $timestamp.$filename." Did not complete BIND RESULT entry for REGISTRATION SELECT with ".$registration_id."\n";
						error_log($error_msg, 3, $root."/backend/payments/_error.log");
					}
				} else {
					$error_msg = $timestamp.$filename." Did not complete EXECUTE entry for REGISTRATION SELECT with ".$registration_id."\n";
					error_log($error_msg, 3, $root."/backend/payments/_error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Did not complete BIND PARAMETER entry for REGISTRATION SELECT with ".$registration_id."\n";
				error_log($error_msg, 3, $root."/backend/payments/_error.log");
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete PREPARE entry for REGISTRATION SELECT with ".$registration_id."\n";
			error_log($error_msg, 3, $root."/backend/payments/_error.log");
		}
	} else {  // If a Registration ID is not sent..
		echo '
					<div class="section-title" id="event-id-switch">Pay For Events</div>	
					<div class="event-info-instance">
						<div id="registration-id-request">
							<p>Here you can check your current payment status, housing information (if available and requested for the event), and other registration information for the event you\'re attending.  To continue, enter your Registration ID below!  When you filled out the registration form, you should have also received an e-mail containing your Registration ID as well as a link to take you directly to your registration page.  If you did not receive that e-mail, please contact the committee respondible for the event (The contact information for the committee running an event will be on the event page) and let us know that you registered and did not receive an e-mail, and we\'ll confirm the information in our registration system.</p>
							<form>
								<label for="registration_id">Please enter a Registration ID:</label><input type="text" name="registration_id">
								<input class="event-submit-button brown-button" type="submit" value="Get Registration Info">
							</form>
						</div> <!-- Registration ID Request -->
					</div>';
	}
?>
<script src="//www.paypalobjects.com/api/checkout.js" async></script>