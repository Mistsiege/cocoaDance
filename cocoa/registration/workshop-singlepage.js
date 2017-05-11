(function() {

	var app = angular.module("workshopDisplay", ['labDateFilter']);

	var WorkshopController = function($scope, $http, $sce) {
		
		$scope.eventID = "";
		$scope.workshopTab = "workshop-about";
		
		$scope.getTeachers = function(eventID){
			$scope.eventID = eventID;
			$http.get("http://www.lindyandblues.com/backend/services/display-teachers.php?event_id=" + $scope.eventID)
				.then(onTeacherComplete, onError);
		}
		var onTeacherComplete = function(response) {
				$scope.teachers = $sce.trustAsHtml(response.data);
		}
		var onError = function(reason) {
			$scope.error = "Could not fetch the data.";
		};
	
		$scope.getRegistration = function(eventID){
			$scope.eventID = eventID;
			$http.get("http://www.lindyandblues.com/backend/services/display-prices.php?event_id=" + $scope.eventID)
				.then(onPricingComplete, onError);
			$http.get("http://www.lindyandblues.com/backend/registration/registration.php?event_id=" + $scope.eventID)
				.then(onRegistrationComplete, onError);
		}
		var onPricingComplete = function(response) {
				$scope.pricing = $sce.trustAsHtml(response.data);
		}
		var onRegistrationComplete = function(response) {
				$scope.registration = $sce.trustAsHtml(response.data);
		}
	
		$scope.getVenues = function(eventID){
			$scope.eventID = eventID;
			$http.get("http://www.lindyandblues.com/backend/services/display-venues.php?event_id=" + $scope.eventID)
				.then(onLocationComplete, onError);
		}
		var onLocationComplete = function(response) {
				$scope.venues = $sce.trustAsHtml(response.data);
		}
		
		$scope.getAttendees = function(eventID){
			$scope.eventID = eventID;
			$http.get("http://www.lindyandblues.com/backend/services/who-is-coming.php?event_id=" + $scope.eventID)
				.then(onAttendeesComplete, onError);
		}
		var onAttendeesComplete = function(response) {
				$scope.attendees = $sce.trustAsHtml(response.data);
		}

		// Code executed upon selecting the Schedule Radio Button on Workshop Single Page
		$scope.getSchedule = function(eventID){
			$scope.eventID = eventID;
			$http.get("http://www.lindyandblues.com/backend/services/display-schedule-days.php?event_id=" + $scope.eventID)
				.then(onSDaysComplete, onError);
		}
		var onSDaysComplete = function(response) {
			$scope.scheduleDays = response.data;
			$http.get("http://www.lindyandblues.com/backend/services/display-schedule-venues.php?event_id=" + $scope.eventID)
				.then(onSVenuesComplete, onError);
		}
		var onSVenuesComplete = function(response) {
			$scope.scheduleVenues = response.data;
			$http.get("http://www.lindyandblues.com/backend/services/display-schedule.php?event_id=" + $scope.eventID)
				.then(onScheduleComplete, onError);
		}
		var onScheduleComplete = function(response) {
			$scope.schedule = response.data;
		}
		

	};

	angular.module('labDateFilter', []).filter('timestampToISO', function() {
		return function(input) {
			input = new Date(input).toISOString();
			return input;
		};
	});
	
	app.controller("WorkshopController", ["$scope", "$http", "$sce", WorkshopController]);

}());