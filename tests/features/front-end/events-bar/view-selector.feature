Feature: Events Bar view selector

A Site Visitor should be able to switch from any calendar view to any other enabled view so that they can see the events in a display that is most relevant for them.

Background:
    Given that "Disable the Event Search Bar" is not enabled
    Given that I am a Visitor on a site with TEC
    Given that the Datepicker is on default (today’s date/this month & current time)

Scenario: Single view enabled
    Given context
    When event
    Then outcome

Scenario: Switching from Month View to List View
    Given context
    When event
    Then outcome

Scenario: Switching from Month View to Day View
    Given context
    When event
    Then outcome

Scenario: Switching from List View to Month View
    Given context
    When event
    Then outcome

Scenario: Switching from Day View to Month View
    Given context
    When event
    Then outcome

Scenario: Switching views with an active keyword search
    Given context
    When event
    Then outcome

# Switching with an active Location Search should be covered in the equivalent ECP file
# Switching to and from Week View should be covered in the equivalent ECP file

