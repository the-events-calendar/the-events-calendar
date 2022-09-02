<?php

namespace TEC\Events\Custom_Tables\V1\Models;

use DateTime;
use DateTimeZone;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Occurrence_ValidatorTest extends \Codeception\TestCase\WPTestCase {
	use CT1_Fixtures;
	/**
	 * Validates the duration calculated takes into consideration both timezones and DST.
	 *
	 * @test
	 */
	public function should_validate_occurrence_dst_duration(  ) {
		// Setup
		$post       = $this->given_a_migrated_single_event();
		$occurrence = Occurrence::find_by_post_id( $post->ID );
		$this->assertInstanceOf( Occurrence::class, $occurrence );
		$this->assertTrue( $occurrence->validate() );
		$event = Event::find( $occurrence->event_id );
		$this->assertInstanceOf( Event::class, $event );

		// Given an event that spans a DST date
		$event->timezone       = 'America/Los_Angeles';
		$event->duration       = 259199;
		$start                 = '2022-11-05 00:00:00';
		$end                   = '2022-11-07 23:59:59';
		$start_date            = new DateTime( $start, new DateTimeZone( $event->timezone ) );
		$start_date_utc        = ( clone $start_date )->setTimezone( new DateTimeZone( 'UTC' ) );
		$end_date              = new DateTime( $end, new DateTimeZone( $event->timezone ) );
		$end_date_utc          = ( clone $end_date )->setTimezone( new DateTimeZone( 'UTC' ) );
		$event->start_date     = $start_date->format( 'Y-m-d H:i:s' );
		$event->end_date       = $end_date->format( 'Y-m-d H:i:s' );
		$event->start_date_utc = $start_date_utc->format( 'Y-m-d H:i:s' );
		$event->end_date_utc   = $end_date_utc->format( 'Y-m-d H:i:s' );
		$event->update();
		$occurrence->duration       = 262799;//259199;
		$occurrence->start_date     = $start_date->format( 'Y-m-d H:i:s' );
		$occurrence->end_date       = $end_date->format( 'Y-m-d H:i:s' );
		$occurrence->start_date_utc = $start_date_utc->format( 'Y-m-d H:i:s' );
		$occurrence->end_date_utc   = $end_date_utc->format( 'Y-m-d H:i:s' );

		$valid = $occurrence->validate();
		$this->assertEmpty( $occurrence->errors(), "Errors found: " . var_export( $occurrence->errors(), true ) );
		$this->assertTrue( $valid );

		// Should not be bigger
		$occurrence->duration = $occurrence->duration + 1;
		$valid                = $occurrence->validate();
		$this->assertArrayHasKey( 'duration', $occurrence->errors() );
		$this->assertFalse( $valid );
	}

}