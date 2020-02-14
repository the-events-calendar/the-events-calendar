<?php
/**
 * Handles compatibility with Beaver Builder plugin.
 *
 * @package Tribe\Events\Integrations
 * @since 5.0.2
 */
namespace Tribe\Events\Integrations;

use Tribe\Events\Views\V2\Template\Page;

/**
 * Integrations with Beaver Builder plugin.
 *
 * @package Tribe\Events\Integrations
 *
 * @since TBD
 */
class Beaver_Builder {

	/**
	 * Hooks all the required methods for Beaver_Builder usage on our code.
	 *
	 * @since TBD
	 */
	public function hook() {
		// Bail when not on V2
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		add_filter( 'fl_theme_builder_before_render_content', [ $this, 'action_restore_post' ] );
	}

	/**
	 * Restore main post for Beaver Builder plugin.
	 *ww
	 * @since TBD
	 *
	 * @param int $post_id Items to be excluded by WP Rocket.
	 *
	 * @return void
	 */
	public function action_restore_post( $post_id ) {
		/* @var Page $page_template */
		$page_template = tribe( Page::class );

		// Bail when not using page template.
		if ( ! $page_template->has_hijacked_posts() ) {
			return;
		}

		$page_template->restore_main_query();
	}
}
