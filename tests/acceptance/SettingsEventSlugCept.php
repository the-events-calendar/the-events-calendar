<?php

// @group: settings

$I = new AcceptanceTester( $scenario );
$I->am( 'administrator' );
$I->wantTo( 'verify that TEC Single Event Slug Setting' );

$I->activate_tec();
$I->set_pretty_permalinks();

// Create new Event
$I->createEvent( array( 'title' => 'Sample Event' ) );

// Navigate to new Event
// Verify Event is at default URL
$I->amOnPage( '/event/sample-event' );
$I->see( 'Sample Event' );

// Change Event URL
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=tribe-common' );
$I->fillField( 'singleEventSlug', 'box' );
$I->click( 'Save Changes' );

// Verify Event is at new URL.
$I->amOnPage( '/box/sample-event' );
$I->see( 'Sample Event' );

// Change the slug back
$I->amOnPage( '/wp-admin/edit.php?post_type=tribe_events&page=tribe-common' );
$I->fillField( 'singleEventSlug', 'event' );
$I->click( 'Save Changes' );
