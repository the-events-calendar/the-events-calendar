<?php
$I = new NojsacceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( 'verify change to TEC Events Slug Setting' );

// arrange
$I->setTribeOption( 'eventsSlug', 'events' );
$current_slug = 'events';

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/edit.php?post_type=tribe_events&page=tribe-common' );
$new_slug = 'classes-and-courses';
$I->fillField( 'eventsSlug', $new_slug );
$I->click( '#tribeSaveSettings' );

// assert
$I->useTheme( 'twentyfifteen' );

$I->amOnPage( '/' . $current_slug );
$I->seeElement( 'body.error404' );
$I->amOnPage( '/' . $new_slug );
$I->dontSeeElement( 'body.error404' );

// change the slug back to the default
$I->setTribeOption( 'eventsSlug', 'events' );
