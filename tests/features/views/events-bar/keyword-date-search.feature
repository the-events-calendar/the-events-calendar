Feature: Search by date, keyword, and/or location

    A Visitor can search by date, keyword, and/or location from the Tribe Events Bar located at the top of a frontend view.

Background:
    Given that "Disable the Event Search Bar" is not enabled
    Given that I am a Visitor on a site with TEC
    Given that the Datepicker is on default (today’s date/this month & current time)
    Given the site timezone is America/Los_Angeles

Scenario: Datepicker search in List View
    Given that I am on List View
    And "Number of events to show per page" is set to 10
    And there are 12 upcoming events
        | number of events | start date     | duration |
        | 2                | today +3 days  | 3 days   |
        | 2                | today +4 days  | 1 day    |
        | 4                | today +1 week  | 2 hours  |
        | 4                | today +2 weeks | 4 hours  |
    When I select a date 4 days in the future in the Datepicker
    Then I should see 10 events listed in ascending chronological order by start date-time
    And I should see both events that start on the selected date
    And I should see both multi-day events that end on or after the selected date

Scenario: Datepicker search in Month View
    Given that I am on Month View
    And "Month view events per day" is set to 3
    When I select November in the Datepicker
    Then I should see the calendar grid for November
    And up to 3 events should show in each day square
    And days with more than 3 events should display a "View All" link to Day View

Scenario: Keyword search in List View with results
	Given that I am on List View
	And "Number of events to show per page" is set to 10
	And 2 upcoming events have the word "cabbage" in the title or description
	When I search for the keyword "cabbage"
	Then I should see 2 upcoming events listed that include "cabbage" in the title or description
	And the listed events should display in ascending chronological order by start date-time

Scenario: Keyword search in List View with no results
	Given that I am on List View
	And "Number of events to show per page" is set to 10
	And no upcoming events have the word "cabbage" in the title or description
	When I search for the keyword "cabbage"
	Then I should see no event results
    And I should see a "There were no results found." message

Scenario Outline: Keyword search in Month View with results
	Given that I am on Month View
	And "Month view events per day" is set to <per day>
	And at least <cabbage events> events in the current month have the word "cabbage" in the title or description
	When I search for the keyword "cabbage"
	Then I should see up to <events displayed> events that include "cabbage" in the title or description listed per day in the grid
	And days with more than <per day> matching events should display only <per day> events and a "View All" link to Day View

	Examples:
        | per day        | cabbage events | events displayed |
        | 3 [default]    | 5              | 3                |
        | 5              | 5              | 5                |
        | 5              | 10             | 5                |
        | 10             | 10             | 10               |
        | -1 [unlimited] | 10             | 10               |

Scenario: Keyword search in Month View with no results
	Given that I am on Month View
	And no events in the current month have the word "cabbage" in the title or description
	When I search for the keyword "cabbage"
	Then I should see no events in the calendar grid
	And I should see a "There were no results found." message



