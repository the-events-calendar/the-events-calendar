<?php
/**
 * WordPress SEO (Yoast) Pagination Integration for The Events Calendar.
 *
 * This integration disables Yoast SEO's `rel="next"` and `rel="prev"` meta tags
 * on Events Calendar (TEC) views to prevent invalid pagination links from being
 * output in the document head. These tags can cause SEO crawlers to index
 * non-existent calendar pages.
 *
 * Since TEC's pagination context is not available early enough in the page
 * lifecycle to override Yoast’s links safely, this integration simply disables
 * them on event-related queries.
 *
 * @since 6.15.9
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

/**
 * Class Events_Pagination
 *
 * Disables Yoast SEO pagination meta tags (`rel="next"` and `rel="prev"`)
 * when viewing Events Calendar pages.
 *
 * @since 6.15.9
 */
class Events_Pagination {

	/**
	 * Registers the hook to disable Yoast pagination meta tags.
	 *
	 * Hooks into `template_redirect` early enough in the WordPress lifecycle
	 * to ensure Yoast’s filters are applied before its `wp_head` output runs.
	 *
	 * @since 6.15.9
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'template_redirect', [ $this, 'disable_yoast_pagination' ], 5 );
	}

	/**
	 * Disables Yoast's rel=next/prev meta tags for Events Calendar views.
	 *
	 * Runs during `template_redirect` once the main query is known. If the
	 * current page is an Events Calendar view, Yoast's pagination meta tags
	 * are disabled using the `wpseo_next_rel_link` and `wpseo_prev_rel_link`
	 * filters.
	 *
	 * @since 6.15.9
	 *
	 * @return void
	 */
	public function disable_yoast_pagination(): void {
		// Bail if this is not an Events Calendar query.
		if ( ! function_exists( 'tribe_is_event_query' ) || ! tribe_is_event_query() ) {
			return;
		}

		// Disable Yoast's pagination meta tags for TEC pages.
		add_filter( 'wpseo_next_rel_link', '__return_false' );
		add_filter( 'wpseo_prev_rel_link', '__return_false' );
	}
}
