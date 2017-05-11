<?php
	// Lindy and Blues Registration Script
	// Version 1.0.1
	// 
	// The purpose of this script is to display a form to fill out in order to register for a Weekend Event.  This form
	// will submit to the Pay For Events page which will direct the information to create a user and registration
	// in our databases for the registrant.
	//
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-26-13 Marc Longhenry  		File Created, modified to take registrant information and send it to Pay For Events.
	// 04-02-14		"					Modified to not display if Registration is not open yet.
	// 08-10-14		"					Added VIP Functionality, added Follow Registration VIP
	// 11-11-14 	"					Added dynamic information based on event timing and status.  Changed to go right to price choice instead of choosing an event.
	// 02-16-15		"					Added to query and refund policy to dynamically display timeframes for refunds.
	// 06-06-15		"					Upgraded to use DB2.php
	
	// Include Database Information  ----------------------------------------------------------------------------
	$root = $_SERVER['DOCUMENT_ROOT'];
	include($root.'/backend/dbstatic/db2.php');
	$filename = " registration.php";  // Space added in front for alignment and spacing purposes

	// Grab Event Information, determine if Registration is Open  -----------------------------------------------
	$registration_flag = 0;
	if($_GET['event_id']) {
		$event_id = $_GET['event_id'];
	}
	if ($result = $mysqli->query("SELECT * FROM events WHERE event_id = $event_id")) {
		if($row = $result->fetch_assoc()) {
			$curtime = time();
			$rightnow = date('Y-m-d H:i:s', $curtime);
			$name = $row['name'];
			$startTime = $row['s_time'];
			$reg_start = $row['reg_start'];
			$reg_flag = $row['reg_flag'];
			$reg_end = $row['reg_end'];
			$whois_flag = $row['whois_flag'];
			$code_flag = $row['code_flag'];
			$extra_flag = $row['extra_flag'];
			$housing_flag = $row['housing_flag'];
			$lead_flag = $row['lead_flag'];
			$follow_flag = $row['follow_flag'];
			$contact_name = $row['contact_name'];
			$contact_email= $row['contact_email'];
			if(($rightnow > $reg_start || $vip == 1) && $reg_flag == 'open') {
				$registration_flag = 'open';
			}
			if($rightnow < $reg_start && $reg_flag == 'open') {
				$registration_flag = 'notyet';
			}
			if($rightnow > $reg_end && $reg_flag == 'open') {
				$registration_flag = 'ended';
			}
			if($reg_flag == 'closed') {
				$registration_flag = 'closed';
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete FETCH ASSOC for REGISTRATION STATUS SELECT with ".$event_id."\n";
			error_log($error_msg, 3, $root."/registration/registration/error.log");
		}
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for REGISTRATION STATUS SELECT with ".$event_id."\n";
		error_log($error_msg, 3, $root."/registration/registration/error.log");
	}
	
	// Determine VIP Status, override Registration Closed  -----------------------------------------------------
	if($_GET['vip']) {
		$vip = $_GET['vip'];
	}
	if($vip == 1) { 
		$registration_flag = 'open';
	}
	
	
	// Display Registration  -----------------------------------------------------------------------------------
	if($registration_flag == 'open') {
?>
<script src="../../scripts/page-specific.js" type='text/javascript'></script>
<div id="event-registration-form">
    <p>Please enter all the information below, each piece is needed to ensure we are able to contact you, keep your information organized in our database, and keep on-site registration and any requests/hiccups as smooth as possible!  Thanks!</p>
		<form method='post' action='../../events/pay-for-events'>
	<div class="registration-section">
		<h1>Dancer Information</h1><hr />
			<label>First Name:</label><input type='text' name='fname' placeholder="First Name" required><br />
			<label>Last Name:</label><input type='text' name='lname' placeholder="Last Name" required><br />
			<label>E-mail:</label><input type='text' name='email' placeholder="You@youremail.com" required><br />
			<label>Phone:</label><input type='text' name='phone' placeholder="999-888-7777" required><br />
			<label>City:</label><input type='text' name='city' placeholder="Philadelphia" required><br />
			<label>State:</label><input type='text' name='state' placeholder="PA (Two digit state please!)" maxlength="2" required><br />
			<label>Country:</label><input type='text' name='country' placeholder="USA" required><br />
			<label>Role:</label><select name='role' required>
				<option value=''>Please Select a Role</option>
				<?php 
					if($vip == 1 || $lead_flag == 'open') {
						echo "<option value='Lead'>Lead</option>";
					} 
					if($vip == 1 || $follow_flag == 'open') {
						echo "<option value='Follow'>Follow</option>";
					} 
				?>
			</select><br />
			<label for="event_id">Event:</label><input type="text" value="<?php echo $name; ?>" disabled><input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
			<?php 
				if($code_flag == 1) {
					echo "<label>Code:</label><input type='text' name='code' placeholder='Leave this blank unless you have a discount code' ><br />";
				}
			?>
			<?php include($root.'/backend/registration/price-dropdown.php'); ?>
			</select><br />
			<div id="edit-price-dropdown"></div>
			<div id="hidden-item-price-desc"></div>
	</div> <!-- Dancer Information Section -->
<?php
		if($whois_flag != 'no') {
?>		

		<br /><label>Would you like your name on the Who Is Coming page?</label><select name='share_name' required>
        	<option value=''>Please Choose One</option>
        	<option value='yes'>Yes</option>
            <option value='no'>No</option>
        </select><br /><br />
<?php
		} // End Who Is Flag Check
?>
		<input class='event-submit-button' type='submit' value='Submit'>
    </form><br /><br />
	<h1>Policy on Refunds for <?php echo $name; ?></h1>
	<p>- You will get a full refund if you cancel your registration before <?php echo date('F jS', strtotime('11 days ago', strtotime($startTime))); ?><br />
	- You will get a half refund if you cancel your registration between <?php echo date('F jS', strtotime('10 day ago', strtotime($startTime))); ?> and <?php echo date('F jS', strtotime('2 days ago', strtotime($startTime))); ?> (inclusive)<br />
	- There will be no refunds for registrations cancelled on or after <?php echo date('F jS', strtotime('1 day ago', strtotime($startTime))); ?></p>
	<h1>If you have any questions, please contact <?php echo $contact_name.' via '.$contact_email; ?>.</h1>
</div> <!-- Event Registration Form -->
<?php
	} elseif($registration_flag == 'notyet') {
		echo 'Registration is scheduled to open '.date('F jS, Y - g:i A', strtotime($row['reg_start'])).'!!'.$vip;
	} elseif($registration_flag == 'ended') {	
		echo 'Registration closed at '.date('F jS, Y - g:i A', strtotime($row['reg_end'])).'!!';
	} elseif($registration_flag == 'closed') {
		echo 'Registration is closed for this event!';
	}
?>