<script src="../../scripts/page-specific.js" type='text/javascript'></script>
<div id="event-registration-form">
    <form method='post' action='../events/pay-for-events'>
        <label>First Name:</label><input type='text' name='fname'><br />
        <label>Last Name:</label><input type='text' name='lname'><br />
        <label>E-mail:</label><input type='text' name='email'><br />
        <label>Phone:</label><input type='text' name='phone'><br />
        <label>City:</label><input type='text' name='city'><br />
        <label>State:</label><input type='text' name='state'><br />
        <label>Country:</label><input type='text' name='country'><br />
        <label>Role:</label><select name='role'>
            <option value='Lead'>Lead</option>
            <option value='Follow'>Follow</option>
        </select><br />
        <label for="event_id">Event:</label><select name="event_id" onchange="fillEventForm(this.value, 'price-dropdown', 'purchase')"><?php include("event-dropdown.php"); ?></select>
        </select><br />
        <div id="edit-price-dropdown"></div>
        <div id="hidden-item-price-desc"></div>
        
        
        <input type='submit' value='Submit'>
    </form>
</div> <!-- Event Registration Form -->