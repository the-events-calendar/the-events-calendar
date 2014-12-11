<?php use Tribe\Events\Codeception\UITester;

$scenario->group('settings');

$I = new UITester($scenario);
$I->am('administrator');
$I->wantTo('verify that TEC Events Slug Setting');

// Activate The Events Calendar Plugin
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin('the-events-calendar');
$I->see('Thanks for Updating');

// Set Permalink to "pretty"
$I->amOnPage('wp-admin/options-permalink.php');
$I->see('Permalink Settings');
$I->selectOption('form input[name=selection]', '/%postname%/');
$I->click('Save Changes');

// Verify Events Calendar is at default URL
$I->amOnPage('/events/');
$I->see('Upcoming Events');

// Change Events URL
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=tribe-events-calendar' );
$I->fillField('eventsSlug', 'classes');
$I->click('Save Changes');

// Verify Events Calendar is at new URL.
$I->amOnPage('/classes/');
$I->see('Upcoming Events');
