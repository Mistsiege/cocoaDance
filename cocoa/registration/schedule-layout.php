<?php echo '<div class="schedule-container">
				<div ng-repeat="day in scheduleDays" class="schedule-table">
					<div class="schedule-header">
						<div class="schedule-day">{{day.dow}} Schedule - Time/Venue</div>
						<div class="schedule-venues" ng-repeat="venue in scheduleVenues | filter:day.real_date" title="{{venue.name}}">{{venue.abbreviation}}</div>
					</div> <!-- Schedule Header -->
					<div class="schedule-body">
						<div class="schedule-instance" ng-repeat="instance in schedule | filter:day.real_date">
							<div class="schedule-row">
								<div>{{instance.s_time | timestampToISO | date: "h:mma"}} - {{instance.e_time | timestampToISO | date: "h:mma"}}</div>
								<div class="schedule-block" ng-repeat="venue in scheduleVenues | filter:day.real_date" title="{{instance.description}}"><div class="schedule-blank" ng-show="{{instance.venue_id != venue.venue_id}}">-</div><div class="schedule-class" ng-show="{{instance.venue_id === venue.venue_id}}"><div class="schedule-class-title">{{instance.title}}</div><div class="schedule-class-teacher">{{instance.landmark}}</div><div class="schedule-class-difficulty" ng-show="{{instance.difficulty}}">[{{instance.difficulty}}]</div><div class="schedule-class-price" ng-show="{{instance.price_flag}}">Price \${{instance.price}}</div></div></div>
							</div> <!-- Schedule Row -->
							<div class="schedule-description">
								<div>blah</div>
							</div> <!-- Schedule Description -->
						</div> <!-- Schedule Instance -->
					</div> <!-- Schedule Body -->
					<div class="schedule-excerpt">
						<p ng-repeat="instance in schedule | filter:day.real_date" ng-show="{{instance.description}}"><span class="schedule-class-title">{{instance.title}}</span> <span class="schedule-class-teacher" ng-show="{{instance.landmark}}">taught by {{instance.landmark}}</span> <span class="schedule-class-difficulty" ng-show="{{instance.difficulty}}">[{{instance.difficulty}}]</span><br>
							{{instance.description}}</p>
					</div> <!-- Schedule Exercpt -->
				</div> <!-- Schedule Table -->
			</div> <!-- Schedule Container -->';
			
			
?>