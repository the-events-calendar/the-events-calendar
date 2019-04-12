Feature: Search by date, keyword, and/or location

A Visitor can search by date, keyword, and/or location from the Tribe Events Bar located at the top of a frontend view.

Background: 
    Given that "Disable the Event Search Bar" is not enabled
    Given that I am a Visitor on a site with TEC
    Given that the Datepicker is on default (today’s date/this month/this week & current time)
    Given the site timezone is PST

Scenario: Datepicker search in List View
Given that I am on List View
And "Number of events to show per page" is set to 10
And there are 12 upcoming events
	| number of events	| start date		| duration	|
	| 2			        | today + 3 days 	| 3 days	|
	| 2			        | today + 4 days 	| 1 day		|
	| 4			        | today + 1 week	| 2 hours	|
	| 4			        | today + 2 weeks	| 4 hours	|
When I select a date 4 days in the future in the Datepicker
Then I should see 10 events listed in ascending chronological order by start date-time 
And I should see both events that start on the selected date 
And I should see both multi-day events that end on or after the selected date  