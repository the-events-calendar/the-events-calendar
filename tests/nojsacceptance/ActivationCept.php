<?php
$I = new NojsacceptanceTester($scenario);

$I->am('administrator');
$I->wantTo('verify plugin activation');

$I->haveOptionInDatabase('active_plugins', []);

$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin('the-events-calendar');

$I->seePluginActivated('the-events-calendar');
