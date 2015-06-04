<?php use Tribe\Events\Codeception\UITester;
$I = new UITester($scenario);
$I->am('administrator');
$I->wantTo('verify that the events calendar plugin is active');
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin('the-events-calendar');
$I->amOnPluginsPage(); // skip the welcome message
$I->seePluginActivated('the-events-calendar');