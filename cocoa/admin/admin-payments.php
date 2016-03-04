<?php
	/** 
	* Payment Management Script
	*
	* The purpose of this script is to be a payment collection of current registration for an event.  It will give totals and a list of registrants
	* as well as some relevant information to pricing, payment method, and what individuals may be comped for.  It will also allow for follow up
	* e-mails to be sent and for payment information to be updated for Checks and errors in the system.
	*/
	
	// If Event ID is sent with a URL, POST, or otherwise generated, produce payment information and interface  ----------------------------------------------	
	if($_GET['event_id'] or $_POST['event_id'] or $event_id) { // If an Event ID is sent.. Assign Event ID
		if($_GET['event_id']) {
			$event_id = $_GET['event_id'];
		}
		if($_POST['event_id']) {
			$event_id = $_POST['event_id'];
		}
		
	// Establish Error Logging Details  ----------------------------------------------------------------------------------------------------------------------
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/cocoa/settings/db.php');
	$filename = " admin-payments.php";  // Space added in front for alignment and spacing purposes	

	echo "<div id='top' class='title02' style='text-align:left;'>Payments<img src='http://www.lindyandblues.com/wp-content/uploads/2012/12/lines2.png'></div>
		All payment related information, totals, and admin tools will be available in this page.  Please do not modify anything unless you are sure of the accuracy of your new information.
	<div id='admin-payment-nav'><a href='#registration-totals'>Go to Registration Totals</a> - <a href='#registration-list'>Go to Registration List</a> - <a href='#change-payment'>Go to Change Payment Information</a> - <a href='#event-id-switch'>Switch Event</a></div>";
	
	// Select Registration Information based on Event ID  ------------------------------------------------------------------------------------------------
		$records = 0;  // Set to -1.  Once While Loop commences will become 0 and associate to an array properly.
		$total_price = 0;  // For total amount owed for the event
		$total_amount = 0;  // For total amount paid so far
		$followReg = 0;
		$leadReg = 0;
		$followPaid = 0;
		$leadPaid = 0;
		$followComp = 0;
		$leadComp = 0;
		$followOutstanding = 0;
		$leadOutstanding = 0;
		$followCanceled = 0;
		$leadCanceled = 0;
		$leadHousing = 0;
		$followHousing = 0;
		$totalPaid = 0;
		$totalFees = 0;
		$totalOutstanding = 0;
		$totalComped = 0;
		$totalReg = 0;
		$totalCanceled = 0;
		$registration_id = array();
		$user_id = array();
		$fname = array();
		$lname = array();
		$email = array();
		$role = array();
		$price_id = array();
		$price = array();
		$title = array();
		$payment_id = array();
		$method = array();
		$amount = array();
		$status = array();
		$code_id = array();
		$code_price = array();
		
		if ($result = $mysqli->query("SELECT registration.registration_id, registration.user_id, users.fname, users.lname, users.email, registration.role, registration.price_id, prices.price, prices.title, registration.payment_id, payments.method, payments.amount, payments.status, registration.code_id, codes.price AS code_price FROM registration, users, prices, payments, codes WHERE registration.user_id = users.user_id AND registration.price_id = prices.price_id AND registration.payment_id = payments.payment_id AND registration.event_id = $event_id AND registration.code_id = codes.code_id  ORDER BY payments.status, registration.role ASC")) {
			while($row = $result->fetch_assoc()) {
				// Store all the data to be displayed in a table later
				$registration_id[$record] = $row['registration_id'];
				$user_id[$record] = $row['user_id'];  // 4/23/14 - changed from user.id to user_id to pull the right info
				$fname[$record] = $row['fname'];
				$lname[$record] = $row['lname'];
				$email[$record] = $row['email'];
				$role[$record] = $row['role'];
				$price_id[$record] = $row['price_id'];
				$price[$record] = $row['price'];
				$title[$record] = $row['title'];
				$payment_id[$record] = $row['payment_id'];
				$method[$record] = $row['method'];
				$amount[$record] = $row['amount'];
				$status[$record] = $row['status'];
				$code_id[$record] = $row['code_id'];
				$code_price[$record] = $row['code_price'];
				
// Perform Counts ------------------------------------------------------------------------------------------------------------------------------------------
				if($row['role']==='Lead') {
					if($row['status']==='Paid') {
						$leadReg++;
						$leadPaid++;
						$totalPaid = $totalPaid + $row['price'];
						$totalReg = $totalReg + $row['price'];
						$totalFees = $totalFees + $row['amount'];
					} elseif($row['status']==='Not Yet Paid') {
						$leadReg++;
						$leadOutstanding++;
						$totalOutstanding = $totalOutstanding + $row['price'];
						$totalReg = $totalReg + $row['price'];
						$totalFees = $totalFees + $row['amount'];
					} elseif($row['status'] === 'Canceled' || $row['status'] === 'Refunded') {
						$leadCanceled++;
						$totalCanceled = $totalCanceled + $row['price'];
					} elseif($row['status'] === 'Offered Housing') {
						$leadHousing++;
					} elseif(strpos($row['status'], 'Comped') !== false) {  // Assumes all Codes have been created with a status that states Comped somewhere
						$leadReg++;
						$leadComped++;
						$totalComped = $totalComped + $row['code_price'];
						$totalReg = $totalReg + $row['code_price'];
					}
				} elseif($row['role']==='Follow') {
					if($row['status']==='Paid') {
						$followReg++;
						$followPaid++;
						$totalPaid = $totalPaid + $row['price'];
						$totalReg = $totalReg + $row['price'];
						$totalFees = $totalFees + $row['amount'];
					} elseif($row['status']==='Not Yet Paid') {
						$followReg++;
						$followOutstanding++;
						$totalOutstanding = $totalOutstanding + $row['price'];
						$totalReg = $totalReg + $row['price'];
						$totalFees = $totalFees + $row['amount'];
					} elseif($row['status'] === 'Canceled' || $row['status'] === 'Refunded') {
						$followCanceled++;
						$totalCanceled = $totalCanceled + $row['price'];
					} elseif($row['status'] === 'Offered Housing') {
						$followHousing++;
					} elseif(strpos($row['status'], 'Comped') !== false) {  // Assumes all Codes have been created with a status that states Comped somewhere
						$followReg++;
						$followComped++;
						$totalComped = $totalComped + $row['code_price'];
						$totalReg = $totalReg + $row['code_price'];
					}
				} else {
					$error_msg = $timestamp.$filename." Role does not fit Lead/Follow. -- ".$row['role']." for event ".$event_id."\n";
					error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
				}
				$record++;
			}  /* fetch value */
			$result->close();
		} else {
			$error_msg = $timestamp.$filename." Failed to QUERY for REGISTRATION INFORMATION SELECT using EVENT ID ".$event_id.".  Error is ".$mysqli->error."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		}
	// Generate Display for Totals Information  --------------------------------------------------------------------------------------------------------------	
		echo "<div id='registration-totals' class='title02' style='text-align:left;'>Registration Totals<img src='http://www.lindyandblues.com/wp-content/uploads/2012/12/lines2.png'></div>
			<div id='event-registration-totals'>
				<table>
					<tr>
						<th>-</th>
						<th>Leads</th>
						<th>Follows</th>
						<th>Total Number</th>
						<th>Total Money</th>
					</tr>
					<tr>
						<td>Registration (All)</td>
						<td>".$leadReg."</td>
						<td>".$followReg."</td>
						<td>".($leadReg+$followReg)."</td>
						<td>$".$totalReg."</td>
					</tr>
					<tr>
						<td>Outstanding</td>
						<td>".$leadOutstanding."</td>
						<td>".$followOutstanding."</td>
						<td>".($leadOutstanding+$followOutstanding)."</td>
						<td>$".$totalOutstanding."</td>
					</tr>
					<tr>
						<td>Paid (Attending)</td>
						<td>".$leadPaid."</td>
						<td>".$followPaid."</td>
						<td>".($leadPaid+$followPaid)."</td>
						<td title='Total amount based on Pass Prices with Total Paid with Paypal/etc fees in Parenthesis'>$".$totalPaid." / ($".$totalFees.")</td>
					</tr>
					<tr>
						<td>Comped</td>
						<td>".$leadComped."</td>
						<td>".$followComped."</td>
						<td>".($leadComped+$followComped)."</td>
						<td>$".$totalComped."</td>
					</tr>
					<tr>
						<td>Canceled/Refunded</td>
						<td>".$leadCanceled."</td>
						<td>".$followCanceled."</td>
						<td>".($leadCanceled+$followCanceled)."</td>
						<td>$".$totalCanceled."</td>
					</tr>
					<tr>
						<td>Offered Housing</td>
						<td>".$leadHousing."</td>
						<td>".$followHousing."</td>
						<td>".($leadHousing+$followHousing)."</td>
						<td>$0</td>
					</tr>
				</table>
				<a href='#top' class='float_right'>back to top</a><br /><br />
			</div> <!-- Event Registration Totals -->";
			
	// Generate Total Ticket Breakdown  ----------------------------------------------------------------------------------------------------------------------		
		echo "<div id='ticket-breakdown' class='title02' style='text-align:left;'>Ticket Breakdown<img src='http://www.lindyandblues.com/wp-content/uploads/2012/12/lines2.png'></div><table>
					<tr>
						<th>Item</th>
						<th>Price</th>
						<th># Bought</th>
					</tr>";
		if ($result = $mysqli->query("SELECT prices.title, prices.price, COUNT(registration.price_id) as purchased FROM prices LEFT JOIN registration ON prices.price_id = registration.price_id, payments WHERE registration.payment_id = payments.payment_id AND prices.event_id = $event_id AND payments.status != 'Refunded' AND payments.status != 'Canceled' GROUP BY prices.price_id ORDER BY prices.price DESC")) {
			while($row = $result->fetch_assoc()) {	
				echo "<tr>
					<td>".$row['title']."</td>
					<td>".$row['price']."</td>
					<td>".$row['purchased']."</td>
				</tr>";
			}  /* fetch value */
			$result->close();
			echo "</table>";
		} else {
			$error_msg = $timestamp.$filename." Failed to QUERY for TICKET BREAKDOWN SELECT using EVENT ID ".$event_id.".  Error is ".$mysqli->error."\n";
			error_log($error_msg, 3, $root."/cocoa/admin/_error.log");
		}			
			
	// Generate Display for Registrant Information  ----------------------------------------------------------------------------------------------------------
		$display = 0;
		echo "<div id='registration-list' class='title02' style='text-align:left;'>Registration List<img src='http://www.lindyandblues.com/wp-content/uploads/2012/12/lines2.png'></div>
		<div id='event-registration-list'>
				<table>
					<tr>
						<th>Name</th>
						<th>E-Mail</th>
						<th>Role</th>
						<th>Price</th>
						<th>Title</th>
						<th>Status</th>
						<th title='Method of Payment'>MD</th>
						<th title='Amount Paid (with Fees)'>AMT</th>
						<th title='Discount Price'>DP</th>
					</tr>";
		while($display <= $record) {  // 4/23/14 - Added user_id display
			echo "<tr>
					<td>".$fname[$display]." ".$lname[$display]."(".$registration_id[$display].")</td>  
					<td>".$email[$display]."</td>
					<td>".$role[$display]."</td>
					<td>".$price[$display]."</td>
					<td>".$title[$display]."</td>
					<td>".$status[$display]."</td>
					<td>".$method[$display]."</td>
					<td>".$amount[$display]."</td>
					<td>".$code_price[$display]."</td>
				</tr>
				<tr>
					<td class='bottom-border-td' colspan=9>http://www.lindyandblues.com/pay-for-events?registration_id=".$registration_id[$display]."</td>";
			$display++;
		}	
		echo '	</table>
				<a href="#top" class="float_right">back to top</a><br /><br />
			</div> <!-- Event Registration List -->';
		
		
	// Generate Change Payment Information Form
		echo '<div id="change-payment">
			<div id="top" class="title02" style="text-align:left;">Change Payment Information<img src="http://www.lindyandblues.com/wp-content/uploads/2012/12/lines2.png"></div>
				<span id="change-payment-detail">This segment is designed to allow organizers to change payment information, primarily updating Checks, for individuals attending the given event. Please DO NOT CHANGE ANYTHING unless you are absolutely sure of the accuracy of your information.  Mouse-over the labels for drop downs and text boxes for more information.
				<form id="payment-info-form" action="../../cocoa/admin/edit-payment.php" method="POST">
					<span title="User\'s Last Name, First Name, User ID, and E-mail">Select User</span> - <select name="registration_id" id="reg_id" onchange="getPaymentInfo(this.value)">';
					
	// Search for users for this event.  Create a prepared statement.  Avoids SQL injection.
					if ($result = $mysqli->query("SELECT registration.registration_id, registration.user_id, users.fname, users.lname, users.email FROM registration, users WHERE registration.event_id = $event_id AND registration.user_id = users.user_id ORDER BY users.lname ASC")) {
						echo '<option value="">Please Select a User</option>';
						while($row = $result->fetch_assoc()) {
							echo '<option value="'.$row['registration_id'].'">'.$row['lname'].', '.$row['fname'].' <'.$row['user_id'].'> '.$row['email'].'</option>';
						}
						$result->close();
					} else {
						echo 'Error, unable to create mysqli statement';	
					}
		echo 		'</select>
					<div id="edit-payment-info"></div>
				</form>
				<a href="#top" class="float_right">back to top</a><br /><br />
			</div>';
	// Give the option to change the event you are looking at payment info for		
		echo '<div id="event-id-switch">
			<div id="top" class="title02" style="text-align:left;">Switch Event<img src="http://www.lindyandblues.com/wp-content/uploads/2012/12/lines2.png"></div>
				<span id="switch-event-detail">This segment will allow you to select a different event to grab payment information from.</span>
				<div id="switch-event">	
					<form>
						<label for="event_id">Payment Information for Event:</label><select name="event_id">';
						include("backend/admin/event-dropdown-all.php");
				echo '	</select>
						<input type="submit" value="Grab Event Payment Information">
					</form>
				</div> <!-- Event ID Request -->
				<a href="#top" class="float_right">back to top</a><br /><br />
			</div>';		
	// If Event ID is NOT sent with a URL, POST, or otherwise generated, produce payment information and interface  ------------------------------------------	
	} else {
		echo '<div id="switch-event">	
				<form>
					<label for="event_id">Payment Information for Event:</label><select name="event_id">';
					include("backend/admin/event-dropdown-all.php");
		echo '		</select>
					<input type="submit" value="Grab Event Payment Information">
				</form>
			</div> <!-- Event ID Request -->';
	}	

?>