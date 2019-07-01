Feature: End of Day Cutoff setting in Day View

Events that start on one day and end on a later day between 12am and the established "End of Day Cutoff" time should not be displayed on the Day View for their actual end date.

Background:
    Given I am a Site Visitor on a site with TEC
    Given Day View is enabled
    Given the "End of Day Cutoff" is set to 2:00am
    Given the site timezone is America/Los_Angeles

Scenario: Event within Event of Day Cutoff
    Given there is an event that starts today at 6:00pm and ends tomorrow at 1:00am 
    When I view tomorrow in Day View
    Then I should not see that event listed

Scenario: Event outside of Event of Day Cutoff
    Given there is an event that starts today at 6:00pm and ends tomorrow at 3:00am 
    When I view tomorrow in Day View
    Then I should see that event listed under the "Ongoing" header

Scenario: Display of event within Event of Day Cutoff
    Given there is an event that starts today at 6:00pm and ends tomorrow at 1:00am 
    When I view today in Day View
    Then I should see that event listed under the 6:00pm heading
    And the event listing should show the start date and time
    And the event listing should show the end time
    And the event listing should not show the end date
