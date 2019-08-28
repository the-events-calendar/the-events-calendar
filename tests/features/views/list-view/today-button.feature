Feature: Today button in List View

A Site Visitor can always click the Today button at the top of a view to return to a display which includes events on today's date.

# This feature is part of the Views Redesign project. There is not currently a Today button.

Background:
    Given that I am a Visitor on a site with TEC
    Given I am on List View
    Given "Number of events to show per page" is set to 10
    Given the site timezone is America/Los_Angeles

Scenario Outline: Today Button in List View + datepicker
    Given today's date is <today> of this year
    And the datepicker is currently set to <datepicker> of this year
    And there are 5 single-day timed events scheduled on the 15th of every month this year
    And there are no other events on the calendar for this year
    When I click the "Today" button
    Then I should see all events from <months> listed in ascending chronological order
    And the datepicker should be set to <new date> of this year

    Examples:
        | today        | datepicker  | months                | new date     |
        | August 11    | December 4  | August and September  | August 11    |
        | February 29  | August 3    | March and April       | February 29  |
        | April 4      | July 15     | April and May         | April 4      |
        | September 15 | November 23 | September and October | September 15 |
        | October 8    | August 20   | October and November  | October 8    |

Scenario: events displayed when using the Today button in List View
    Given today's date is March 6 of this year
    And the datepicker is currently set to November 18 of this year
    And the following events are scheduled in March of this year:
        | event name | start date/time | duration |
        | Pigeon     | March 1 9:00am  | 8 days   |
        | Crow       | March 2 all day | 4 days   |
        | Hawk       | March 5 all day | 1 day    |
        | Chickadee  | March 5 all day | 2 days   |
        | Eagle      | March 5 9:00am  | 2 hours  |
        | Swallow    | March 6 all day | 1 day    |
        | Sparrow    | March 6 9:00am  | 2 hours  |
    When I click the "Today" button
    Then I should see up to 10 events including "Pigeon", "Crow", "Swallow", "Sparrow", "Hawk", and "Chickadee"
    And I should not see "Eagle"