<?php

// @group: settings

$I = new AcceptanceTester( $scenario );
$I->am( 'administrator' );
$I->wantTo( 'verify that TEC Single Event slug can be set' );

// arrange
$I->activate_tec();
$I->setPermalinkStructureAndFlush( '/%postname%/' );
$title = 'An event of mine';
$event = get_page_by_title( $title, OBJECT, 'tribe_events' );
if ( $event ) {
	wp_delete_post( $event->ID );
}
$event_id          = wp_insert_post( [
	'post_title' => $title,
	'post_type'  => 'tribe_events',
	'post_name' => 'an-event-of-mine',
    'post_status' => 'publish',
] );
$single_event_slug = $I->getTribeOptionFromDatabase( 'singleEventSlug', 'event' );
$old_event_url     = home_url( $single_event_slug . '/an-event-of-mine' );

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/post.php?post=' . $event_id . '&action=edit' );
$I->click( '#edit-slug-buttons > button.edit-slug' );
$new_slug = 'hopefully-totally-unrelated-slug';
$I->fillField( '#new-post-slug', $new_slug );
$I->click( '#edit-slug-buttons > button.save' );
$I->waitForJqueryAjax(10);
$I->click('#publish');
$I->wait(3);

// assert
$I->seePostInDatabase(['ID' => $event_id, 'post_name' => $new_slug]);
