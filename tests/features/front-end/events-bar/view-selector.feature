Feature: Events Bar view selector

A Site Visitor should be able to switch from any calendar view to any other enabled view so that they can see the events in a display that is most relevant for them.

#This feature describes the newly redesigned Month View and does not reflect the current Month View functionality.

Background:
    Given that I am a Visitor on a site with TEC
    Given "Number of events to show per page" is set to 10
    Given "Display up to X single-day events per day in Month View" is set to 3

Scenario: View selector displays with 2+ enabled views
    Given that 2 or more views are enabled under Events --> Settings --> Display
    When I view the event calendar
    Then I should see an option to switch to any of the other enabled views

Scenario Outline: Switching from Month View to List View
    Given I am on Month View
    And the Datepicker is set to <selection>
    When I switch to List View
    Then I should see up to 10 events in ascending chronological order starting with events that start on or end after <event datetime>
    And the Datepicker should show <display> selected

    Examples: 
        | selection     | event datetime        | display                   |
        | default       | today & current time  | no selection (default)    |
        | January       | January 1 12:00am     | January 1                 |
        | June          | June 1 12:00am        | June 1                    |
        | November      | November 1 12:00am    | November 1                |
        | February      | February 1 12:00am    | February 1                |

Scenario: Switching from Month View to Day View
    Given I am on Month View
    And the Datepicker is set to <selection>
    When I switch to Day View
    Then I should see all events that happen on <event date> listed in ascending chronological order
    And the Datepicker should show <display> selected

    Examples: 
        | selection     | event date    | display                   |
        | default       | today         | today's date (default)    |
        | January       | January 1     | January 1                 |
        | June          | June 1        | June 1                    |
        | November      | November 1    | November 1                |
        | February      | February 1    | February 1                |

Scenario: Switching from List View to Month View
    Given I am on List View
    And the Datepicker is set to <selection>
    When I switch to Month View
    Then I should see the calendar grid for <month>
    And I should see up to 3 single-day events in each day segment for that month grid
    And I should see all sticky events for that month grid
    And I should see all multi-day events for that month grid
    And the Datepicker should show <display> selected

    Examples: 
        | selection     | month         | display                   |
        | default       | current month | current month (default)   |
        | January 14    | January       | January                   |
        | June 6        | June          | June                      |
        | November 30   | November      | November                  |
        | February 29   | February      | February                  |

Scenario: Switching from Day View to Month View
    Given I am on Day View
    And the Datepicker is set to <selection>
    When I switch to Month View
    Then I should see the calendar grid for <month>
    And I should see up to 3 single-day events in each day segment for that month grid
    And I should see all sticky events for that month grid
    And I should see all multi-day events for that month grid
    And the Datepicker should show <display> selected

    Examples: 
        | selection     | month         | display                   |
        | default       | current month | current month (default)   |
        | January 14    | January       | January                   |
        | June 6        | June          | June                      |
        | November 30   | November      | November                  |
        | February 29   | February      | February                  |

Scenario: Switching views with an active keyword search
    Given I am on List View
    And the Datepicker is set to July 10
    And I have performed a search for the keyword "cabbage"
    And there is one event that includes "cabbage" in the title or description every day in July
    When I switch to Month View
    Then I should see the month view grid for July
    And I should see one event in each day segment for the month of July (31 total events)
    And the keyword field should still show the keyword "cabbage"

# Switching with an active Location Search should be covered in the equivalent ECP file
# Switching to and from Week View should be covered in the equivalent ECP file

