# The Events Calendar tests

This is a brief and quick guide that's covering the bare essentials needed to set up the tests on your local plugin copy.
Please refer to [Codeception](http://codeception.com/docs) and [WP Browser](https://github.com/lucatume/wp-browser) documentation for any issue that's not TEC related.

## Set up
After cloning the TEX repository on your local machine change directory to the plugin root folder and pull in any needed dependency using [Composer](https://getcomposer.org/):

	composer update

when Composer finished the update process (might take a while) set up your own [Codeception](http://codeception.com/) installation running

	vendor/bin/wpcept bootstrap

The `wpcept bootstrap` command is a modified version of the default `codecept bootstrap` command that will take care of setting up a WordPress-friendly testing environment.  
To be able to run successfully on your system Codeception will need to be configured to look for the right database, the right WordPress installation and so on.  
Codeception allows for "distribution" versions of its configuration to be  shared among developers, what you define in your local Codeception configuration files will override the "distribution" setting; think of CSS rules.  
The repository contains a `codeception.dist.yml` file that Codeception will read before reading the local to your machine `codeception.yml` file.  
Copy the distribution version of the Codeception configuration file in the root folder of the plugin

	cp codeception.dist.yml codeception.yml

**Edit the file `codeception.yml` file to suit your database, installation folder and web driver settings.**

**Beware**: The `WPLoader` module that's used in functional tests will **destroy** the database it's working on: **do not** point it to the same database you use for development! A good rule of thumb is to have a database for development (e.g. `tec`) and one that will be used for tests (e.g. `tec-tests`).  
On the same lines the repository packs "distribution" versions of the `unit.suite.dist.yml`, `functional.suite.dist.yml` and `acceptance.suite.dist.yml` configuration files: there is usually no need to override those but it's worth mentioning they exist.
The last piece of the configuration is the bootstrap file; the repository comes with "distribution" versions of these file in the root folder of the pluging tests (`/tests/_bootstrap.dist.php`) and a bootstrap file specific to each suite (`/tests/acceptance/_bootstrap.dist.php`, `/tests/functional/_bootstrap.dist.php`, `/tests/unit/_bootstrap.dist.php`); remove the root `_bootstrap.php` file Codeception created during bootstrapping and copy the one in the root of the plugin tests (`/tests`)
	
	rm _bootstrap.php
	cp _bootstrap.dist.php _bootstrap.php

You *should* not need to edit anything in any bootstrap file to make things work. Do the same for the suite specific bootstrap files

	cp acceptance/_bootstrap.dist.php acceptance/_bootstrap.php
	cp functional/_bootstrap.dist.php functional/_bootstrap.php
	cp unit/_bootstrap.dist.php unit/_bootstrap.php
	
## Running the tests
Nothing different from a default Codeception environment so this command will run all the tests

	vendor/bin/codecept run

Failing tests are ok in set up terms: the system works. Errors should be reported.
Please refer to [Codeception documentation](http://codeception.com/docs) to learn about more run and configuaration options.

## Contributing to tests
Should you come up with good utility methods, worthy database configurations and "cool additions" in general for the plugin tests feel free to open a PR and submit them for review.
