<?php

// @group: settings

$I = new AcceptanceTester( $scenario );
$I->am( 'administrator' );
$I->wantTo( 'verify that TEC Single Event slug can be set' );

// arrange
$title = 'An event of mine';
$event_id = $I->havePostInDatabase(['post_title' => $title , 'post_type' => 'tribe_events', 'post_name' => 'an-event-of-mine']);

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/post.php?post=' . $event_id . '&action=edit' );
$I->click( '#edit-slug-buttons > button.edit-slug' );
$new_slug = 'hopefully-totally-unrelated-slug';
$I->fillField( '#new-post-slug', $new_slug );
$I->click( '#edit-slug-buttons > button.save' );
$I->click('#publish');

// assert
$I->seePostInDatabase(['ID' => $event_id, 'post_name' => $new_slug]);
