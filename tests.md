# Quick tests introduction

This is a brief and quick guide that's covering the bare essentials needed to set up the tests on your local plugin copy.
Please refer to [Codeception](http://codeception.com/docs) and [WP Browser](https://github.com/lucatume/wp-browser) documentation for any issue that's not TEC related.

## Set up
After cloning the TEC repository on your local machine change directory to the plugin root folder and pull in any needed dependency using [Composer](https://getcomposer.org/):

	composer install

Using `composer install` in place of `composer update` will ensure you are using working and known dependencies; only run `composer update` if you know what you are doing.  
When Composer finished the update process (might take a while) set up your own [Codeception](http://codeception.com/) installation to work in your local setup.  
Create a `codeception.yml` file in the plugin root folder with this content:

```yaml
params:
	- .env.local
```

Codeception will process configuration files in a cascading way, think of CSS, so the `codeception.dist.yml` file will be read first and whatever you set in `codeception.yml` will be applied on top of it.  
The only override we do here is telling Codeception that it should read the modules settings not from the `.env` file, that is configured to run the tests on Travis CI, but to read them from a `.env.local` file.  
Now create, again in the plugin root folder, a `.env.local` file copying the `.env.` file and changing any value in it to match your local installation, e.g.:

```
WP_ROOT_FOLDER="/Users/Luca/Sites/wp"
WP_DOMAIN="tribe.test"
WP_URL="http://tribe.test"
WP_ADMIN_USERNAME="admin"
WP_ADMIN_PASSWORD="secred"
DB_HOST="db"
DB_NAME="tribe"
DB_USER="root"
DB_PASSWORD="root"
TEST_DB_HOST="db"
TEST_DB_NAME="test"
TEST_DB_USER="root"
TEST_DB_PASSWORD="root"
```

If you look at any `tests/*.suite.dist.yml` file you will see that the configuration contains placeholders like `%WP_ROOT_FOLDER%` that [Codeception will configure at runtime](http://codeception.com/docs/06-ModulesAndHelpers#Dynamic-Configuration-With-Parameters).  
Once those are correct you are ready to run, no need to change anything else.
	
## Running the tests
Nothing different from a default Codeception environment so this command will run all the tests in the `wpunit` suite:

```bash
vendor/bin/codecept run wpunit
```

**Do not** run all the suites at the same time using `vendor/bin/codecept run`: due to WordPress love for globals and side-effects this will mean disaster.  
To run a specific test case (a `class`) use:

```bash
vendor/bin/codecept run tests/wpunit/Some/Path/MyTest.php
```

To run a single test method (a `function`) in a test case use:

```bash
vendor/bin/codecept run tests/wpunit/Some/Path/MyTest.php:some_test
```

Failing tests are ok in set up terms: the system works. Errors should be reported.
Please refer to [Codeception documentation](http://codeception.com/docs) to learn about more run and configuration options.

## Where to find help
Look for test examples in the code; look for configuration guides on [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.") and [wp-browser](https://github.com/lucatume/wp-browser "lucatume/wp-browser · GitHub")  site; ask for help to other testers for things like "How should I test this?" or "In what suite should I add this test?".  

