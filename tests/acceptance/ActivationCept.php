<?php
$I = new AcceptanceTester($scenario);

$I->am('administrator');
$I->wantTo('verify plugin activation');

// arrange
$I->bootstrapWp();
update_option('active_plugins', []);

// act
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin('the-events-calendar');

// assert
$active_plugins = $I->grabOptionFromDatabase('active_plugins');
$I->assertContains('the-events-calendar/the-events-calendar.php', $active_plugins);
