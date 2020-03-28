Feature: layout and visual elements of an event in List View

# This feature spec is specifically for the V2 List View

Background:
    Given I am a Visitor on a site with TEC
    Given List View is enabled under Events --> Settings --> Display
    Given the site timezone is America/Los_Angeles
    Given "Number of events to show per page" is set to 10
	Given Views V2 is active

Scenario Outline: Venue display in List View
    Given that this site has the following public, published upcoming events with associated venues:
        | event        | venue name   | venue address       | venue city | venue state/province | venue zip |
        | Cinnamon     | Spice World  |                     |            |                      |           |
        | Thyme        | Spice Palace | 3414 NW Maxine Cir. | Corvallis  | OR                   | 97330     |
        | Basil        | Spicington's |                     | Portland   | OR                   |           |
        | Black Pepper | Club Spice   | 830 E Burnside St   | Portland   |                      | 97214     |
        | Cayenne      |              | 120 NW 10th Ave     | Portland   | OR                   | 97209     |
        | Oregano      |              |                     |            | BC                   | V5K 2M5   |
    And I am viewing upcoming events in List View
    When I see the listing for <event>
    Then I should see the following venue information listed under the event title: <venue display>
    And the venue information should wrap if it exceeds the space

    Examples
        | event        | venue display              |
        | Cinnamon     | Spice World                |
        | Thyme        | Spice Palace Corvallis, OR |
        | Basil        | Spincington's Portland, OR |
        | Black Pepper | Club Spice Portland        |
        | Cayenne      | Portland, OR               |
        | Oregano      | BC                         |

# While the defined default is to only show the venue title, city, and state/province, users should be able to customize their list view template to show more or less venue information (e.g. address, country)

Scenario: Automatic event excerpt (30 words) in List View
    Given the next public, published upcoming event has the following event description: "Pie apple pie cookie chocolate gingerbread jujubes gingerbread cotton candy chocolate. Chocolate bar dragée lollipop caramels. Sweet tootsie roll gummi bears fruitcake jelly beans caramels candy gummi bears. Lollipop jelly beans jelly-o brownie pudding macaroon liquorice bear claw dessert. Cupcake ice cream marshmallow bear claw tart dragée gummi bears cake. Topping sugar plum topping tiramisu jujubes. Caramels ice cream chupa chups apple pie. Muffin danish gingerbread fruitcake. Cookie candy apple pie cupcake cupcake liquorice. Cheesecake sweet roll gummies apple pie."
    And the admin has not written anything in the event excerpt field
    When I view the List View for upcoming events
    Then I should see the next upcoming event displayed with the following excerpt: "Pie apple pie cookie chocolate gingerbread jujubes gingerbread cotton candy chocolate. Chocolate bar dragée lollipop caramels. Sweet tootsie roll gummi bears fruitcake jelly beans caramels candy gummi bears. Lollipop jelly ..."

# We will define the default auto-excerpt size with the goal of hitting about three lines of text. However, the user should be able to customize their list view template to change the auto-excerpt size.

Scenario: Manual event excerpt in List View
    Given the next public, published upcoming event has the following event description: "Pie apple pie cookie chocolate gingerbread jujubes gingerbread cotton candy chocolate. Chocolate bar dragée lollipop caramels. Sweet tootsie roll gummi bears fruitcake jelly beans caramels candy gummi bears. Lollipop jelly beans jelly-o brownie pudding macaroon liquorice bear claw dessert. Cupcake ice cream marshmallow bear claw tart dragée gummi bears cake. Topping sugar plum topping tiramisu jujubes. Caramels ice cream chupa chups apple pie. Muffin danish gingerbread fruitcake. Cookie candy apple pie cupcake cupcake liquorice. Cheesecake sweet roll gummies apple pie."
    And the event has the following event excerpt as added by the admin: "This event is gonna be super awesome! Come build your own life-size gingerbread house make a snow angel in a pile of donuts. Get your ticket today and join us for the best most sugary event in the WHOLE WORLD."
    When I view the List View for upcoming events
    Then I should see the next upcoming event displayed with the following excerpt: "This event is gonna be super awesome! Come build your own life-size gingerbread house make a snow angel in a pile of donuts. Get your ticket today and join us for the best most sugary event in the WHOLE WORLD."

Scenario: Date marker number on current & upcoming multi-day events
    Given there is an all-day multi-day event called "Coriander" that runs from the 1st of next month to the 5th of next month
    When I view the List View for <date>
    Then the date marker for "Coriander" should be the number <marker>

    Examples:
        | date                      | marker |
        | today                     | 1      |
        | the 1st day of next month | 1      |
        | the 3rd day of next month | 3      |
        | the 5th day of next month | 5      |

Scenario: Date marker on past multi-day events
    Given there is an all-day multi-day event called "Saffron" that ran from the 1st of last month to the 5th of last month
    When I view the List View for <date>
    Then the date marker for "Saffron" should be the number <marker>

    Examples:
        | date                      | marker |
        | the 1st day of last month | 1      |
        | the 3rd day of last month | 3      |
        | the 5th day of last month | 5      |