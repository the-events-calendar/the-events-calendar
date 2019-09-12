Feature: Today button in Month View

A Site Visitor can always click the Today button at the top of a view to return to a display which includes events on today's date.

# This feature is part of the Views Redesign project. There is not currently a Today button.

Background:
    Given that I am a Visitor on a site with TEC
    Given I am on Month View

Scenario Outline: Today Button in Month View
    Given today's date is <today> of this year
    And the datepicker is set to <month> of this year
    When I click the "Today" button
    Then I should see the Month View calendar for <result> of this year
    And the datepicker should be set to <new month> of this year

    Examples:
        | today       | month     | result   | new month |
        | June 15     | September | June     | June      |
        | February 29 | December  | February | February  |
        | November 21 | March     | November | November  |