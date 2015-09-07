<?php
use Tribe\Events\Test\AcceptanceTester;

$I = new AcceptanceTester( $scenario );
$I->am( 'administrator' );
$I->wantTo( 'verify that the events calendar plugin is active' );
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin( 'the-events-calendar' );
$I->amOnPluginsPage(); // skip the welcome message
$I->seePluginActivated( 'the-events-calendar' );