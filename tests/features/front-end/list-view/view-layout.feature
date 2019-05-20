Feature: layout and visual elements of an event in List View

Background:
    Given I am a Visitor on a site with TEC
    Given List View is enabled under Events --> Settings --> Display
    Given the site timezone is America/Los_Angeles
    Given "Number of events to show per page" is set to 10

Scenario: display of single-day timed event in List View

Scenario: display of single-day event with the same start and end time in List View
# This is a thing in the current views, but maybe not supported after VR?

Scenario: display of multi-day timed event in List View

Scenario: display of single-day all day event in List View

Scenario: display of multi-day all day event in List View

Scenario: display of featured event in List View

Scenario: display of an event with a venue in List View

Scenario: display of an event with a price in List View

Scenario: display of an event with a featured image in List View

Scenario: display of an event with a custom excerpt in List View

Scenario: display of an event with a long description in List View

Scenario: display of an event with "Show Google Map Link" enabled in List View

#See also Scenario: Display of event within Event of Day Cutoff in front-end/list-view/end-day-cutoff.feature