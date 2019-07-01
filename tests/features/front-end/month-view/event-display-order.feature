Feature: Number and order of events in each day segment

The number and order of events shown in each day segment depends on the "Display up to X single-day events per day in Month View" setting and the number of multi-day, single-day, and/or sticky events happening that day.

# This feature spec is for the newly redesigned Month View and does not describe the current Month View functionality.

# This feature spec also assumes that as part of the views redesign, several pieces of functionality have changed:
#    1) Events set to "Sticky in Month View" will now always show in Month View regardless of chronology or the number of events on that day.
#    2) Any public, published multi-day event will show on Month View every day over its duration
#    3) The "Month view events per day" setting has been replaced by a new setting: "Display up to X single-day events per day in Month View"

Background:
    Given Month View is enabled under Events --> Settings --> Display
    Given I am a Visitor on a site with TEC
    Given that I am on Month View
    Given this site uses the newly redesigned views
    Given the site timezone is America/Los_Angeles

Scenario Outline: How "Display up to X single-day events per day in Month View" impacts day segment display
    Given this site has the following public, published events happening on July 12:
        | number of events | type                  |
        | 3                | multi-day             |
        | 8                | single-day non-sticky |
    When "Display up to X single-day events per day in Month View" is set to <setting>
    Then I should see <multi-day> multi-day event spans displayed on July 12
    And I should see <single-day> single-day events displayed on July 12

    Examples:
        | setting | multi-day | single-day |
        | -1      | 3         | 8          |
        | 3       | 3         | 0          |
        | 4       | 3         | 1          |
        | 5       | 3         | 2          |
        | 10      | 3         | 7          |

Scenario Outline: Events listed in a day segment with multi-day and single-day events
    Given this site has the following public, published events happening in July:
        | number of events | start date | duration |
        | 1                | July 5     | 7 days   |
        | 1                | July 6     | 5 days   |
        | 1                | July 7     | 2 days   |
        | 1                | July 8     | 2 days   |
        | 4                | July 4     | 2 hours  |
        | 1                | July 5     | 2 hours  |
        | 2                | July 6     | 2 hours  |
        | 2                | July 7     | 4 hours  |
        | 7                | July 8     | 4 hours  |
    And "Display up to X single-day events per day in Month View" is set to <setting>
    When I look at the day segment for <day>
    Then I should see <multi-day> multi-day event spans displayed
    And I should see <single-day> single-day events displayed

    Examples:
        | setting | day    | multi-day | single-day |
        | 3       | July 4 | 0         | 3          |
        | 3       | July 6 | 2         | 1          |
        | 3       | July 7 | 3         | 0          |
        | 3       | July 8 | 4         | 0          |
        | 5       | July 4 | 0         | 4          |
        | 5       | July 7 | 3         | 2          |
        | 5       | July 8 | 4         | 1          |
        | 10      | July 4 | 0         | 4          |
        | 10      | July 8 | 4         | 6          |
        | -1      | July 4 | 0         | 4          |
        | -1      | July 8 | 4         | 7          |

Scenario Outline: Display order with sticky single-day events
    Given this site has the following public, published events single-day events happening on July 10:
        | event name | start time | sticky? |
        | Crunch     | 8:30       | no      |
        | Twix       | 9:00       | yes     |
        | Payday     | 9:30       | no      |
        | Mars       | 10:00      | yes     |
        | Hersheys   | 10:30      | no      |
        | Mounds     | 11:00      | yes     |
        | Heath      | 11:30      | no      |
        | Snickers   | 12:00      | yes     |
    And "Display up to X single-day events per day in Month View" is set to <setting>
    When I look at the day segment for July 10
    Then I should see the following events listed in this order: <order>

    Examples:
        | setting | order                                                                         |
        | 3       | "Twix", "Mars", "Mounds", "Snickers"                                          |
        | 5       | "Crunch", "Twix", "Mars", "Mounds", "Snickers"                                |
        | -1      | "Crunch", "Twix", "PayDay", "Mars", "Hersheys", "Mounds", "Heath", "Snickers" |

    # The above would also be relevant to back-end/event-single/event-options/sticky.feature
    # Testing on current code suggests that sticky in month view currently shifts events out of chronological order even when there is enough space. The above negates that current behavior and requires chronological order within the displayed events.

Scenario Outline: Display order of single-day events (all day & timed)
    Given this site has the following public, published, single-day events happening on July 8:
        | event name | start time | end time |
        | Crunch     | all day    | n/a      |
        | Twix       | 8:00       | 10:00    |
        | Payday     | 8:00       | 12:00    |
        | Mars       | 9:00       | 11:00    |
        | Hersheys   | 9:00       | 13:00    |
        | Mounds     | 10:00      | 12:00    |
    And "Display up to X single-day events per day in Month View" is set to <setting>
    When I look at the day segment for July 8
    Then I should see the following events listed in this order: <order>

    Examples:
        | setting | order                                                    |
        | 3       | "Crunch", "Twix", "Payday"                               |
        | 5       | "Crunch", "Twix", "Payday", "Mars", "Hersheys"           |
        | -1      | "Crunch", "Twix", "Payday", "Mars", "Hersheys", "Mounds" |

Scenario: Display order of events with same start and end time
    Given this site has the following public, published, single-day events happening on July 20:
        | event name | start time | end time | publish date |
        | Crunch     | 8:00       | 10:00    | today -3     |
        | Twix       | 8:00       | 10:00    | today -1     |
        | Payday     | 8:00       | 10:00    | today        |
    And "Display up to X single-day events per day in Month View" is set to 3
    When I look at the day segment for July 20
    Then I should see the following events listed in this order: "Crunch", "Twix", "Payday"

Scenario Outline: Display order of multiple multi-day events
    Given the site has the following public, published, multi-day events happening in August:
        | event name | start datetime   | end datetime     |
        | Crunch     | August 1 all day | August 3         |
        | Twix       | August 1 9:00am  | August 4 10:00am |
        | Payday     | August 2 all day | August 4         |
        | Heath      | August 3 all day | August 7         |
        | Mars       | August 4 10:00am | August 7 4:00pm  |
        | Snickers   | August 5 2:00pm  | August 6 5:00pm  |
        | Hersheys   | August 6 8:00 am | August 8 6:00pm  |
        | Mounds     | August 6 8:00am | August 8 7:00pm  |
    When I look at the day segment for <date>
    Then I should see the following multi-day event spans in this order from the top: <order>

    Examples:
        | date     | order                                             |
        | August 1 | "Crunch", "Twix"                                  |
        | August 2 | "Crunch", "Twix", "Payday"                        |
        | August 3 | "Crunch", "Twix", "Payday", "Heath"               |
        | August 4 | "Mars", "Twix", "Payday", "Heath"                 |
        | August 5 | "Mars", "Snickers", "Heath"                       |
        | August 6 | "Mars", "Snickers", "Mounds", "Heath", "Hersheys" |
        | August 7 | "Mars", "Mounds", "Heath", "Hersheys"             |
        | August 8 | "Mounds", "Hersheys"                              |