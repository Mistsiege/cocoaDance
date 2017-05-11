<?php
	// Lindy and Blues Registration Post - Weekend Script
	// Version 1.0.1
	// Date Created:  12-26-2013 by Marc Longhenry
	// 
	// The purpose of this script is to display a form to fill out in order to register for a Weekend Event.  This form
	// will submit to the Pay For Events page which will direct the information to create a user and registration
	// in our databases for the registrant.
	//
	// Date		Developer  				Modified
	// -------- ----------------------  --------------------------------------------------------------------------
	// 12-26-13 Marc Longhenry  		File Created, modified to take registrant information and send it to Pay For Events.
	// 04-02-14	    "					Modified to not display if Registration is not open yet.
	// 07-01-14 	"					Modified start time process to use full datetime instead of minutes since epoch
	
	// Set Variables for Error Logging  ------------------------------------------------------------------------
	$timestamp = date("d-m-y H:i:s");
	$filename = " create-event.php ";  // Space added in front for alignment and spacing purposes

	// Search Users using e-mail (assumed to be unique) go retrieve User_id for transaction processing.
	include('db.php');
	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		$error_msg = $timestamp.$filename." Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		error_log($error_msg, 3, "error.log");
		echo "Failed to connect to MySQL please contact us with the following error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}

	// Query Database to see if Registration is Open for given event_id
	$registration_flag = 0;
	if ($result = $mysqli->query("SELECT reg_start FROM events WHERE event_id = $event_id")) {
		if($row = $result->fetch_assoc()) {
			$curtime = time();
			$rightnow = date('Y-m-d H:i:s', $curtime);
			$starttime = $row['reg_start'];
			if($rightnow > $starttime || $vip == 1) {
				$registration_flag = 1;
			}
		} else {
			$error_msg = $timestamp.$filename." Did not complete FETCH ASSOC for EVENT ID SELECT with ".$event_id."\n";
			error_log($error_msg, 3, "error.log");	
		}
		$result->close();
	} else {
		$error_msg = $timestamp.$filename." Did not complete MYSQLI QUERY for EVENT ID SELECT with ".$event_id."\n";
		error_log($error_msg, 3, "error.log");	
	}
	
	if($registration_flag == 1) {
?>
<script src="../../scripts/page-specific.js" type='text/javascript'></script>
<div id="event-registration-form">
    <p>Please enter all the information below, each piece is needed to ensure we are able to contact you, keep your information organized in our database, and keep on-site registration and any requests/hiccups as smooth as possible!  Thanks!</p><br />
    <h1>Dancer Information</h1><hr />
    <form method='post' action='../../events/pay-for-events'>
        <label>First Name:</label><input type='text' name='fname' placeholder="First Name" required><br />
        <label>Last Name:</label><input type='text' name='lname' placeholder="Last Name" required><br />
        <label>E-mail:</label><input type='text' name='email' placeholder="You@youremail.com" required><br />
        <label>Phone:</label><input type='text' name='phone' placeholder="999-888-7777" required><br />
        <label>City:</label><input type='text' name='city' placeholder="Philadelphia" required><br />
        <label>State:</label><input type='text' name='state' placeholder="PA (Two digit state please!)" required><br />
        <label>Country:</label><input type='text' name='country' placeholder="USA" required><br />
        <label>Role:</label><select name='role' required>
			<option value=''>Please Select a Role</option>
            <option value='Lead'>Lead</option>
            <option value='Follow'>Follow</option>
        </select><br />
        <label>Code:</label><input type='text' name='code' /><br />
        <label for="event_id">Event:</label><select name="event_id" onchange="fillEventForm(this.value, 'price-dropdown', 'purchase')" required><?php include("event-dropdown.php"); ?></select>
        </select><br />
        <div id="edit-price-dropdown"></div>
        <div id="hidden-item-price-desc"></div><br /><br />
    <h1>Housing Information</h1><hr />
    <p>We are glad to have you coming to our city to enjoy a weekend of dancing with us!  We are so excited about that some of us decided to house you all!  Please bear in mind that housing is <b>LIMITED</b>.  It is a first come first serve basis and we will poke and prod people to house if we are able to.  People who are offering housing are doing so out of the goodness of their hearts and at the personal expense, or the expense of housemates they had to bake cookies for to persuade to let you stay.  As such, you may get a range of options from a 2'x6' piece of floor to a fully furnished bed, from directions to the nearest subway station to door-to-door transportation, and from directions to Wawa to a professional chef who makes you breakfast to order.. Please thank and appreciate your hosts regardless of situation!  Please do not expect anything more than a 2'x6' piece of floor.</p>
	<?php include('housing-cap.php'); 
		// set timezone to user timezone
		date_default_timezone_set('America/New_York');
		if($housing_end >= date('Y-m-d H:i:s')) { ?>
    	<label>Housing:</label><select name='housing_option' required>
        	<option value=''>Please Choose One</option>
        	<option value='need'>I Need Housing</option>
            <option value='offer'>I Can Offer Housing</option>
            <option value='neither'>Don't Need & Can't Offer</option>
        </select><br />
		<p>If you select "I Need Housing" or "I Can Offer Housing" please fill out the following information so we can best match people up!  (Everyone else doesn't need to fill the form up until "Share Name")</p>
       	<label>Number of People (Offering only):</label><input type='text' name='capacity'  placeholder="3-4"/><br />
        <p>Please let us know how many people you can reasonably house, feel free to give a range such as 3-5 people.  A general rule is about 3-4 people per bathroom available.</p>
		<label>Will you be driving to events?</label><select name='driving'>
        	<option value=''>Please Choose One</option>
        	<option value='yescar'>Yes, I have a car</option>     
        	<option value='nocar'>No, I will use friends/public transit</option>
        </select><br />
        <p>If you have a car and you are driving people, please list "Yes, I have a Car", everyone else please select "No".</p>
        <label>Are you attending Late Nights?</label><select name='latenight'>
        	<option value=''>Please Choose One</option>
        	<option value='yes'>Yes, I would like to attend Late Nights</option>
            <option value='no'>No, I would like to sleep</option>
        </select><br />
        <label>Do You Like/Have Dogs?</label><select name='dogs'>
        	<option value=''>Please Choose One</option>
        	<option value='havedogs'>Yes, I have dogs (offering housing)</option>
            <option value='likedogs'>Yes, I don't mind dogs or I like dogs</option>
            <option value='nodogs'>No, I do not have dogs (offering housing)</option>
            <option value='notlikedogs'>No, I mind dogs or I do not like dogs</option>
        </select><br />
        <label>Do You Like/Have Cats?</label><select name='cats'>
        	<option value=''>Please Choose One</option>
        	<option value='havecats'>Yes, I have cats (offering housing)</option>
            <option value='likecats'>Yes, I don't mind cats or I like cats</option>
            <option value='nocats'>No, I do not have cats (offering housing)</option>
            <option value='notlikecats'>No, I mind cats or I do not like cats</option>
        </select><br />
		<label>Do You Smoke or Mind Smoking?</label><select name='smoking'>
        	<option value=''>Please Choose One</option>
        	<option value='smoke'>Yes, I Smoke</option>
            <option value='dontmind'>I do not smoke, but do not mind smokers</option>
            <option value='mindsmoking'>No, I don't want to be around smoke</option>
        </select><br />
		<?php } ?>
		<label>Would you like your name on the Who Is Coming page?</label><select name='share_name' required>
        	<option value=''>Please Choose One</option>
        	<option value='yes'>Yes</option>
            <option value='no'>No</option>
        </select><br /><br />
		<?php if($housing_end >= date('Y-m-d H:i:s')) { ?>
		<label>Comments</label><input type='textarea' name='comments'  placeholder="Comments Placed Here"/>
        <p>If you have any additional comments to add to your Housing registration, please list them here.  If you can also give us a list of people you are traveling with, we will do our best to house you together, but will make no guarantees that it will happen due to limited space for housing.</p>
		<?php } ?>
		<input type='submit' value='Submit'>
    </form>
</div> <!-- Event Registration Form -->
<?php
	} else {
		echo 'Registration is not open for this event just yet!  Registration is scheduled to open '.date('F jS, Y - g:i A', strtotime($row['reg_start'])).'!!';
	}
?>