# Introduction

This is a guide to help get you up-and-running with tests on your local plugin copy. Please refer to the [Codeception](https://codeception.com/docs) and [wp-browser](https://wpbrowser.wptestkit.dev/) for full documentation.

## Setup

1. Clone this repository on your local machine into a location within a WordPress site's folders. [Local by Flywheel](https://localbyflywheel.com/) makes it easy to create *localhost* sites.
2. Change directory (`cd`) to the root of this cloned repo.
3. Check if the Common submodule came through. If not, run this:
	```bash
	git submodule update --init --recursive
	```
4. Pull in `common` dependencies and generate its autoloader:
	```bash
	(cd common; composer update)
	```
5. Pull in any needed dependency using [Composer](https://getcomposer.org/):
	```bash
	composer update
	```
6. Database setup (an application like [Sequel Pro](https://www.sequelpro.com/) can be helpful)
	1. It is highly recommended to use `wp_` for your all your table prefixes (actual site and test databases) because it is the environment's default and The Events Calendar import files use it.
	2. Add a new database named `test`
	3. Acceptance and functional tests might not run correctly unless you have tables in your `test` database so import `the-events-calendar/tests/_data/dump.sql` (it contains the required tables and some bootstrap data)
7. Duplicate the `.env` file to `.env.testing.local` and edit it for your localhost site's information, matching `wp-config.php` (except needing to use Local by Flywheel's IP address and port for the database host). Example:
	```dotenv
	# WordPress root folder is the one that contains the wp-load.php file
	WP_ROOT_FOLDER=~/Local Sites/tribe

	WP_URL=https://tribe.local
	WP_DOMAIN=tribe.local

	WP_ADMIN_USERNAME=admin
	WP_ADMIN_PASSWORD=password

	# The credentials of the database that will be used in acceptance/functional tests.
	WP_DB_PORT=4019
	WP_DB_HOST=192.168.75.100:4019
	WP_DB_NAME=local
	WP_DB_USER=root
	WP_DB_PASSWORD=root

	# The credentials of the database that will be used in integration/wp-unit tests.
	WP_TEST_DB_HOST=192.168.75.100:4019
	WP_TEST_DB_NAME=test
	WP_TEST_DB_USER=root
	WP_TEST_DB_PASSWORD=root

	CHROMEDRIVER_HOST=localhost
	CHROMEDRIVER_PORT=4444
	WP_CHROMEDRIVER_URL=tribe.local
	```
8. Duplicate the `codeception.dist.yml` file to `codeception.yml` and change _params_ to point to `.env.testing.local` instead of `.env`:
	```yaml
	params:
	  - .env.testing.local
	```
9. _PhpStorm > Preferences > Languages & Frameworks > PHP > Test Frameworks…_ -- You do not have to set this up because you should run Codeception via command line.
10. Run a single Codeception test to confirm your setup is working (see _Running the Tests_, below).

If you look at any `tests/*.suite.dist.yml` file you will see that the configuration contains placeholders like `%WP_ROOT_FOLDER%` that [Codeception will configure at runtime](http://codeception.com/docs/06-ModulesAndHelpers#Dynamic-Configuration-With-Parameters).
Once those are correct you are ready to run, no need to change anything else.

## Running the Tests

***Do not*** run all the suites at the same time using `codecept run`. This will mean disaster due to WordPress' love for globals and other side-effects.

Failing tests are ok in set up terms: the system works. Errors should be fixed, if you cannot fix them, then report them.

Refer to [Codeception documentation](http://codeception.com/docs) to learn more about run and configuration options.

### Examples

##### Single test method:

```bash
codecept run tests/wpunit/Tribe/Events/CommonTest.php:it_is_loading_common
```

##### Single test class:

```bash
codecept run tests/wpunit/Tribe/Events/CommonTest.php
```

##### All the tests within the _wpunit_ suite:

```bash
codecept run wpunit
```

## Running acceptance tests

Codecepton can run acceptance tests like any other test. However, acceptance tests may be written in a way that requires Selenium and Chromedriver to "drive" the Chrome browser through the tests.

While you can spin up your local solution, the fastest way is to use a container (requires having [Docker](https://docs.docker.com/install/) installed and working on your system):

```bash
docker run --name chromedriver --rm -d -p 4444:4444 selenium/standalone-chrome
```

The command will pull, if not already present on your machine, and run a Docker container with the Selenium and Chromedriver stand-alone installations that will listen for connections on port `4444`.

The command is also available as a [Composer script](https://getcomposer.org/doc/articles/scripts.md) from this repo's root folder:

```bash
composer start-chromedriver
```

Since the container is running in "detached" mode (due to the `-d` flag used to run it) to stop it you can either stop it using the Docker daemon directly:

```bash
docker stop chromedriver
```

Or you can use the Composer script defined in the plugin `composer.json` file:

```bash
composer stop-chromedriver
```

## Notes

* Codeception will process configuration files in a cascading way (think of CSS) so the `codeception.dist.yml` file will be read first and whatever you set in `codeception.yml` will be applied on top of it. This is true for the main configuration file (`codeception.dist.yml`) and for any suite configuration file (e.g. `acceptance.suite.dist.yml`). You should not commit changes (unless you know what you're doing) to the `.dist` version of the main, or the suites, configuration files as those are used by our CI system to run the tests. On the same note: you should not push local version of the configuration files (without `.dist`) to the repository origin.
* Do not install Codeception globally ("Install Phar Globally" from https://codeception.com/install) because different repos use different versions of Codeception, and you need to use the version that comes with each plugin's repo, as defined in `composer.json`. (We do not update the required Codeception version unless strictly required.)
* Each repo needs its own `.env.testing.local` because Codeception knows where to look for it in each repo's relative location, and there are some inconsistencies in parameter names between the various plugins' repos; additionally different plugins need different configurations and the environment files (`.env`) are used in the CI tests.
* Ideally, you should only run the tests via command line, not via PhpStorm, because PhpStorm can only be configured to run one instance of Codeception so you would have to manually switch the configuration file to be use if you need to test Event Tickets, then The Events Calendar, then Common, and so on.
* Additionally, PhpStorm may do some weird argument redirection that could lead to difficulties during debug.
* Regarding parameter names: *WP_DB* is for acceptance/functional tests, and *WP_TEST_DB* is for integration tests. You should set them accordingly. We've got some inconsistency in naming, but this is how parameters are supposed to be named.
* Even though ET, ET+, and TEC have a *WP_DB_PORT* parameter, *DB_HOST* and *TEST_DB_HOST* need to have the port added to database host, like this: `192.168.95.100:4010`
* Some of the repos have double-quotes around the parameters' values, and others do not. You should be okay either way, except when specifying paths ([reference](https://stackoverflow.com/questions/33318499/should-i-use-quotes-in-environment-path-names)).

## Helpful hints

### Avoid having to type vendor/bin/codecept
In terminal, run this so you can drop the _vendor/bin/_ prefix from the run command:
	```bash
	echo "export PATH=vendor/bin:$PATH" >> ~/.bashrc
	```
([explanation of what this command does](https://askubuntu.com/questions/720678/what-does-export-path-somethingpath-mean)).
The command will add a line to your shell configuration to add the `vendor/bin` path to those it looks up to find a command. If you use a shell different from `bash` you might need to change the command to write to the correct configuration file, e.g. `~/.zshrc` if you use `zsh`.

## Where to find additional help
Look for test examples in the code; look for configuration guides on [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.") and [wp-browser](https://wpbrowser.wptestkit.dev) site; and ask for help to other testers for things like "How should I test this?" or "In which suite should I add this test?"
