<?php

// @group: settings
$I = new AcceptanceTester( $scenario );

$I->am( 'administrator' );
$I->wantTo( "verify that a category can be created" );

// arrange
$I->activate_tec();
$term_slug = 'some-event-category';
$term      = get_term_by( 'slug', $term_slug, 'tribe_events_cat' );
if ( $term ) {
	wp_delete_term( $term->term_id, 'tribe_events_cat' );
}

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events' );
$I->fillField( 'tag-name', 'Some event category' );
$I->fillField( 'slug', $term_slug );
$I->fillField( 'description', 'Yet another event term' );
$I->click( '#submit' );

// assert
$I->waitForJqueryAjax( 10 );
$term = get_term_by( 'slug', $term_slug, 'tribe_events_cat' );
$I->assertNotEmpty( $term );
$I->assertEquals( 'Some event category', $term->name );
$I->assertEquals( 'Yet another event term', $term->description );
