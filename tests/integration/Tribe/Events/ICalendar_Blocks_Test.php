<?php

namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe__Events__Editor__Provider;
use Tribe__Events__iCal;
use WP_Block_Type_Registry;

class ICalendar_Blocks_Test extends WPTestCase {

	public function content_provider() {
		return [
			'plain content'        => [ '' ],
			'legacy block'         => [ '<!-- wp:tribe/event-datetime /-->' ],
			'single event block'   => [ '<!-- wp:tec/single-event /-->' ],
			'archive events block' => [ '<!-- wp:tec/archive-events /-->' ],
			'nested single event'  => [ '<!-- wp:group --><div class="wp-block-group"><!-- wp:tec/single-event /--></div><!-- /wp:group -->' ],
		];
	}

	/**
	 * @dataProvider content_provider
	 */
	public function test_feed_preserves_description_without_loading_editor_templates( $block_content ) {
		$container       = tribe();
		$template_backup = $container->isBound( 'events.editor.template' )
			? tribe( 'events.editor.template' )
			: null;
		$original_theme  = get_stylesheet();

		// Reproduce a classic theme with event blocks disabled, even if another test loaded the editor.
		switch_theme( 'twentytwenty' );
		add_filter( 'tribe_editor_should_load_blocks', '__return_false', PHP_INT_MAX );
		$container->offsetUnset( 'events.editor.template' );

		try {
			tribe( Tribe__Events__Editor__Provider::class )->register();
			// Export must not render event blocks, even if their rendering service is unavailable.
			$container->offsetUnset( 'events.editor.template' );
			$this->assertFalse( $container->isBound( 'events.editor.template' ) );
			$this->assertTrue( WP_Block_Type_Registry::get_instance()->is_registered( 'tec/single-event' ) );
			$this->assertTrue( WP_Block_Type_Registry::get_instance()->is_registered( 'tec/archive-events' ) );

			$event_id = ( new Event() )->create(
				[
					'post_title'   => 'Exported event',
					'post_content' => '<!-- wp:paragraph --><p>Before the block.</p><!-- /wp:paragraph -->'
						. $block_content
						. '<!-- wp:paragraph --><p>After the block.</p><!-- /wp:paragraph -->',
				]
			);

			$feed = ( new Tribe__Events__iCal() )->generate_ical_feed( $event_id, false );

			$this->assertContains( 'BEGIN:VCALENDAR', $feed );
			$this->assertSame( 1, substr_count( $feed, 'BEGIN:VEVENT' ) );
			$this->assertContains( 'SUMMARY:Exported event', $feed );
			$this->assertContains( 'DESCRIPTION:Before the block.', $feed );
			$this->assertContains( 'After the block.', $feed );
			$this->assertContains( 'END:VCALENDAR', $feed );
			$this->assertFalse( $container->isBound( 'events.editor.template' ) );
		} finally {
			remove_filter( 'tribe_editor_should_load_blocks', '__return_false', PHP_INT_MAX );
			if ( null !== $template_backup ) {
				$container->singleton( 'events.editor.template', $template_backup );
			}
			switch_theme( $original_theme );
		}
	}
}
