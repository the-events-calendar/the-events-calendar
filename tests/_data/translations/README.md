This file accompanies the .mo files you find in this directory.
The `.mo` files here contained are used for testing purposes only, not release purposes; as such, it's fine for them to be incomplete or contain errors.

### Generating the .mo files
In the following example the path used is the one that would be set in a `slic` environment, adjust accordingly.

Assuming you're `slic` or a terminal emulator and that the wp-cli binary, `wp`, is available on the `PATH`, start by generating a `.pot` file for the plugin:

	wp i18n make-pot /var/www/html/wp-content/plugins/the-events-calendar /var/www/html/wp-content/plugins/the-events-calendar/tests/_data/translations/my-test-translation.pot

After some time, you should find the `.pot` file generated in this directory.

Generate a `.po` file for the `it_IT` locale using this command.

	touch /var/www/html/wp-content/plugins/the-events-calendar/tests/_data/translations/my-test-translation-it_IT.po
	wp i18n update-po /var/www/html/wp-content/plugins/the-events-calendar/tests/_data/translations/my-test-translation.pot /var/www/html/wp-content/plugins/the-events-calendar/tests/_data/translations/my-test-translation-it_IT.po

Manually update the translations in the `.po` file as require and generate the final `.mo` file:

	wp i18n make-mo /var/www/html/wp-content/plugins/the-events-calendar/tests/_data/translations/my-test-translation-it_IT.po

> Note: `.pot` and `.po` files are not committed to reduce the repo size and the pull-request noise.