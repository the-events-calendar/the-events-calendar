<?php

namespace TEC\Events\Blocks;

use Tribe\Events\Test\Factories\Event;
use Tribe\Test\Products\WPBrowser\Views\V2\TestCase;
use Tribe\Utils\Body_Classes;
use Tribe__Events__Editor__Provider;
use Tribe__Events__Editor__Template;

class Rendering_Test extends TestCase {

	private $original_theme;
	private $bindings = [];
	private $body_classes;

	public function setUp(): void {
		parent::setUp();
		// The base keeps a context reference; navigation must not mutate that backup.
		tribe()->singleton( 'context', clone tribe_context() );
		$this->body_classes = clone tribe( Body_Classes::class );
		$this->original_theme = get_stylesheet();
		foreach ( [ 'events.editor.template', 'events.editor.template.overwrite' ] as $id ) {
			$this->bindings[ $id ] = tribe()->isBound( $id ) ? tribe( $id ) : null;
			tribe()->offsetUnset( $id );
		}

		// Remove the previous editor setup to reproduce a fresh classic-editor request.
		$overwrite = $this->bindings['events.editor.template.overwrite'];
		if ( $overwrite ) {
			remove_filter( 'tribe_events_template_single-event.php', [ $overwrite, 'silence' ] );
			remove_action( 'tribe_events_before_view', [ $overwrite, 'include_blocks' ], 1 );
		}
		switch_theme( 'twentytwenty' );
		add_filter( 'tribe_editor_should_load_blocks', '__return_false', PHP_INT_MAX );
	}

	public function tearDown(): void {
		tribe()->singleton( Body_Classes::class, $this->body_classes );
		foreach ( $this->bindings as $id => $binding ) {
			tribe()->offsetUnset( $id );
			if ( null !== $binding ) {
				tribe()->singleton( $id, $binding );
			}
		}
		remove_filter( 'tribe_editor_should_load_blocks', '__return_false', PHP_INT_MAX );
		switch_theme( $this->original_theme );
		parent::tearDown();
	}

	public function test_disabling_the_editor_keeps_rendering_available_without_template_overrides() {
		tribe( Tribe__Events__Editor__Provider::class )->register();

		$this->assertTrue( tribe()->isBound( 'events.editor.template' ) );
		$this->assertInstanceOf( Tribe__Events__Editor__Template::class, tribe( 'events.editor.template' ) );
		$this->assertFalse( tribe()->isBound( 'events.editor.template.overwrite' ) );
		$this->assertFalse( tribe( 'editor' )->should_load_blocks() );
	}

	public function content_cases() {
		$blocks = [
			'plain'   => '',
			'single'  => '<!-- wp:tec/single-event /-->',
			'archive' => '<!-- wp:tec/archive-events /-->',
			'nested'  => '<!-- wp:group --><div class="wp-block-group"><!-- wp:tec/single-event /--></div><!-- /wp:group -->',
		];
		$cases = [];
		foreach ( $blocks as $name => $block ) {
			$cases[ $name . ' helper' ]  = [ $block, 'helper' ];
			$cases[ $name . ' classic' ] = [ $block, 'classic' ];
		}

		return $cases;
	}

	/**
	 * @dataProvider content_cases
	 */
	public function test_event_descriptions_do_not_render_whole_event_views( $block, $renderer ) {
		// Supply the real dependency to isolate recursion from the registration regression above.
		tribe()->singleton( 'events.editor.template', Tribe__Events__Editor__Template::class );
		$event_id = ( new Event() )->create(
			[
				'post_content' => '<p>Before the block.</p>' . $block . '<p>After the block.</p>',
			]
		);
		$this->go_to( get_permalink( $event_id ) );
		tribe_context()->refresh();

		// Fail before an unexpected full-view render can recursively exhaust memory.
		$guard = static function () {
			throw new \RuntimeException( 'A full event view was rendered inside an event description.' );
		};
		add_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', $guard );
		$buffer_level = ob_get_level();
		try {
			if ( 'helper' === $renderer ) {
				$content = tribe_get_the_content( null, false, $event_id );
			} else {
				ob_start();
				the_content();
				$content = ob_get_clean();
			}
			$this->assertSame( 1, substr_count( $content, 'Before the block.' ) );
			$this->assertSame( 1, substr_count( $content, 'After the block.' ) );
			$this->assertNotContains( 'tec-block__', $content );
		} finally {
			while ( ob_get_level() > $buffer_level ) {
				ob_end_clean();
			}
			remove_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', $guard );
		}
	}

	public function template_cases() {
		return [
			'classic single'  => [ 'twentytwenty', 'single-event' ],
			'classic archive' => [ 'twentytwenty', 'archive-events' ],
			'block single'    => [ 'twentytwentyfour', 'single-event' ],
			'block archive'   => [ 'twentytwentyfour', 'archive-events' ],
		];
	}

	/**
	 * @dataProvider template_cases
	 */
	public function test_template_blocks_still_render_event_content( $theme, $slug ) {
		switch_theme( $theme );
		tribe( Tribe__Events__Editor__Provider::class )->register();
		$event_id = ( new Event() )->create(
			[
				'post_title'   => 'Template rendering control',
				'post_content' => '<p>Event description control.</p><!-- wp:tec/' . $slug . ' /-->',
			]
		);
		$this->go_to( get_permalink( $event_id ) );
		tribe_context()->refresh();

		$view_calls = 0;
		// Keep a recursion regression from exhausting memory while rendering the real template.
		$guard = static function ( $html ) use ( &$view_calls ) {
			if ( ++$view_calls > 1 ) {
				throw new \RuntimeException( 'The event template recursively rendered its description.' );
			}
			return $html;
		};
		add_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', $guard );
		$buffer_level = ob_get_level();
		try {
			$content = do_blocks( '<!-- wp:tec/' . $slug . ' /-->' );
			$this->assertContains( 'tec-block__' . $slug, $content );
			$this->assertContains( 'Template rendering control', $content );
			$this->assertSame( 1, substr_count( $content, 'Event description control.' ) );
		} finally {
			while ( ob_get_level() > $buffer_level ) {
				ob_end_clean();
			}
			remove_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', $guard );
		}
	}

	public function test_archive_block_is_not_removed_from_regular_page_content() {
		tribe( Tribe__Events__Editor__Provider::class )->register();
		$page_id = static::factory()->post->create(
			[
				'post_type'    => 'page',
				'post_content' => '<!-- wp:tec/archive-events /-->',
			]
		);
		$this->go_to( get_permalink( $page_id ) );
		// Each navigation represents a fresh request, including TEC's cached query context.
		tribe_context()->refresh();

		$view_calls = 0;
		$guard = static function ( $html ) use ( &$view_calls ) {
			if ( ++$view_calls > 1 ) {
				throw new \RuntimeException( 'The archive block recursively rendered page content.' );
			}
			return $html;
		};
		add_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', $guard );
		$buffer_level = ob_get_level();
		try {
			$content = tribe_get_the_content( null, false, $page_id );

			$this->assertContains( 'tec-block__archive-events', $content );
			$this->assertContains( 'tribe-events-view', $content );
		} finally {
			while ( ob_get_level() > $buffer_level ) {
				ob_end_clean();
			}
			remove_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', $guard );
		}
	}
	public function block_slugs() {
		return [ [ 'single-event' ], [ 'archive-events' ] ];
	}

	/**
	 * @dataProvider block_slugs
	 */
	public function test_page_content_renders_when_the_global_post_is_an_event( $slug ) {
		tribe( Tribe__Events__Editor__Provider::class )->register();
		$page_id = static::factory()->post->create(
			[ 'post_type' => 'page', 'post_content' => '<!-- wp:tec/' . $slug . ' /-->' ]
		);
		$event_id = ( new Event() )->create();
		$this->go_to( get_permalink( $page_id ) );
		tribe_context()->refresh();
		$GLOBALS['post'] = get_post( $event_id );

		// Third parties can filter another post's content without changing the global post.
		$content = apply_filters( 'the_content', get_post( $page_id )->post_content );

		$this->assertContains( 'tec-block__' . $slug, $content );
		$this->assertContains( 'tribe-events-view', $content );
	}

	/**
	 * @dataProvider block_slugs
	 */
	public function test_event_description_does_not_render_a_single_view_when_the_global_post_is_a_page( $slug ) {
		tribe( Tribe__Events__Editor__Provider::class )->register();
		$event_id = ( new Event() )->create(
			[ 'post_content' => '<p>Before.</p><!-- wp:tec/' . $slug . ' /--><p>After.</p>' ]
		);
		$page_id = static::factory()->post->create( [ 'post_type' => 'page' ] );
		$this->go_to( get_permalink( $event_id ) );
		tribe_context()->refresh();
		$GLOBALS['post'] = get_post( $page_id );

		// Stop an incorrect full-view render before it can recursively exhaust memory.
		$guard = static function () {
			throw new \RuntimeException( 'A mismatched global post bypassed the recursion protection.' );
		};
		add_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', $guard );
		$buffer_level = ob_get_level();
		try {
			$content = apply_filters( 'the_content', get_post( $event_id )->post_content );
			$this->assertContains( 'Before.', $content );
			$this->assertContains( 'After.', $content );
			$this->assertNotContains( 'tec-block__', $content );
		} finally {
			while ( ob_get_level() > $buffer_level ) {
				ob_end_clean();
			}
			remove_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', $guard );
		}
	}

	public function test_archive_selected_by_filters_can_render_inside_event_content() {
		tribe( Tribe__Events__Editor__Provider::class )->register();
		$event_id = ( new Event() )->create(
			[ 'post_content' => '<!-- wp:tec/archive-events /-->' ]
		);
		$this->go_to( get_permalink( $event_id ) );
		tribe_context()->refresh();
		$list_view = static function () {
			return 'list';
		};
		add_filter( 'tribe_events_views_v2_bootstrap_should_display_single', '__return_false' );
		add_filter( 'tribe_events_views_v2_bootstrap_view_slug', $list_view );
		try {
			$content = tribe_get_the_content( null, false, $event_id );
			$this->assertContains( 'tec-block__archive-events', $content );
			$this->assertContains( 'tribe-events-view--list', $content );
		} finally {
			remove_filter( 'tribe_events_views_v2_bootstrap_should_display_single', '__return_false' );
			remove_filter( 'tribe_events_views_v2_bootstrap_view_slug', $list_view );
		}
	}

}
