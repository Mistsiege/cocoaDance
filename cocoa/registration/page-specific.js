// Fills the forms for Create/Edit Event Page and the Event Registration Page
function fillEventForm(value, path, purpose) {
	if (value == "") { // Value should be Event ID for first use, Price ID for second
		document.getElementById("txtHint").innerHTML = "Please select an item.";
		return;
	}
	if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	} else { // code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = function() {
		if (path === "edit-event") {
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				document.getElementById("edit-event-fill-form").innerHTML = xmlhttp.responseText;
			}
		} else if (path === "price-dropdown") {
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				document.getElementById("edit-price-dropdown").innerHTML = xmlhttp.responseText;
			}
		} else if (path === "edit-price" && purpose === "edit") {			
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				document.getElementById("edit-price-fill-form").innerHTML = xmlhttp.responseText;
			}
		} else if (path === "edit-price" && purpose === "purchase") {			
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				document.getElementById("hidden-item-price-desc").innerHTML = xmlhttp.responseText;
			}
		} else if (path === "registration" && purpose === "display") {
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				document.getElementById("hidden-registration-form").innerHTML = xmlhttp.responseText;
			}
		}
	}
	if (path === "edit-event") {  
		// Passes Value (Event ID) to external to generate populated event form
		xmlhttp.open("GET","/backend/registration/get-event-info.php?q="+value,true);
	} else if (path === "price-dropdown" && purpose === "purchase") {
		// Passes Event ID and Purpose to create Price Dropdown.  Purpose determines dropdown onchange
		// function to Purchase or Edit
		var package = value + "&purpose=" + purpose;
		xmlhttp.open("GET","/backend/registration/price-dropdown.php?q="+package,true);
	} else if (path === "price-dropdown" && purpose === "edit") {
		// Passes Event ID and Purpose to create Price Dropdown.  Purpose determines dropdown onchange
		// function to Purchase or Edit
		var package = value + "&purpose=" + purpose;
		xmlhttp.open("GET","/scripts/event-system/create-edit-event/edit-price-dropdown.php?q="+package,true);
	} else if (path === "edit-price" && purpose === "edit") {
		// From previous submission, sets up Price Dropdown with Editing onchange
		xmlhttp.open("GET","/backend/registration/get-price-info.php?q="+value,true);
	} else if (path === "edit-price" && purpose === "purchase") {
		// From previous submission, sets up Price Dropdown with Purchasing onchange
		xmlhttp.open("GET","/backend/registration/get-purchase-info.php?q="+value,true);
	} else if (path === "registration" && purpose === "display") {
		// From previous submission, sets up event registration
		xmlhttp.open("GET","/backend/registration/registration-simple.php?event_id="+value,true);
	}
	xmlhttp.send();
}

// Creates onload event listener to start the javascipt on the page when it loads.
if (window.addEventListener) {
   window.addEventListener("load", onLoad, false);
   revealHiddenForms;
} else if (window.attachEvent) {
   window.attachEvent("onload", onLoad);
   revealHiddenForms;
} else {
   document.addEventListener("load", onLoad, false);
   revealHiddenForms;
}

// Function used in the Admin Payment page to retrieve Payment Information based on user dropdown
// Edits the edit-payment-info DIV ID with proper information
function getPaymentInfo (value) {
	var ajax = new XMLHttpRequest();
	ajax.onreadystatechange = function() {
		if (ajax.readyState === 4 && ajax.status === 200) {
			document.getElementById("edit-payment-info").innerHTML = ajax.responseText;
		}
	};
	ajax.open("GET", "../../backend/registration/get-payment-info.php?q="+value, true);
	ajax.send(null);
}


//Functionality for Create/Edit Form
$(function(){
	$('input[type=radio][name=create-edit-radio]').change(function() {    
		if($(this).val() == "create-event-choice"){   
			$('#create-event-form').addClass('reveal-if-selected');
			$("#create-price-form").removeClass('reveal-if-selected');
			$("#edit-event-form").removeClass('reveal-if-selected');
			$("#edit-price-form").removeClass('reveal-if-selected');
		}
		else if($(this).val() == "create-price-choice"){   
			$('#create-event-form').removeClass('reveal-if-selected');
			$("#create-price-form").addClass('reveal-if-selected');
			$("#edit-event-form").removeClass('reveal-if-selected');
			$("#edit-price-form").removeClass('reveal-if-selected');
		}
		else if($(this).val() == "edit-event-choice"){   
			$('#create-event-form').removeClass('reveal-if-selected');
			$("#create-price-form").removeClass('reveal-if-selected');
			$("#edit-event-form").addClass('reveal-if-selected');
			$("#edit-price-form").removeClass('reveal-if-selected');
		}
		else if($(this).val() == "edit-price-choice"){   
			$('#create-event-form').removeClass('reveal-if-selected');
			$("#create-price-form").removeClass('reveal-if-selected');
			$("#edit-event-form").removeClass('reveal-if-selected');
			$("#edit-price-form").addClass('reveal-if-selected');
		}
	});
});