<?php use Tribe\Events\Test\UITester;

$scenario->group('settings');

$I = new UITester\EventSteps($scenario);
$I->am('administrator');
$I->wantTo('verify that TEC Single Event Slug Setting');

// Activate The Events Calendar Plugin
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin('the-events-calendar');
$I->see('Thanks for Updating');

// Set Permalink to "pretty"
$I->amOnPage('/wp-admin/options-permalink.php');
$I->see('Permalink Settings');
$I->selectOption('form input[name=selection]', '/%postname%/');
$I->click('Save Changes');

// Create new Event
$I->createEvent(array( 'title' => "Sample Event" ) );

// Navigate to new Event
// Verify Event is at default URL
$I->amOnPage( "/event/sample-event" );
$I->see('Sample Event');

// Change Event URL
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=tribe-events-calendar' );
$I->fillField('singleEventSlug', 'box');
$I->click('Save Changes');

// Verify Event is at new URL.
$I->amOnPage('/box/sample-event');
$I->see('Sample Event');
