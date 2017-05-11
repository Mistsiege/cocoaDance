<?php
// Lindy and Blues Admin Registration Script
// Version 1.0.1
// Date Created:  02-05-2014 by Marc Longhenry
// 
// The purpose of this script is to be a main collection of current registration for an event.  It will give totals and a list of registrants
// as well as some relevant information to pricing, payment method, and what individuals may be comped for.
//
// Date		Developer  				Modified
// -------- ----------------------  ----------------------------------------------------------------------------------------------------------------------
// 02-05-14 Marc Longhenry  		File Created, modified to use the database tables to retrieve information on current registration
// 04-23-14		"					Fixed User ID display error
// 12-03-15 	-					Updated Error Messages for the correct directory from $root
	
// Establish Error Logging Details  ----------------------------------------------------------------------------------------------------------------------
$timestamp = date("d-m-y H:i:s");
$filename = " admin-registration.php";  // Space added in front for alignment and spacing purposes	
	
// If Event ID is sent with a URL, POST, or otherwise generated, produce payment information and interface  ----------------------------------------------	
if($_GET['event_id'] or $_POST['event_id'] or $event_id) { // If an Event ID is sent.. Assign Event ID
	if($_GET['event_id']) {
		$event_id = $_GET['event_id'];
	}
	if($_POST['event_id']) {
		$event_id = $_POST['event_id'];
	}
	
	// Establish Database Connection
	include("db.php");
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error."\n";
		error_log($error_msg, 3, $root."/registration/registration/error.log");
	}
	
	// Select Registration Information based on Event ID  ------------------------------------------------------------------------------------------------
	$records = 0;  // Set to -1.  Once While Loop commences will become 0 and associate to an array properly.
	$total_price = 0;  // For total amount owed for the event
	$total_amount = 0;  // For total amount paid so far
	$followCount = 0;
	$leadCount = 0;
	$followPaid = 0;
	$leadPaid = 0;
	$followComp = 0;
	$leadComp = 0;
	$followOutstanding = 0;
	$leadOutstanding = 0;
	$totalPaid = 0;
	$totalUnpaid = 0;
	$totalComped = 0;
	$totalReg = 0;
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
	
	if ($result = $mysqli->query("SELECT registration.registration_id, registration.user_id, users.fname, users.lname, users.email, users.role, registration.price_id, prices.price, prices.title, registration.payment_id, payments.method, payments.amount, payments.status, registration.code_id, codes.price AS code_price FROM registration, users, prices, payments, codes WHERE registration.user_id = users.user_id AND registration.price_id = prices.price_id AND registration.payment_id = payments.payment_id AND registration.event_id = $event_id AND registration.code_id = codes.code_id ORDER BY registration.code_id, registration.price_id ASC")) {
		while($row = $result->fetch_assoc()) {
			$record++;
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
			// Perform counts
			if($row['role']=='Lead') {
				$leadCount++;
				if($row['status']=='Paid') {
					$leadPaid++;
					$totalPaid = $totalPaid + $row['amount'];
					$totalReg = $totalReg + $row['price'];
				} elseif($row['status']=='Not Yet Paid') {
					$leadOutstanding++;
					$totalUnpaid = $totalUnpaid + $row['price'];
					$totalReg = $totalReg + $row['price'];
				} elseif(strpos($row['status'], 'Comped') !== false) {  // Assumes all Codes have been created with a status that states Comped somewhere
					$leadComped++;
					$totalComped = $totalComped + $row['code_price'];
					$totalReg = $totalReg + $row['code_price'];
				} else {
					$error_msg = $timestamp.$filename." Status does not fit Paid/Note Yet Paid or Comped in some fashion. -- ".$row['status']."\n";
					error_log($error_msg, 3, $root."/registration/registration/error.log");
				}
			} elseif($row['role']=='Follow') {
				$followCount++;
				if($row['status']=='Paid') {
					$followPaid++;
					$totalPaid = $totalPaid + $row['amount'];
					$totalReg = $totalReg + $row['price'];
				} elseif($row['status']=='Not Yet Paid') {
					$followOutstanding++;
					$totalUnpaid = $totalUnpaid + $row['price'];
					$totalReg = $totalReg + $row['price'];
				} elseif(strpos($row['status'], 'Comped') !== false) {  // Assumes all Codes have been created with a status that states Comped somewhere
					$followComped++;
					$totalComped = $totalComped + $row['code_price'];
					$totalReg = $totalReg + $row['code_price'];
				} else {
					$error_msg = $timestamp.$filename." Status does not fit Paid/Note Yet Paid or Comped in some fashion. -- ".$row['status']."\n";
					error_log($error_msg, 3, $root."/registration/registration/error.log");
				}
			} else {
				$error_msg = $timestamp.$filename." Role does not fit Lead/Follow. -- ".$row['role']."\n";
				error_log($error_msg, 3, $root."/registration/registration/error.log");
			}
		}  /* fetch value */
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Failed to QUERY for REGISTRATION INFORMATION SELECT using EVENT ID ".$event_id.".  Error is ".$mysqli->error."\n";
		error_log($error_msg, 3, $root."/registration/registration/error.log");
	}
// Generate Display for Totals Information  --------------------------------------------------------------------------------------------------------------	
	echo "<div id='event-registration-totals'>
			<table>
				<tr>
					<th>-</th>
					<th>Leads</th>
					<th>Follows</th>
					<th>Total Number</th>
					<th>Total Money</th>
				</tr>
				<tr>
					<td>Registration</td>
					<td>".$leadCount."</td>
					<td>".$followCount."</td>
					<td>".($leadCount+$followCount)."</td>
					<td>$".$totalReg."</td>
				</tr>
				<tr>
					<td>Outstanding</td>
					<td>".$leadOutstanding."</td>
					<td>".$followOutstanding."</td>
					<td>".($leadOutstanding+$followOutstanding)."</td>
					<td>$".$totalUnpaid."</td>
				</tr>
				<tr>
					<td>Paid</td>
					<td>".$leadPaid."</td>
					<td>".$followPaid."</td>
					<td>".($leadPaid+$followPaid)."</td>
					<td title='Total amount paid to LaB, includes transaction fees'>$".$totalPaid."</td>
				</tr>
				<tr>
					<td>Comped</td>
					<td>".$leadComped."</td>
					<td>".$followComped."</td>
					<td>".($leadComped+$followComped)."</td>
					<td>$".$totalComped."</td>
				</tr>
				<tr>
					<td>Attending</td>
					<td>".($leadCount-$leadOutstanding)."</td>
					<td>".($followCount-$followOutstanding)."</td>
					<td>".($leadCount+$followCount-$leadOutstanding-$followOutstanding)."</td>
					<td title='Registration Total minus Unpaid Total'>$".($totalReg-$totalUnpaid)."</td>
				</tr>
			</table>
		</div> <!-- Event Registration Totals -->";
		
// Generate Display for Registrant Information  ----------------------------------------------------------------------------------------------------------
	$display = 1;
	echo "<div id='event-registration-list'>
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
				<td colspan=9>http://www.lindyandblues.com/pay-for-events?registration_id=".$registration_id[$display]."</td>";
		$display++;
	}	
	echo "</table>
		</div> <!-- Event Registration List -->";
		
		
// If Event ID is NOT sent with a URL, POST, or otherwise generated, produce payment information and interface  ------------------------------------------	
} else {
	echo '<div id="event-id-request">	
			<form>
				<label for="event_id">Please enter an Event ID:</label><input type="text" name="event_id">
				<input type="submit" value="Get Event Registration Info">
			</form>
		</div> <!-- Event ID Request -->';
}	

?>