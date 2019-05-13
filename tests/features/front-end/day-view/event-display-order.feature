Feature: display and order of events in Day View

Background:
    Given I am a Visitor on a site with TEC
    Given the site timezone is America/Los_Angeles
    Given there are 13 events on this site
        | event name        | start date-time		    | duration  	    |
        | strawberry S-T-P  | today -3 days -2 hours    | 4 hours	        |
        | banana M-A-P      | today -2 days all day     | 1 day		        |
        | eggplant M-A-N    | today -2 days all day     | 4 days	        |
        | apple S-T-P       | today -2 days -2 hours    | 1 hour            |
        | cabbage S-T-P     | today -2 days -2 hours    | 4 hours	        |
        | lettuce M-T-N     | today -2 days -2 hours    | 4 days 4 hours    |
        | grape	S-A-N	    | today all day     	    | all day	        |
        | parsnip S-T-P     | today -4 hours            | 2 hours           |
        | lemon S-T-N       | today -4 hours            | 6 hours           |
        | melon S-T-N	    | today -2 hours	        | 4 hours	        |
        | turnip S-A-U	    | today +2 days all day	    | all day   	    |
        | pepper M-A-U      | today +2 days all day	    | 2 days    	    |
        | carrot S-T-U      | today +2 days +2 hours    | 4 hours	        |

# technically the above requires that it is between 2:01am and 9:59pm PST
# coded letters in the event name denote whether that event is multiday or single day, timed or all day, and previous or now or upcoming.

Scenario: Day View today
    Given the Datepicker is on default (today, current time in site timezone)
    When I view the Day View
    Then I should see these 2 events in this order displayed under "All Day": "eggplant M-A-N", "grape S-A-N"
    And I should see "lettuce M-T-N" displayed under "Ongoing"
    And I should see these 2 events in this order displayed under a heading of now -4 hours: "parsnip S-T-P", "lemon S-T-N"
    And I should see "melon S-T-N" displayed under a heading of now -2 hours

#If two events start at the same time, the one ending sooner should be listed first (regardless of Publish date)
# If two events start at the same time and end at the same time, the one published first is listed twice.

Scenario: Day View on future date A
    Given I am on Day View
    When I select a date 1 day in the future
    Then I should see "eggplant M-A-N" displayed under "All Day"
    And I should see "lettuce M-T-N" displayed under "Ongoing"

Scenario: Day View on future date B
    Given I am on Day View
    When I select a date 2 days in the future
    Then I should see these events in this order displayed under "All Day": "pepper M-A-U", "turnip"
    And I should see "lettuce M-T-N" displayed under "Ongoing"
    And I should see "carrot S-T-U" displayed under under a heading of the current time +2 hours

Scenario: Day View on past date A
    Given I am on Day View
    When I select a date 2 days in the past
    Then I should see these events in this order displayed under "All Day": "banana M-A-P", "eggplant M-A-N"
    And I should see these events in this order displayed under a heading of the current time -2 hours: "apple S-T-P", "cabbage S-T-P", "lettuce M-T-N" 

Scenario: Day View on past date B
    Given I am on Day View
    When I select a date 3 days in the past
    Then I should see "strawberry S-T-P" displayed under a heading of current time -2 hours