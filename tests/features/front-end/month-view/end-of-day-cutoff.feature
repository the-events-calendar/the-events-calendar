Feature: End of Day Cutoff setting in Month View

Background:
    Given I am a Site Visitor on a site with TEC
    Given Month View is enabled
    Given the "End of Day Cutoff" is set to 2:00am
    Given "Display up to X single-day events per day in Month View" is set to 3 (default)
    Given the site timezone is PST

Scenario: Event within Event of Day Cutoff
    Given there is an event that starts on July 10 at 6:00pm and ends on July 11 at 1:00am
    And there are no other events on July 10 
    When I view Month View for July
    Then I should see the event listed as a single-day event on July 10

Scenario: Event outside of Event of Day Cutoff
    Given there is an event that starts on September 8 at 6:00pm and ends on September 9 at 1:00am
    And there are no other events on September 8 or 9 
    When I view Month View for September
    Then I should see the event listed as a multi-day event span on September 8 and 9.