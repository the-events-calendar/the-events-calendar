# BDD testing for The Event Calendar

This suite is dedicated to feature-level testing.
It should contains only `.feature` files that respect the [Gherkin Syntax](http://docs.behat.org/en/latest/guides.html) and format.

## Adding features to this suite
While you can add file manually, if you can use the terminal, then you should use [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.") built-in `generate:feature` command to generate a new feature file.

### Creating feature files manually
You can copy and paste the template below in a new `.feature` file and start writing.


```gherkin
Feature: a feature we want test

    In order to do something
    As a user of The Events Calendar
    I need to be able to do this and that

    Scenario:
        Given pre-condition one
        And pre-condition two
        When I do something
        And something else
        Then I should see post-condition one
        And I should see post-condition two
```

### Creating feature files using Codeception command
First of all pull the required dependencies by opening a terminal window in the plugin root folder and running the [Composer](https://getcomposer.org/) command:

```shell
composer install
```

You should now be able to run the `codecept` command from the plugin root folder:

```shell
vendor/bin/codecept generate:feature features front-end/widget/week/color-sorting
```

This will produce the empty `tests/features/front-end/widget/week/color-sorting.feature` file.
You can now edit it to your heart contents.
