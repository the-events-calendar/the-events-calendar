Feature: End of Day Cutoff setting in List View

Background:
    Given I am a Site Visitor on a site with TEC
    Given List View is enabled
    Given the "End of Day Cutoff" is set to 2:00am
    Given "Number of events to show per page" is set to 10
    Given the site timezone is PST

Scenario: Event within Event of Day Cutoff
    Given I am on List View
    And there is an event that starts today at 6:00pm and ends tomorrow at 1:00am 
    When I view select tomorrow's date in the Datepicker
    Then I should not see that event displayed

Scenario: Event outside of Event of Day Cutoff
    Given I am on List View
    And there is an event that starts today at 6:00pm and ends tomorrow at 1:00am 
    When I view select tomorrow's date in the Datepicker
    Then I should see that event displayed

Scenario: Display of event within Event of Day Cutoff
    Given there is an event that starts tomorrow at 6:00pm and ends the day after at 1:00am
    And the Datepicker is set to tomorrow's date
    When I view today in List View
    Then I should see that event listed
    And the event listing should show the start date and time
    And the event listing should show the end time
    And the event listing should not show the end date
