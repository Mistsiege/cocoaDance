(function() {

	var app = angular.module("volunteerSelection", ['labDateFilter']);

	var VolunteerController = function($scope, $http, $sce) {
		
		$scope.eventID = "";
		$scope.volunteeringTab = "";	

		$scope.getVolunteeringForm = function(eventID){
			$scope.eventID = eventID;
			$http.get("http://www.lindyandblues.com/backend/payments/display-volunteer-form.php?registration_id=" + $scope.registrationID)
				.then(onFormComplete, onError);
		}
		var onFormComplete = function(response) {
			$scope.volunteerForm = $sce.trustAsHtml(response.data);
		}
		var onError = function(reason) {
			$scope.error = "Could not fetch the data.";
		};

		$scope.removeVolunteer = function(eventID){
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
	};

	angular.module('labDateFilter', []).filter('timestampToISO', function() {
		return function(input) {
			input = new Date(input).toISOString();
			return input;
		};
	});
	
	app.controller("VolunteerController", ["$scope", "$http", "$sce", VolunteerController]);
}());