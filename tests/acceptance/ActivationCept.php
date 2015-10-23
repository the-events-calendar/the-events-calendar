<?php
$I = new AcceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( 'verify plugin activation' );

$I->activate_tec();

$I->amOnPluginsPage(); // skip the welcome message
$I->seePluginActivated( 'the-events-calendar' );
