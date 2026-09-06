<?php

namespace TEC\Events\Recurrence\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Event;
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
			$this->assertArrayNotHasKey( 'tec-schedule', $columns );
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
			$this->assertStringContainsString( 'tec-occurrence-admin__badge--dates', $html );
			$this->assertStringNotContainsString( 'tec-occurrence-admin__lock', $html );
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

	/** @test */
	public function should_preserve_parent_management_when_quick_edit_refreshes_a_row(): void {
		$request = $_REQUEST;
		$_REQUEST['tec_events_view'] = 'events';
		$_REQUEST['tec_dates'] = 'all';
		$_REQUEST['tec_event'] = '45';
		try {
			ob_start();
			tribe( Provider::class )->inline_context( 'tec-start-date', 'tribe_events' );
			$html = ob_get_clean();
			$this->assertStringContainsString( 'name="tec_events_view" value="events"', $html );
			$this->assertStringContainsString( 'name="tec_dates" value="all"', $html );
			$this->assertStringContainsString( 'name="tec_event" value="45"', $html );
			ob_start();
			tribe( Provider::class )->filters( 'tribe_events', 'top' );
			$filters = ob_get_clean();
			$this->assertStringContainsString( 'name="tec_dates" value="all"', $filters );
			$this->assertStringContainsString( 'name="tec_event" value="45"', $filters );
			$this->assertStringNotContainsString( 'id="tec-occurrence-range"', $filters );
			$this->assertArrayHasKey( 'cb', tribe( Provider::class )->columns( [ 'cb' => 'Select', 'author' => 'Author' ] ) );
		} finally {
			$_REQUEST = $request;
		}
	}

	/** @test */
	public function should_retain_date_and_parent_filters_in_author_and_tag_destinations(): void {
		$request = $_REQUEST;
		$screen = $GLOBALS['current_screen'] ?? null;
		$_REQUEST = [ 'tec_dates' => 'past', 'tec_event' => '45' ];
		set_current_screen( 'edit-tribe_events' );
		try {
			$link = tribe( Provider::class )->taxonomy_links( [ '<a href="edit.php?post_type=tribe_events&amp;tag=yoga">Yoga</a>' ] )[0];
			$this->assertStringContainsString( 'tec_dates=past', $link );
			$this->assertStringContainsString( 'tec_event=45', $link );
			$this->assertStringContainsString( 'tag=yoga', $link );
			$post = static::factory()->post->create( [ 'post_type' => 'tribe_events', 'post_author' => static::factory()->user->create() ] );
			ob_start();
			tribe( Provider::class )->column( 'tec-author', $post );
			$html = ob_get_clean();
			$this->assertStringContainsString( 'tec_dates=past', $html );
			$this->assertStringContainsString( 'tec_event=45', $html );
		} finally {
			$_REQUEST = $request;
			$GLOBALS['current_screen'] = $screen;
		}
	}

	/** @test */
	public function should_save_the_display_preference_with_native_screen_options_for_only_the_current_user(): void {
		$request = $_REQUEST;
		$get = $_GET;
		$post = $_POST;
		$page = $GLOBALS['pagenow'] ?? null;
		$user = get_current_user_id();
		$admin = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin );
		$provider = tribe( Provider::class );
		try {
			$_REQUEST = [];
			$this->assertSame( 'occurrences', Provider::view() );
			$GLOBALS['pagenow'] = 'edit.php';
			$_GET = [ 'post_type' => 'tribe_events' ];
			$_POST = [ 'tec_occurrence_screen_options' => '1', 'wp_screen_options' => [ 'option' => 'edit_tribe_events_per_page', 'value' => '25' ] ];
			$provider->save_screen_settings( 'screen-options-nonce', false );
			$this->assertFalse( get_user_option( 'tec_events_display_occurrences' ) );
			$provider->save_screen_settings( 'screen-options-nonce', 1 );
			$this->assertSame( 'events', Provider::view() );
			$this->assertSame( '25', $_POST['wp_screen_options']['value'] );
			$url = apply_filters( 'wp_redirect', admin_url( 'edit.php?post_type=tribe_events&tec_events_view=occurrences&tec_dates=past&s=Yoga%20%26%20music' ), 302 );
			parse_str( wp_parse_url( $url, PHP_URL_QUERY ), $args );
			$this->assertSame( 'events', $args['tec_events_view'] );
			$this->assertSame( 'past', $args['tec_dates'] );
			$this->assertSame( 'Yoga & music', $args['s'] );
			$this->assertFalse( has_filter( 'wp_redirect', [ $provider, 'screen_settings_redirect' ] ) );
			$_REQUEST['tec_events_view'] = 'occurrences';
			$this->assertSame( 'occurrences', Provider::view() );
			unset( $_REQUEST['tec_events_view'] );
			wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
			$this->assertSame( 'occurrences', Provider::view() );
			wp_set_current_user( $admin );
			$_POST['tec_display_occurrences'] = '1';
			$provider->save_screen_settings( 'screen-options-nonce', 1 );
			$this->assertSame( 'occurrences', Provider::view() );
			wp_set_current_user( static::factory()->user->create( [ 'role' => 'subscriber' ] ) );
			$provider->save_screen_settings( 'screen-options-nonce', 1 );
			$this->assertFalse( get_user_option( 'tec_events_display_occurrences' ) );
		} finally {
			remove_filter( 'wp_redirect', [ $provider, 'screen_settings_redirect' ] );
			wp_set_current_user( $user );
			$_REQUEST = $request;
			$_GET = $get;
			$_POST = $post;
			$GLOBALS['pagenow'] = $page;
		}
	}

	/** @test */
	public function should_expose_the_current_display_setting_only_on_the_events_screen(): void {
		$request = $_REQUEST;
		$screen = $GLOBALS['current_screen'] ?? null;
		try {
			set_current_screen( 'edit-tribe_events' );
			$_REQUEST['tec_events_view'] = 'events';
			$html = tribe( Provider::class )->screen_settings( 'existing', get_current_screen() );
			$this->assertStringStartsWith( 'existing', $html );
			$this->assertStringContainsString( 'Display occurrences', $html );
			$this->assertStringNotContainsString( "checked='checked'", $html );
			$_REQUEST['tec_events_view'] = 'occurrences';
			$this->assertStringContainsString( "checked='checked'", tribe( Provider::class )->screen_settings( '', get_current_screen() ) );
			set_current_screen( 'edit-post' );
			$this->assertSame( 'existing', tribe( Provider::class )->screen_settings( 'existing', get_current_screen() ) );
		} finally {
			$_REQUEST = $request;
			$GLOBALS['current_screen'] = $screen;
		}
	}

	/** @test */
	public function should_put_the_lock_and_its_accessible_explanation_inside_the_title_badge(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$post = $this->given_a_multi_date_event();
		delete_post_meta( $post->ID, '_EventRecurrence' );
		Event::find( $post->ID, 'post_id' )->update( [ 'rset' => "DTSTART:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=2" ] );
		$screen = $GLOBALS['current_screen'] ?? null;
		set_current_screen( 'edit-tribe_events' );
		try {
			ob_start();
			tribe( Provider::class )->row_actions( [], $post );
			$html = ob_get_clean();
			$this->assertStringContainsString( 'tec-occurrence-admin__badge--rules', $html );
			$this->assertStringContainsString( 'class="tec-occurrence-admin__lock"', $html );
			$this->assertStringContainsString( 'aria-describedby="tec-recurrence-lock-' . $post->ID . '"', $html );
			$this->assertStringContainsString( 'role="tooltip" id="tec-recurrence-lock-' . $post->ID . '"', $html );
			$this->assertStringContainsString( 'Existing scheduled dates are preserved.', $html );
			$this->assertStringNotContainsString( 'Recurrence locked ·', $html );
		} finally {
			$GLOBALS['current_screen'] = $screen;
		}
	}
}
