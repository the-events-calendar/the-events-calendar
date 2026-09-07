<?php

namespace TEC\Events\Recurrence\Admin;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_REST_Request;

class Past_DatesTest extends WPTestCase {
	use With_Recurrence_Engine;

	/** @test */
	public function should_bound_initial_past_only_dates_and_load_the_remaining_page(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$dates = [];
		for ( $day = 2; $day <= 30; ++$day ) {
			$dates[] = [ 'start' => sprintf( '2020-01-%02d 09:00:00', $day ), 'end' => sprintf( '2020-01-%02d 10:00:00', $day ) ];
		}
		$post = $this->given_a_multi_date_event( $dates, [ 'start_date' => '2020-01-01 09:00:00', 'end_date' => '2020-01-01 10:00:00' ] );
		$api = tribe( Past_Dates::class );
		$summary = $api->summary( $post->ID );
		$this->assertSame( 30, $summary['count'] );
		$this->assertCount( 25, $summary['dates'] );
		$this->assertTrue( $summary['pastOnly'] );
		$this->assertSame( 25, $summary['next'] );
		$this->assertSame( 'January 30, 2020', $summary['dates'][0]['label'] );
		$page = $api->page( $post->ID, 25, $summary['asOf'] );
		$this->assertCount( 5, $page['dates'] );
		$this->assertNull( $page['next'] );
		$links = array_column( array_merge( $summary['dates'], $page['dates'] ), 'edit_link' );
		$this->assertCount( 30, array_unique( $links ) );
		$request = new WP_REST_Request( 'GET' );
		$request['id'] = $post->ID;
		$this->assertTrue( $api->can_read( $request ) );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$this->assertFalse( $api->can_read( $request ) );
		wp_set_current_user( 0 );
		$this->assertFalse( $api->can_read( $request ) );
	}
}
