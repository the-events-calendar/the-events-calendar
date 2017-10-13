# The Events Calendar test setup

## Do your homework
This is a brief and quick guide that's covering the bare essentials needed to set up the tests on your local plugin copy.
Please refer to [Codeception](http://codeception.com/docs) and [WP Browser](https://github.com/lucatume/wp-browser) documentation for any issue that's not TEC related.

## Set up
After cloning the TEX repository on your local machine change directory to the plugin root folder and pull in any needed dependency using [Composer](https://getcomposer.org/):

	composer update

when Composer finished the update process (might take a while) set up your own [Codeception](http://codeception.com/) installation creating a `codeception.yml` file with the following contents:

```yaml
params:
    - .env.local
```

Codeception is configured to use [dynamic configuration](http://codeception.com/docs/06-ModulesAndHelpers#Dynamic-Configuration-With-Parameters) and here we tell it that we want, locally, to load the configuration parameters from a file called `.env.local` found in the plugin root folder.  
Create the `.env.local` file copying the `.env` file and modify each entry to suite your **local** configuration; look up the suite configuration files (`wpunit.suite.dist.yml`, `integration.suite.dist.yml` and so on) to understand how those parameters will be used.  
**Beware**: testing will wipe and burn the databases, if you have cherished data on them it will be gone forever.
	
## Running the tests
You can use the command:

```
vendor/bin/codecept run <suite>
```

E.g. to run the `wpunit` suite:

```
vendor/bin/codecept run wpunit
```

**Do not run all the tests at the same time** using `codecpt run`, always run each suite separately; due to WordPress extensive use of constants and globals running all the tests at the same time will cause failures and false negatives/positives.  
Should you want to run a single test case (a class) you can use:

```bash
vendor/bin/codecept run tests/wpunit/Tribe/Some/Path/SomeTest.php
```

Furthermore, should you want to run a single test method (a method of a test case class) you can use:

```bash
vendor/bin/codecept run tests/wpunit/Tribe/Some/Path/SomeTest.php:test_something_does_stuff
```

Failing tests are ok in set up terms: the system works. Errors should be reported.
