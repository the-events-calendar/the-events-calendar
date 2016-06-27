<?php

// @group: settings
$I = new NojsacceptanceTester($scenario);

//Activate TEC Calendar
$I->am('administrator');
$I->wantTo("verify that an event title can be modified");

// arrange
$old_title = 'A test event';
$event_id = $I->havePostInDatabase(['post_type' => 'tribe_events', 'post_title' => $old_title]);

// act
$I->loginAsAdmin();
$I->amOnAdminPage('/post.php?post=' . $event_id . '&action=edit');
$I->fillField('post_title', 'A new title');
$I->click('#publish');

// assert
$I->seePostInDatabase(['post_type' => 'tribe_events', 'ID' => $event_id, 'post_title' => 'A new title']);


