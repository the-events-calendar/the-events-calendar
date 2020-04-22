<?php
/**
 * Provides methods for a Views that supports fast-forward links.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 */

namespace Tribe\Events\Views\V2\Views\Traits;

use Tribe\Events\Views\V2\View_Interface;
use Tribe__Date_Utils as Dates;

/**
 * Class With_Fast_Forward_Link
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 */
trait With_Fast_Forward_Link {
	/**
	 * Creates a HTML link and "fast forward" message to append to the "no events found" message.
	 *
	 * @since 5.1.0
	 *
	 * @param bool  $canonical         Whether to return the canonical (pretty) version of the URL or not.
	 * @param array $passthru_vars     An optional array of query variables that should pass thru the method untouched
	 *                                 in key and value.
	 *
	 * @return string                  The html link and message.
	 */
	public function get_fast_forward_link( $canonical = false, array $passthru_vars = [] ) {
		if ( $this->use_ff_link( $canonical, $passthru_vars ) ) {
			return '';
		}

		$date      = $this->context->get( 'event_date', $this->context->get( 'today' ) );
		$cache_key = __METHOD__ . '_' . md5( wp_json_encode( array_merge( [ $date, $canonical ], $passthru_vars ) ) );

		if ( isset( $this->cached_urls[ $cache_key ] ) ) {
			return $this->cached_urls[ $cache_key ];
		}

		$next_event = tribe_events()->where( 'starts_after', $date )->per_page( 1 )->first();

		if ( ! $next_event instanceof \WP_Post ) {
			return '';
		}

		$url_date = Dates::build_date_object( $next_event->start_date );
		$url      = $this->build_url_for_date( $url_date, $canonical, $passthru_vars );

		$link = sprintf(
		/* translators: 1: opening href tag 2: closing href tag */
			__( 'Jump to the %1$snext upcoming event(s)%2$s.', 'the-events-calendar' ),
			'<a href="' . esc_url( $url ) . '" class="tribe-events-c-messages__message-list-item-link tribe-common-anchor-thin-alt" data-js="tribe-events-view-link">',
			'</a>'
		);

		$this->cached_urls[ $cache_key ] = $link;

		return $link;
	}

	/**
	 * Whether to use the fast-forward link in the View or not.
	 *
	 * @since TBD
	 *
	 * @param bool  $canonical     Whether to return the canonical, pretty, version of the link or not.
	 * @param array $passthru_vars A set of query vars to just passthru and not process as part of the canonical link
	 *                             resolution.
	 *
	 * @return bool Whether the View should use canonical links or not.
	 */
	public function use_ff_link( $canonical = false, array $passthru_vars = [] ) {
		/**
		 * Filters whether the fast-forward link should be used in Views or not whenever possible.
		 *
		 * @since TBD
		 *
		 * @param bool           $use_ff_link   Whether to use the fast-forward link in Views or not.
		 * @param bool           $canonical     Whether to return the canonical, pretty, version of the link or not.
		 * @param array          $passthru_vars A set of query vars to just passthru and not process as part of the
		 *                                      canonical link  resolution.
		 * @param View_Interface $this          The View currently rendering.
		 */
		$use_ff_link = apply_filters( 'tribe_events_views_v2_use_ff_link', true, $canonical, $passthru_vars, $this );

		/**
		 * Filters whether the fast-forward link should be used for this specific View or not whenever possible.
		 *
		 * @since TBD
		 *
		 * @param bool           $use_ff_link   Whether to use the fast-forward link in Views or not.
		 * @param bool           $canonical     Whether to return the canonical, pretty, version of the link or not.
		 * @param array          $passthru_vars A set of query vars to just passthru and not process as part of the
		 *                                      canonical link  resolution.
		 * @param View_Interface $this          The View currently rendering.
		 */
		$use_ff_link = apply_filters(
			"tribe_events_views_v2_{$this->slug}_use_ff_link",
			$use_ff_link,
			$passthru_vars,
			$this
		);

		return tribe_is_truthy( $use_ff_link );
	}
}
