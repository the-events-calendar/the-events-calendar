<?php

$I = new AcceptanceTester( $scenario );
$I->am( 'administrator' );
$I->wantTo( 'Test events CSV import' );

// act
$I->loginAsAdmin();
$I->amOnAdminPage( '/edit.php?post_type=tribe_events&page=events-importer&tab=csv-importer' );
$I->selectOption( 'import_type', 'venues' );
$I->checkOption( '#events-import-header' );
$I->attachFile( 'import_file', 'csv-import-test-files/venues.csv' );
$I->click( 'form#import input[type="submit"]' );

$I->submitForm( 'form#import',
	[
		'column_map[0]' => 'venue_name',
		'column_map[1]' => 'venue_country',
		'column_map[2]' => 'venue_address',
		'column_map[3]' => 'venue_address2',
		'column_map[4]' => 'venue_city',
		'column_map[5]' => 'venue_state',
		'column_map[6]' => 'venue_zip',
		'column_map[7]' => 'venue_phone',
	],
	'submit' );

$I->waitForJqueryAjax( 10 );
$I->wait(2);
$I->seeElement( '.tribe-import-success' );

$I->amOnAdminPage( '/edit.php?post_type=tribe_events&page=events-importer&tab=csv-importer' );
$I->selectOption( 'import_type', 'organizers' );
$I->checkOption( '#events-import-header' );
$I->attachFile( 'import_file', 'csv-import-test-files/organizers.csv' );
$I->click( 'form#import input[type="submit"]' );


$I->submitForm( 'form#import',
	[
		'column_map[0]' => 'organizer_name',
		'column_map[1]' => 'organizer_email',
		'column_map[2]' => 'organizer_website',
		'column_map[3]' => 'organizer_phone',
	],
	'submit' );

$I->waitForJqueryAjax( 10 );
$I->wait(2);
$I->seeElement( '.tribe-import-success' );

$I->amOnAdminPage( '/edit.php?post_type=tribe_events&page=events-importer&tab=csv-importer' );
$I->selectOption( 'import_type', 'events' );
$I->checkOption( '#events-import-header' );
$I->attachFile( 'import_file', 'csv-import-test-files/events.csv' );
$I->click( 'form#import input[type="submit"]' );


$I->submitForm( 'form#import',
	[
		'column_map[0]'  => 'event_name',
		'column_map[1]'  => 'event_description',
		'column_map[2]'  => 'event_start_date',
		'column_map[3]'  => 'event_start_time',
		'column_map[4]'  => 'event_end_date',
		'column_map[5]'  => 'event_end_time',
		'column_map[6]'  => 'event_all_day',
		'column_map[7]'  => 'event_venue_name',
		'column_map[8]'  => 'event_organizer_name',
		'column_map[9]'  => 'event_show_map_link',
		'column_map[10]' => 'event_show_map',
		'column_map[11]' => 'event_cost',
		'column_map[12]' => 'event_category',
		'column_map[13]' => 'event_website',
	],
	'submit' );

$I->waitForJqueryAjax( 10 );
$I->wait(2);

// assert
$I->seeElement( '.tribe-import-success' );
