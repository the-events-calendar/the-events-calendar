Feature: color sorting events in Week View
	In order to show events in ascending chromatic scale in the context of a day
	As an admin user
	I need to be able to assign each event a color

	Background:
		Given I am logged in as administrator
		And I have 3 events on each day of the 4/8 to 4/14 week at times:
			| title | start_time | end_time |
			| One   | 9am        | 11am     |
			| Two   | 12pm       | 1pm      |
			| Three | 4pm        | 6pm      |

	Scenario: no events have any color assigned
		Given no event has been assigned a color
		When I go on the 4/8 Week View
		Then I should see events show in this order on each day:
			| title | position |
			| One   | 1        |
			| Two   | 2        |
			| Three | 3        |

	Scenario: all events have a color assigned
		Given I have assigned colors to the events:
			| title | color |
			| One   | red   |
			| Two   | Blue  |
			| Three | Green |
		When I go on the 4/8 Week View
		Then I should see events show in this order on each day:
			| title | position |
			| One   | 3        |
			| Two   | 1        |
			| Three | 2        |

