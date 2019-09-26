Feature: Day segment styles in Month View

Day segments for dates that have passed have a different style than today's day segment or upcoming day segments.

Background:
    Given that I am a Visitor on a site with TEC
    Given the site timezone is America/Los_Angeles
    Given Views V2 is active
    Given "Week Starts On" is set to Sunday under the WordPress General Settings

Scenario: Day segments styles for days in the previous month
    Given I am on Month View
    When I view a month that starts on a Thursday
    Then I should see four visible day segments from the previous month styled as past days

Scenario: Day segments styles for past dates in the current month
    Given I am on Month View
    When I view the current month with today's date
    Then I should see all visible day segments before today's date styled as past days

Scenario: Day segments styles for upcoming dates
    Given I am on Month View
    When I view the current month with today's date
    Then I should see all visible day segments after today's date styled as upcoming days

Scenario: Day segments style for today's date
    Given I am on Month View
    When I view the current month with today's date
    Then I should see the day segment for today styled uniquely from past and upcoming days

Scenario: Past events that ended on today's date are not styled differently from other events happening today
    Given I am on Month View
    And "Display up to X single-day events per day in Month View" is set to 3
    And there are the following events taking place today:
        | event name | start time | duration |
        | Rowboat    | now -2 hrs | 1 hr     |
        | Skiff      | now -1 hr  | 2 hrs    |
        | Scow       | now +1 hr  | 2 hrs    |
    When I view the current month with today's date
    Then I should see three events "Rowboat," "Skiff", and "Scow" listed on the today's day segment with similar styles

Scenario: Day segment styles for days in the next month
    Given I am on Month View
    When I view a month that ends on a Monday
    Then I should see five visible day segments from the next month styled as upcoming days