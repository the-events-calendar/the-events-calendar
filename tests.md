# The Events Calendar tests

This is a brief and quick guide that's covering the bare essentials needed to set up the tests on your local plugin copy.
Please refer to [Codeception](http://codeception.com/docs) and [WP Browser](https://github.com/lucatume/wp-browser) documentation for any issue that's not TEC related.

## Set up
After cloning the TEX repository on your local machine change directory to the plugin root folder and pull in any needed dependency using [Composer](https://getcomposer.org/):

	composer update

when Composer finished the update process (might take a while) set up your own [Codeception](http://codeception.com/) installation running

	wpcept bootstrap:pyramid

The `wpcept bootstrap:pyramid` command is a modified version of the default `codecept bootstrap` command that will take care of setting up a WordPress-friendly testing environment.
The repository contains a `codeception.dist.yml` file that Codeception will read before reading the local to your machine `codeception.yml` file: this means that any setting you define in your own `codeception.yml` file will overwrite the setting in the `codeception.dist.yml` file while any setting you do not overwrite will be read from the `codeception.dist.yml` file; think CSS properties.
Along the same path each suite has its own configuration files and the repository comes with a `ui.dist.yml` file that contains some UI (acceptance) testing defaults.
Ideally you should need to overwrite just folder paths and database access configurations.

###  Bootstrap
After the bootstrapping process has completed each suite will contain a `_bootstrap.php` file and a general purpose `_bootstrap.php` file will be placed in the root tests folder; the repository comes with a `_bootstrap.dist.php` file that can replace the Codeception-created one completely, included or be used as a reference to set up a specific `_bootstrap` file.

## Running the tests
Nothing different from a default Codeception environment so this command will run all the tests

	codecept run

Please refer to [Codeception documentation](http://codeception.com/docs) to learn about more run and configuaration options.
