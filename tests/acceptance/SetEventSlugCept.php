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
	'post_name' => 'an-event-of-mine'
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
$I->click( '#publish' );
$I->wait( 2 );

// assert
$I->amOnPage( $old_event_url );
$I->seeElement( 'body.error404' );
$new_event_url = home_url( $single_event_slug . '/hopefully-totally-unrelated-slug' );
$I->amOnPage( $new_event_url );
$I->dontSeeElement( 'body.error404' );
$full_url = $I->grabFullUrl();
$I->assertContains( $new_slug, $full_url );
