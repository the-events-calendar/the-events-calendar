Feature: order of events in List View

Background:
    Given I am a Visitor on a site with TEC
    Given List View is enabled under Events --> Settings --> Display
    Given the site timezone is America/Los_Angeles
    Given "Number of events to show per page" is set to 10
    Given there are 12 events on this site
        | event name       | start date-time        | duration       |
        | strawberry S-T-P | today -3 days -2 hours | 4 hours        |
        | banana M-A-P     | today -2 days all day  | 1 day          |
        | eggplant M-A-N   | today -2 days all day  | 4 days         |
        | apple S-T-P      | today -2 days -2 hours | 1 hour         |
        | cabbage S-T-P    | today -2 days -2 hours | 4 hours        |
        | lettuce M-T-N    | today -2 days -2 hours | 4 days 4 hours |
        | grape S-A-N      | today all day          | all day        |
        | parsnip S-T-P    | today -4 hours         | 2 hours        |
        | melon S-T-N      | today -2 hours         | 4 hours        |
        | turnip S-A-U     | today +2 days all day  | all day        |
        | pepper M-A-U     | today +2 days all day  | 2 days         |
        | carrot S-T-U     | today +2 days +2 hours | 4 hours        |

# technically the above requires that it is between 2:01am and 9:59pm PST
# coded letters in the event name denote whether that event is multiday or single day, timed or all day, and previous or now or upcoming.

Scenario: List View initial load order
    Given the Datepicker is on default (today, current time in site timezone)
    When I view the List View
    Then I should see 7 events in the following order: "eggplant M-A-N", "lettuce M-T-N", "grape S-A-N", "melon S-T-N", "turnip S-A-U", "pepper M-A-U", "carrot S-T-U".

Scenario: List View order with future date search
    Given I am on List View
    And "Disable the Event Search Bar" is not enabled
    When I select a date 1 day in the future in the Datepicker
    Then I should see 5 events in the following order: "eggplant M-A-N", "lettuce M-T-N", "turnip S-A-U", "pepper M-A-U", "carrot S-T-U".

Scenario: List View order with past date search
    Given I am on List View
    And "Disable the Event Search Bar" is not enabled
    When I select a date 2 days in the past in the Datepicker
    Then I should see 11 events in the following order: "banana M-A-P", "eggplant M-A-N", "apple S-T-P", "cabbage S-T-P", "lettuce M-T-N", "grape S-A-N", "parsnip S-T-P", "melon S-T-N", "turnip S-A-U", "pepper M-A-U", "carrot S-T-U".

Scenario: List View order for events with the same start and end datetime
    Given I create an additional event on this site
        | event name     | start date-time        | duration |
        | rutabaga S-T-U | today +2 days +2 hours | 4 hours  |
    When I load List View
    Then I should see 8 events in the following order: "eggplant M-A-N", "lettuce M-T-N", "grape S-A-N", "melon S-T-N", "turnip S-A-U", "pepper M-A-U", "carrot S-T-U", "rutabaga S-T-U".
    # events with the same start and end datetine should be ordered by publish date ascending from the first published event

Scenario: List View "Previous events" link
# maybe this belongs in list-view/navigation.feature

Scenario: List View "Next events" link
# maybe this belongs in list-view/navigation.feature