Feature: single event navigation links

A Site Visitor can navigate to the single event preceeding or following the one they are viewing.

Background:
    Given that I am a Visitor on a site with TEC
    Given the site timezone is America/Los_Angeles
    Given that there are 11 events on this site
        | event name        | start date-time		    | duration  	    |
        | strawberry S-T-P  | today -3 days -2 hours    | 4 hours	        |
        | banana M-A-P      | today -2 days all day     | 1 day		        |
        | eggplant M-A-N    | today -2 days all day     | 4 days	        |
        | apple S-T-P       | today -2 days -2 hours    | 1 hour            |
        | cabbage S-T-P     | today -2 days -2 hours    | 4 hours	        |
        | lettuce M-T-N     | today -2 days -2 hours    | 4 days 4 hours    |
        | grape	S-A-N	    | today all day     	    | all day	        |
        | melon S-T-N	    | today -2 hours	        | 4 hours	        |
        | turnip S-A-U	    | today +2 days all day	    | all day   	    |
        | pepper M-A-U      | today +2 days all day	    | 2 days    	    |
        | carrot S-T-U      | today +2 days +2 hours    | 4 hours	        |

# technically the above requires that it is between 2:01am and 9:59pm PST
# coded letters in the event name denote whether that event is multi-day or single-day, timed or all day, and previous or now or upcoming.

Scenario Outline: navigating to proceeding event
    Given that I am viewing the single event page for event <home event>
    When I use the event navigation to view the preceeding event
    Then I should see the single event page for event <preceeding event>

Examples:
    | home event        | preceeding event  |
    | strawberry S-T-P  | none (no link)    |               
    | banana M-A-P      | strawberry S-T-P  |
    | eggplant M-A-N    | banana M-A-P      |
    | apple S-T-P       | eggplant M-A-N    |
    | cabbage S-T-P     | apple S-T-P       |
    | lettuce M-T-N     | cabbage S-T-P     |
    | melon S-T-N       | grape S-A-N       |
    | grape	S-A-N       | lettuce M-T-N     |
    | carrot S-T-U      | pepper M-A-U      |
    | turnip S-A-U      | melon S-T-N       |
    | pepper M-A-U      | turnip S-A-U      |

Scenario Outline: navigating to following event
    Given that I am viewing the single event page for event <home event>
    When I use the event navigation to view the following event
    Then I should see the single event page for event <following event>

Examples:
    | home event        | following event   |
    | strawberry S-T-P  | banana M-A-P      |         
    | banana M-A-P      | eggplant M-A-N    |
    | eggplant M-A-N    | apple S-T-P       |
    | apple S-T-P       | cabbage S-T-P     |
    | cabbage S-T-P     | lettuce M-T-N     |
    | lettuce M-T-N     | grape S-A-N       |
    | melon S-T-N       | turnip S-A-U      |
    | grape	S-A-N       | melon S-T-N       |
    | carrot S-T-U      | none (no link)    |
    | turnip S-A-U      | pepper M-A-U      |
    | pepper M-A-U      | carrot S-T-U      |
