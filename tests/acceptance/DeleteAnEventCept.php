<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

//Activate TEC Calendar
$I->am( 'administrator' );
$I->wantTo( "verify that deleted events can't be viewed on front end" );

// arrange
$I->activate_tec();
$title = 'An event of mine';
$event = get_page_by_title( $title, OBJECT, 'tribe_events' );
if ( $event ) {
	wp_delete_post( $event->ID );
}
$event     = get_post( wp_insert_post( [ 'post_title' => $title, 'post_type' => 'tribe_events' ] ) );
$event_url = get_post_permalink( $event->ID );

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/post.php?post=' . $event->ID . '&action=edit' );
$I->click( '#delete-action > a' );

// assert
$I->useTheme( 'twentyfifteen' );
$I->amOnPage( $event_url );
$I->seeElement( 'body.error404' );
