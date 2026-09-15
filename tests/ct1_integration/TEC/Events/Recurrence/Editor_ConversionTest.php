<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe__Events__Editor as Editor;
use WP_Post;

class Editor_ConversionTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * @before
	 */
	public function force_blocks_editor(): void {
		// After the blocks-toggle Compatibility filter (100): the toggle is off in the test database.
		add_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
	}

	/**
	 * @after
	 */
	public function reset_request_state(): void {
		unset( $_GET['post'] );
		remove_all_filters( 'tribe_editor_should_load_blocks' );
	}

	private function given_a_classic_content_event(): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Editor Conversion Test Event',
				'status'     => 'publish',
				'start_date' => '2050-01-05 09:00:00',
				'end_date'   => '2050-01-05 10:00:00',
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
		$rset = "DTSTART;TZID=America/Sao_Paulo:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=10";

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

	/**
	 * It should not rewrite a flagged post when the conversion produces no blocks
	 *
	 * A site filtering the converted content to something block-less would otherwise get
	 * the post rewritten, and a revision spawned, on every edit screen load.
	 *
	 * @test
	 */
	public function should_not_rewrite_a_flagged_post_when_the_conversion_produces_no_blocks(): void {
		$post = $this->given_a_classic_content_event();
		update_post_meta( $post->ID, tribe( 'editor' )->key_flag_classic_editor, 1 );
		$modified_before = get_post( $post->ID )->post_modified_gmt;

		add_filter( 'tribe_blocks_editor_update_classic_content', '__return_empty_string' );

		$_GET['post'] = $post->ID;
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		try {
			$this->assertFalse( ( new Editor() )->flag_post_from_classic_editor() );
		} finally {
			remove_filter( 'tribe_blocks_editor_update_classic_content', '__return_empty_string' );
		}

		$this->assertFalse( has_blocks( $post->ID ) );
		$this->assertEquals( 'A classic, block-less event description.', get_post( $post->ID )->post_content );
		$this->assertEquals( $modified_before, get_post( $post->ID )->post_modified_gmt );
		// The attempt was counted.
		$this->assertEquals( 2, (int) get_post_meta( $post->ID, tribe( 'editor' )->key_flag_classic_editor, true ) );
	}

	/**
	 * It should stop retrying the conversion after the maximum number of attempts
	 *
	 * @test
	 */
	public function should_stop_retrying_the_conversion_after_the_maximum_attempts(): void {
		$post = $this->given_a_classic_content_event();
		update_post_meta( $post->ID, tribe( 'editor' )->key_flag_classic_editor, Editor::MAX_CONVERSION_ATTEMPTS );

		$_GET['post'] = $post->ID;
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->assertFalse( ( new Editor() )->flag_post_from_classic_editor() );

		$this->assertFalse( has_blocks( $post->ID ) );
		$this->assertEquals( Editor::MAX_CONVERSION_ATTEMPTS, (int) get_post_meta( $post->ID, tribe( 'editor' )->key_flag_classic_editor, true ) );
	}
}
