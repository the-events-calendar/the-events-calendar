<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Editor as Editor;
use WP_Post;

class Editor_ConversionTest extends WPTestCase {
	/**
	 * @before
	 */
	public function activate_recurrence_engine(): void {
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );
		// After the blocks-toggle Compatibility filter (100): the toggle is off in the test database.
		add_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
		tribe()->setVar( 'ct1_fully_activated', true );
		// The WordPress test case restores the hooks state after each test: force a re-registration.
		tribe()->setVar( Controller::class . '_registered', false );
		tribe( Controller::class )->register();
		// Reset the Model static extensions cache: it may have been locked before the engine registered.
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	/**
	 * @after
	 */
	public function reset_registration_state(): void {
		unset( $_GET['post'] );
		remove_all_filters( 'tec_events_recurrence_enabled' );
		remove_all_filters( 'tribe_editor_should_load_blocks' );
		// Symmetric cleanup: no engine hook or extended model state leaks into other tests.
		tribe( Controller::class )->unregister();
		tribe()->setVar( Controller::class . '_registered', false );
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	private function given_a_classic_content_event(): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Editor Conversion Test Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		wp_update_post(
			[
				'ID'           => $post->ID,
				'post_content' => 'A classic, block-less event description.',
			]
		);

		$this->assertFalse( has_blocks( $post->ID ) );

		return get_post( $post->ID );
	}

	/**
	 * It should retry the blocks conversion of a flagged post that still has no blocks
	 *
	 * An interrupted first load flags `_tribe_is_classic_editor` without rewriting the
	 * content; before the retry, that state kept the event without the datetime block
	 * (and its Event Dates section) forever.
	 *
	 * @test
	 */
	public function should_retry_the_blocks_conversion_of_a_flagged_post_without_blocks(): void {
		$post = $this->given_a_classic_content_event();

		// Simulate the torn state: flagged, yet never converted.
		update_post_meta( $post->ID, tribe( 'editor' )->key_flag_classic_editor, 1 );

		$_GET['post'] = $post->ID;
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->assertTrue( (bool) ( new Editor() )->flag_post_from_classic_editor() );

		$this->assertTrue( has_blocks( $post->ID ) );
		$content = get_post( $post->ID )->post_content;
		$this->assertStringContainsString( 'wp:tribe/event-datetime', $content );
		$this->assertStringContainsString( 'A classic, block-less event description.', $content );
		// The flag is not duplicated by the retry.
		$this->assertCount( 1, get_post_meta( $post->ID, tribe( 'editor' )->key_flag_classic_editor ) );
	}

	/**
	 * It should preserve a rule-locked event rset and occurrences through the conversion
	 *
	 * @test
	 */
	public function should_preserve_a_rule_locked_event_through_the_conversion(): void {
		$post = $this->given_a_classic_content_event();
		$rset = "DTSTART;TZID=America/Sao_Paulo:20261105T090000\nRRULE:FREQ=WEEKLY;COUNT=10";

		Event::find( $post->ID, 'post_id' )->update( [ 'rset' => $rset ] );
		update_post_meta( $post->ID, tribe( 'editor' )->key_flag_classic_editor, 1 );

		$occurrences_before = Occurrence::where( 'post_id', '=', $post->ID )->count();

		$_GET['post'] = $post->ID;
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->assertTrue( (bool) ( new Editor() )->flag_post_from_classic_editor() );
		$this->assertTrue( has_blocks( $post->ID ) );

		// The conversion save must not touch the rule data or its generated Occurrences.
		$this->assertEquals( $rset, (string) Event::find( $post->ID, 'post_id' )->rset );
		$this->assertEquals( $occurrences_before, Occurrence::where( 'post_id', '=', $post->ID )->count() );
	}

	/**
	 * It should not flag nor convert a post that already has blocks
	 *
	 * @test
	 */
	public function should_not_convert_a_post_that_already_has_blocks(): void {
		$post = $this->given_a_classic_content_event();

		wp_update_post(
			[
				'ID'           => $post->ID,
				'post_content' => "<!-- wp:tribe/event-datetime  /-->\n<!-- wp:paragraph --><p>Blocks already.</p><!-- /wp:paragraph -->",
			]
		);

		$_GET['post'] = $post->ID;

		$this->assertFalse( ( new Editor() )->flag_post_from_classic_editor() );
		$this->assertEmpty( get_post_meta( $post->ID, tribe( 'editor' )->key_flag_classic_editor ) );
	}
}
