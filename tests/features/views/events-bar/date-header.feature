Feature: Date Header under the Events Bar

    The Date Header displays the date or date range of events listed on that page, and changes if the Site Visitor navigates between event pages.

Background:
    Given I am a Visitor on a site with TEC
    Given all views are active
    Given the site timezone is America/Los_Angeles
    Given Views V2 is active
    Given "Date with year" is set to "F j, Y"
    Given "Month and year format" is set to "F Y"
    Given "Date without year" is set to "F j"
    Given "Compact date format" is set to "1/15/2019"
    Given "Number of events to show per page" is set to 10

Scenario Outline: Date header format in List View (desktop) for a date range
    Given the Datepicker is set to September 1, 2025
    And today's date is on or before August 30, 2025
    And there are 10 public, published events on this site
    And the first event starts on <start date>
    And the last event ends on <end date> of this year
    When I look at List View in desktop size
    Then I should see the Date Header <header>

    Examples:
        | start date        | end date     | date header              |
        | September 1, 2025 | September 30 | September 1 - 30         |
        | September 1, 2025 | October 30   | September 1 - October 30 |
        | September 4, 2025 | September 30 | September 1 - 30         |
        | September 4, 2025 | October 30   | September 1 - October 30 |

Scenario: Date header format in List View (mobile) for a date range
    Given the Datepicker is set to September 1, 2025
    And today's date is on or before August 30, 2025
    And there are 10 public, published events on this site
    And the first event starts on September 1, 2025
    And the last event ends on <end date>
    When I look at List View in desktop size
    Then I should see the Date Header <header>

    Examples:
        | end date           | date header           |
        | September 30, 2025 | 9/1/2025 - 9/30/2025  |
        | October 30, 2025   | 9/1/2025 - 10/30/2025 |
        | February 2, 2026   | 9/1/2025 - 2/2/2026   |

Scenario Outline: Date header format in List View (desktop & mobile) for today
    Given today's date is April 10 2022
    And the Datepicker is on default (today’s date & current time)
    And there are 10 upcoming, public, published events on this site
    And the last event ends on <end date>
    When I look at List View in desktop size
    Then I should see the Date Heade <header>

    Examples:
        | end date        | date header            |
        | April 30 2022   | Now - April 30         |
        | May 30 2022     | Now - May 30           |
        | January 30 2023 | Now - January 30, 2022 |

Scenario: Date header format in Month View (desktop & mobile)
    Given the Datepicker is set to March 2019
    When I look at Month View
    Then I should see the Date Header "March 2019"

Scenario: Date header format in Day View (desktop) for a day outside the current year
    Given the Datepicker is set to March 5 2019
    When I look at Day View
    Then I should see the Date Header "Tuesday, March 5, 2019"

Scenario: Date header format in Day View (mobile) for a day outside the current year
    Given the Datepicker is set to March 5 2019
    When I look at Day View
    Then I should see the Date Header "March 5, 2019"

Scenario: Date header format in Day View (desktop) for today
    Given today's date is April 10 2022
    And the Datepicker is on default (today’s date & current time)
    When I look at Day View
    Then I should see the Date Header "Sunday, April 10"

Scenario: Date header format in Day View (mobile) for today
    Given today's date is April 10 2022
    And the Datepicker is on default (today’s date & current time)
    When I look at Day View
    Then I should see the Date Header "April 10"

