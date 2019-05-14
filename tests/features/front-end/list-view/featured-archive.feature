Feature: featured events archive in List View

    Featured archive is at site.com/events/featured or site.com/events/view/featured

Background:
    Given I am a Visitor on a site with TEC
    Given List View is enabled under Events --> Settings --> Display
    Given there are 30 upcoming events on this site
    Given that 8 upcoming events are Featured Events

Scenario: upcoming featured events in List View
    Given I am viewing the featured events archive
    And "Number of events to show per page" is set to 10
    When I view List View
    Then I should see 8 upcoming events listed in ascending chronological order
    And I should see the word "featured" in the page URL

Scenario: previous featured events in List View
    Given I am viewing the featured events archive
    And I am on List View
    And "Number of events to show per page" is set to 5
    When I click the "Next Events" link
    Then I should see 3 upcoming events listed in ascending chronological order
    And I should see the word "featured" in the page URL

# In the current functionality, there is no access to previous events from the List View featured archive