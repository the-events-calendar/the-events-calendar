Feature: layout and visual elements of an event in List View

Background:
    Given I am a Visitor on a site with TEC
    And List View is enabled under Events --> Settings --> Display
    And the site timezone is America/Los_Angeles
    And "Number of events to show per page" is set to 10

Scenario: display of single-day timed event in List View
    Given I have a single-day timed event
    Then I should see the event date as ["start date" "Date time separator" "start time" "Time range separator" "end time"]

Scenario: display of single-day event with the same start and end time in List View
# This is a thing in the current views, but maybe not supported after VR?
    Given I have a single-day event with "start time" the same as "end time"
    Then I should see the event date as ["start date" "Date time separator" "start time"]

Scenario: display of multi-day timed event in List View
    Given I have a multi-day timed event
    Then I should see the event date as ["start date" "Date time separator" "start time" "Time range separator" "end date" "Date time separator" "end time"]

Scenario: display of single-day all day event in List View
    Given I have a single-day all day event
    Then I should see the event date as ["start date"]

Scenario: display of multi-day all day event in List View
    Given I have a multi-day all day event
    Then I should see the event date as ["start date" "Time range separator" "end date"]

Scenario: display of featured event in List View
    Given I have a featured event with a featured image
    Then I should see the event with a background colour
    And I should see the featured image in a bigger size if it has one

Scenario: display of an event with a venue in List View
    Given I have an event with a venue
    And The venue has an address
    Then I should see the event showing the venue and address

Scenario: display of an event with a cost in List View
    Given I have an event with a cost
    Then I should see the event showing the cost
    And The cost should show the correct currency symbol in the correct currency position

Scenario: display of an event with a featured image in List View
    Given I have an event with a featured image
    Then I should see the event with the featured image in thumbnail size

Scenario: display of an event with a custom excerpt in List View
    Given I have an event with a custom excerpt
    Then I should see the event showing the custom excerpt instead of the auto-generated excerpt

Scenario: display of an event with a long description in List View
    Given I have an event with a long description
    Then I should see the auto-generated excerpt for the description and show "..." at the end

Scenario: display of an event with "Show Google Map Link" enabled in List View
    Given I have an event with "Show Google Map Link"
    And The event has a venue with an address
    Then I should see the "+ Google Map" link after the venue name and address

#See also Scenario: Display of event within Event of Day Cutoff in front-end/list-view/end-day-cutoff.feature