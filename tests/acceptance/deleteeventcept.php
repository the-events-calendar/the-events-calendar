<?php use Tribe\Events\Test\UITester;
$scenario->group('settings');
$I = new UITester\EventSteps($scenario);

//Activate TEC Calendar
$I->am('administrator');
$I->wantTo("verify that deleted events can't be viewed on front end");

$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin('the-events-calendar');
$I->amOnPluginsPage(); // skip the welcome message
$I->seePluginActivated('the-events-calendar');

//Set Permalinks to pretty
$I->amOnPage('wp-admin/options-permalink.php');
$I->see('Permalink Settings');
$I->selectOption('form input[name=selection]', '/%postname%/');
$I->click('Save Changes');

//Set up test Event and test it exists
$I->createEvent(array( 'title' => "Test Event A" ));
$I->amOnPage( "event/test-event-a" );
$I->see('Test Event A');

//Delete Event
$I->click('Edit Page');
$I->click('Move to Trash');
$I->amOnPage('event/test-event-a');
$I->see('Oops! Page Not Found.');