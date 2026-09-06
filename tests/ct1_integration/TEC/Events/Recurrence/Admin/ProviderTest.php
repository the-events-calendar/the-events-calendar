<?php

namespace TEC\Events\Recurrence\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class ProviderTest extends WPTestCase {
	use With_Recurrence_Engine;

	/** @test */
	public function should_preserve_pro_columns_and_use_distinct_occurrence_date_columns(): void {
		$request = $_REQUEST;
		$_REQUEST['tec_events_view'] = 'occurrences';
		try {
			$columns = tribe( Provider::class )->columns( [ 'cb' => 'Checkbox', 'title' => 'Title', 'start-date' => 'Start', 'end-date' => 'End', 'series' => 'Series' ] );
			$this->assertArrayHasKey( 'series', $columns );
			$this->assertArrayHasKey( 'tec-schedule', $columns );
			$this->assertArrayHasKey( 'tec-start-date', $columns );
			$this->assertArrayNotHasKey( 'start-date', $columns );
			$this->assertArrayNotHasKey( 'cb', $columns );
			$this->assertSame( [], tribe( Provider::class )->bulk_actions( [ 'trash' => 'Trash' ] ) );
			$_REQUEST['tec_events_view'] = 'events';
			$this->assertSame( [ 'trash' => 'Trash' ], tribe( Provider::class )->bulk_actions( [ 'trash' => 'Trash' ] ) );
		} finally {
			$_REQUEST = $request;
		}
	}

	/** @test */
	public function should_show_identity_outside_hover_actions_and_keep_the_occurrence_url(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$post = $this->given_a_multi_date_event();
		$row = Occurrence::where( 'post_id', $post->ID )->first();
		$screen = $GLOBALS['current_screen'] ?? null;
		set_current_screen( 'edit-tribe_events' );
		try {
			ob_start();
			$actions = tribe( Provider::class )->row_actions( [ 'edit' => 'Edit' ], get_post( $row->provisional_id ) );
			$html = ob_get_clean();
			$this->assertStringContainsString( 'tec-occurrence-admin__identity', $html );
			$this->assertStringContainsString( 'Occurrence', $html );
			$this->assertStringContainsString( 'Multiple dates', $html );
			$this->assertStringContainsString( 'post=' . $row->provisional_id, $actions['edit'] );
			$this->assertStringContainsString( 'post=' . $post->ID, $actions['tec-edit-event'] );
			$this->assertStringContainsString( 'tec_dates=all', $actions['tec-dates'] );
		} finally {
			$GLOBALS['current_screen'] = $screen;
		}
	}

	/** @test */
	public function should_keep_filters_in_navigation_without_copying_action_nonces(): void {
		$request = $_REQUEST;
		$_REQUEST = [ 's' => 'Yoga & music', 'author' => '12', 'tec_dates' => 'past', 'tec_event' => '45', 'paged' => '9', '_wpnonce' => 'secret', 'action' => 'trash' ];
		try {
			parse_str( wp_parse_url( Provider::url(), PHP_URL_QUERY ), $args );
			$this->assertSame( 'Yoga & music', $args['s'] );
			$this->assertSame( 'past', $args['tec_dates'] );
			$this->assertSame( '45', $args['tec_event'] );
			$this->assertArrayNotHasKey( 'paged', $args );
			$this->assertArrayNotHasKey( '_wpnonce', $args );
			$this->assertArrayNotHasKey( 'action', $args );
		} finally {
			$_REQUEST = $request;
		}
	}
}
