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

Scenario: List View order of events with different time zones
    Given "Time zone mode" is set to "Use manual time zones for each event"
    And I have published these single-day events on the date of today +7 days
        | event name | time/duration  | time zone           |
        | arugula    | 10:00am-2:00pm | America/Los_Angeles |
        | spinach    | 10:00am-2:00pm | America/Denver      |
        | leek       | 10:00am-2:00pm | America/Chicago     |
        | potato     | 10:00am-2:00pm | America/New York    |
        | okra       | 10:30am-2:00pm | America/Los_Angeles |
        | celery     | 11:00am-2:00pm | America/New York    |
        | asparagus  | 11:30am-2:00pm | America/Los_Angeles |
    When I select today +7 days in the datepicker
    Then I should see the events listed in the following order: "potato", "leek", "celery", "spinach", "arugula", "okra", "asparagus"

    Then I should see the events listed in the following order: "potato", "leek", "spinach", "arugula", "okra", "celery", "asparagus"

# As per the above, we list events by the start time, even if different time zones mean that technically the events are not listed in the order in which they happen. This is a deliberate choice in order to avoid the intense complications that a true chronological listing with multiple timezones would create in time-based views (Month, Day, Week). If a user has events in multiple time zones, they should be encouraged to use something like Filter Bar to help their visitors see a list of events within only the relevant time zone(s).

# At this time, events may also be listed based on publish date. However, time zone relationships should supercede publish date in the hierarchy of how to define event order.
