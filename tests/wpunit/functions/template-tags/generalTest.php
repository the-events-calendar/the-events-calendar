<?php
namespace TEC\Test\functions\template_tags;

use Codeception\TestCase\WPTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Test\Factories\Event;
use Tribe__Events__Timezones as Timezones;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Events\Test\Traits\With_Uopz;

class generalTest extends WPTestCase {

	use MatchesSnapshots;
	use With_Post_Remapping;
	use With_Uopz;

	protected $content_w_blocks = <<< HTML
<!-- wp:tribe/event-datetime /-->

<!-- wp:paragraph -->
<p>Before embed. </p>
<!-- /wp:paragraph -->

<!-- wp:core-embed/vimeo {"url":"https://vimeo.com/346787418","type":"video","providerNameSlug":"vimeo","className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed-vimeo wp-block-embed is-type-video is-provider-vimeo wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
https://vimeo.com/346787418
</div></figure>
<!-- /wp:core-embed/vimeo -->

<!-- wp:shortcode -->
[ embed width="123" height="456"]http://www.youtube.com/watch?v=dQw4w9WgXcQ[/embed]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p>After embed. </p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>After embed 2. </p>
<!-- /wp:paragraph -->

<!-- wp:tribe/event-price /-->

<!-- wp:tribe/event-organizer /-->

<!-- wp:tribe/event-venue /-->

<!-- wp:tribe/event-website /-->

<!-- wp:tribe/event-links /-->

<!-- wp:tribe/related-events /-->
HTML;

	public static function setUpBeforeClass(  ) {
		parent::setUpBeforeClass();
		static::factory()->event = new Event();
	}

	public function separated_field_inputs() {
		return [
			[ '', ' | ', 'Hello', 'Hello' ],
			[ 'Something', ' | ', 'Hello', 'Something | Hello' ],
			[ 'Something', ' | ', '', 'Something' ],
			[ '', '', '', '' ],
			[ 'Something', '', '', 'Something' ],
			[ 'Something', '', 'Hello', 'SomethingHello' ],
		];
	}

	/**
	 * @dataProvider separated_field_inputs
	 */
	public function test_tribe_separated_field( $body, $sep, $field, $expected ) {
		$this->assertEquals( $expected, tribe_separated_field( $body, $sep, $field ) );
	}

	/**
	 * It should remove blocks from excerpt correctly
	 *
	 * @test
	 */
	public function should_remove_blocks_from_excerpt_correctly() {
		add_filter( 'tribe_events_excerpt_blocks_removal', '__return_true' );
		// Ensure the excerpt is empty to avoid auto-filling by the factory.
		$event = static::factory()->event->create( [ 'post_content' => $this->content_w_blocks, 'post_excerpt' => '' ] );

		$excerpt_wo_blocks = "<p>Before embed. After embed. After embed 2.</p>\n";

		$this->assertEquals( $excerpt_wo_blocks, tribe_events_get_the_excerpt( $event ) );

	}

	/**
	 * It should correctly render excerpt w/ blocks
	 *
	 * @test
	 */
	public function should_correctly_render_excerpt_w_blocks() {
		add_filter( 'tribe_events_excerpt_blocks_removal', '__return_false' );
		// Ensure the excerpt is empty to avoid auto-filling by the factory.
		$event = static::factory()->event->create( [ 'post_content' => $this->content_w_blocks, 'post_excerpt' => '' ] );

		$excerpt_w_blocks = "<p>Before embed. https://vimeo.com/346787418 http://www.youtube.com/watch?v=dQw4w9WgXcQ After embed. After embed 2.</p>\n";

		$this->assertEquals( $excerpt_w_blocks, tribe_events_get_the_excerpt( $event ) );
	}

	public function is_past_event_data_provider() {
		return [
			'tomorrow event mode=site tz=UTC+0'                                => [
				false,
				Timezones::SITE_TIMEZONE,
				[ 'when' => 'tomorrow 8am' ],
				'UTC+0'
			],
			'today event mode=site tz=UTC+0'                                   => [
				false,
				Timezones::SITE_TIMEZONE,
				[ 'when' => 'today 8am', 'duration' => DAY_IN_SECONDS ],
				'UTC+0'
			],
			'yesterday event mode=site tz=UTC+0'                               => [
				true,
				Timezones::SITE_TIMEZONE,
				[ 'when' => 'yesterday 8am', 'duration' => HOUR_IN_SECONDS ],
				'UTC+0'
			],
			'tomorrow event mode=event event_tz=America/Los_Angeles tz=UTC+0'  => [
				false,
				Timezones::EVENT_TIMEZONE,
				[ 'when' => 'tomorrow 8am', 'timezone' => 'America/Los_Angeles' ],
				'UTC+0'
			],
			'today event mode=event event_tz=America/Los_Angeles tz=UTC+0'     => [
				false,
				Timezones::EVENT_TIMEZONE,
				[ 'when' => 'today 8am', 'duration' => DAY_IN_SECONDS, 'timezone' => 'America/Los_Angeles' ],
				'UTC+0'
			],
			'yesterday event mode=event event_tz=America/Los_Angeles tz=UTC+0' => [
				true,
				Timezones::EVENT_TIMEZONE,
				[ 'when' => 'yesterday 8am', 'duration' => HOUR_IN_SECONDS, 'timezone' => 'America/Los_Angeles' ],
				'UTC+0'
			],
		];
	}

	/**
	 * @dataProvider is_past_event_data_provider
	 */
	public function test_is_past_event( $expected, $timezone_mode, $event_overrides, $site_timezone = null ) {
		tribe_update_option( 'tribe_events_timezone_mode', $timezone_mode );
		if ( null !== $site_timezone ) {
			update_option( 'timezone_string', $site_timezone );
		}
		$event = static::factory()->event->create_and_get( $event_overrides );
		$this->assertEquals( $expected, tribe_is_past_event( $event ) );
	}

	/**
	 * @test
	 */
	public function test_empty_event_tag_archive_link( ) {
		$html = tribe_meta_event_archive_tags( null, ', ', false );

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 */
	public function test_empty_wp_tag_archive_link( ) {
		$html = tribe_meta_event_tags( null, ', ', false );

		$this->assertMatchesSnapshot( $html );
	}

	public function tag_archive_provider() {
		return [
			[
				'Event Tags:',
				', ',
			],
			[
				'Class Tags:',
				'# ',
			],
			[
				'',
				'! ',
			],
		];
	}

	/**
	 * @test
	 * @dataProvider tag_archive_provider
	 */
	public function test_event_tag_archive_link( $label, $separator ) {
		$this->set_permalinks();
		$tag        = $this->factory()->tag->create( [ 'slug' => 'tag-1' ] );
		$tag_2      = $this->factory()->tag->create( [ 'slug' => 'tag-2' ] );
		$tag_term   = get_term( $tag, 'post_tag' );
		$tag_term_2 = get_term( $tag_2, 'post_tag' );

		$event = tribe_events()->set_args( [
			'start_date' => 'tomorrow 9am',
			'timezone'   => 'America/New_York',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'title'      => 'Tag Event',
			'status'     => 'publish',
			'tag'        => [ $tag, $tag_2 ],
		] )->create();
		$this->uopz_set_return( 'get_the_ID', $event->ID );

		// Added manually addition of the taxonomies as the above coding was not adding them.
		wp_set_object_terms( $event->ID, [ $tag_term->slug, $tag_term_2->slug ], 'post_tag', false );

		$html = tribe_meta_event_archive_tags( $label, $separator, false );

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 * @dataProvider tag_archive_provider
	 */
	public function test_wp_tag_archive_link( $label, $separator ) {
		$this->set_permalinks();
		$tag        = $this->factory()->tag->create( [ 'slug' => 'tag-1' ] );
		$tag_2      = $this->factory()->tag->create( [ 'slug' => 'tag-2' ] );
		$tag_term   = get_term( $tag, 'post_tag' );
		$tag_term_2 = get_term( $tag_2, 'post_tag' );

		$event = tribe_events()->set_args( [
			'start_date' => 'tomorrow 9am',
			'timezone'   => 'America/New_York',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'title'      => 'Tag Event',
			'status'     => 'publish',
			'tag'        => [ $tag, $tag_2 ],
		] )->create();
		$this->uopz_set_return( 'get_the_ID', $event->ID );

		// Added manually addition of the taxonomies as the above coding was not adding them.
		wp_set_object_terms( $event->ID, [ $tag_term->slug, $tag_term_2->slug ], 'post_tag', false );

		$html = tribe_meta_event_tags( $label, $separator, false );

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 * @dataProvider tag_archive_provider
	 */
	public function test_with_wp_filter_tag_archive_link( $label, $separator ) {
		$this->set_permalinks();
		$tag        = $this->factory()->tag->create( [ 'slug' => 'tag-1' ] );
		$tag_2      = $this->factory()->tag->create( [ 'slug' => 'tag-2' ] );
		$tag_term   = get_term( $tag, 'post_tag' );
		$tag_term_2 = get_term( $tag_2, 'post_tag' );

		$event = tribe_events()->set_args( [
			'start_date' => 'tomorrow 9am',
			'timezone'   => 'America/New_York',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'title'      => 'Tag Event',
			'status'     => 'publish',
			'tag'        => [ $tag, $tag_2 ],
		] )->create();
		$this->uopz_set_return( 'get_the_ID', $event->ID );

		// Added manually addition of the taxonomies as the above coding was not adding them.
		wp_set_object_terms( $event->ID, [ $tag_term->slug, $tag_term_2->slug ], 'post_tag', false );

		add_filter( 'tec_events_use_wordpress_tag_archive_url', '__return_true' );

		$html = tribe_meta_event_archive_tags( $label, $separator, false );

		$this->assertMatchesSnapshot( $html );
	}

	protected function set_permalinks() {
		/** @var \WP_Rewrite */
		global $wp_rewrite;
		$structure = '/%postname%/';
		$wp_rewrite->set_permalink_structure( $structure );
		update_option( 'permalink_structure', $structure );
		$wp_rewrite->init();
		$wp_rewrite->flush_rules( true );
	}
}
