<?php

// @group: settings
$I = new NojsacceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( "verify that a tag can be created" );

$term_slug = 'some-event-tag';

$I->dontSeeTermInDatabase( [ 'slug' => $term_slug, 'taxonomy' => 'post_tag' ] );

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/edit-tags.php?taxonomy=post_tag&post_type=tribe_events' );
$I->fillField( 'tag-name', 'Some event tag' );
$I->fillField( 'slug', $term_slug );
$I->fillField( 'description', 'Yet another event term' );
$I->click( '#submit' );

// assert
$I->seeTermInDatabase( [ 'slug' => $term_slug, 'taxonomy' => 'post_tag' ] );
