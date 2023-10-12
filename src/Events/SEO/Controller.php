<?php
/**
 * Manages the legacy view removal and messaging.
 *
 * @since 6.2.3
 *
 * @package TEC\Events\SEO
 */

 namespace TEC\Events\SEO;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use \Tribe__Date_Utils as Dates;
use Tribe__Repository;


/**
 * Class Provider
 *
 * @since 6.2.3

 * @package TEC\Events\SEO
 */
class Controller extends Controller_Contract {
	public function do_register(): void {
		$this->container->singleton( static::class, $this );

		add_action( 'get_header', [ $this, 'issue_noindex' ] );
	}

	public function unregister(): void {
		remove_action( 'get_header', [ $this, 'issue_noindex' ] );
	}

	/**
	 * Runs on the "wp" action. Inspects the main query object and if it relates to an events
	 * query makes a decision to add a noindex meta tag based on whether events were returned
	 * in the query results or not.
	 *
	 * @since 3.12.4
	 * @since 6.0.0 Relies on v2 code.
	 *
	 * Disabling this behavior completely is possible with:
	 *
	 *     add_filter( 'tec_events_add_no_index_meta_tag', '__return_false' );
	 *
	 *  Always adding the noindex meta tag for all event views is possible with:
	 *
	 *     add_filter( 'tribe_events_add_no_index_meta', '__return_true' );
	 *
	 *  Always adding the noindex meta tag for a specific event view is possible with:
	 *
	 *     add_filter( "tribe_events_{$view}_add_no_index_meta", '__return_true' );
	 *
	 *  Where `$view` above is the view slug, e.g. `month`, `day`, `list`, etc.
	 */
	public function issue_noindex(): void {
		global $wp_query;

		/**
		 * Allows filtering of if a noindex meta tag will be set for the current event view.
		 *
		 * @since 6.2.3
		 *
		 * @var bool $do_noindex_meta Whether to add the noindex meta tag.
		 */
		$do_noindex_meta = apply_filters( 'tec_events_add_no_index_meta_tag', true );

		if ( ! tribe_is_truthy( $do_noindex_meta ) ) {
			return;
		}

		if ( is_home() || is_front_page() || is_single() ) {
			return;
		}

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		$context = tribe_context();

		if ( ! $context->is( 'tec_post_type' ) ) {
			return;
		}

		$view = $context->get( 'view' );

		$start_date = ! empty( $wp_query->query[ 'eventDate' ] ) ? $wp_query->get( 'eventDate' ) : $context->get( 'event_date' );
		$start_date = Dates::build_date_object( $start_date );

		$end_date = $this->get_end_date( $view, $start_date, $context );

		// Prevent issues with invalid dates.
		if ( false === $start_date || false === $end_date ) {
			return;
		}

		$events = tribe_events();

		/**
		 * Allow specific views to hook in and add their own calculated events.
		 * This *bypasses* the cached query immediately after it.
		 *
		 * @since 6.2.3
		 *
		 * @param ?Tribe__Repository|null $events     The events repository. False if not hooked into.
		 * @param DateTime                $start_date The start date (object) of the query.
		 * @param Tribe__Context          $context    The current context.
		 *
		 */
		$events = apply_filters( 'tec_events_noindex', $events, $start_date, $end_date, $context );

		// If nothing has hooked in ($events is null|false, we do a quick query for a single event after the start date.
		if (
			empty( $events )
			|| (
				$events instanceof Tribe__Repository
				&& $events->count() === 0
			)
		) {
			$query_start = $start_date->format( Dates::DBDATEFORMAT );
			$query_end   = $end_date->format( Dates::DBDATEFORMAT );

			if ( $start_date == $end_date )  {
				$events = tribe_events()->per_page( 1 )->where( 'ends_after', $query_start )->fields( 'ids' );
			} else {
				$events = tribe_events()->per_page( 1 )->where( 'ends_after', $query_start )->where( 'starts_before', $query_end )->fields( 'ids' );
			}
		}

		// No posts = no index.
		$count = $events->count();
		$add_noindex = $count <= 0;

		/**
		 * Determines if a noindex meta tag will be set for the current event view.
		 *
		 * @since  3.12.4
		 *
		 * @var bool $add_noindex
		 * @var Tribe__Context $context The view context.
		 */
		$add_noindex = apply_filters( 'tribe_events_add_no_index_meta', $add_noindex, $context );

		/**
		 * Determines if a noindex meta tag will be set for a specific event view.
		 *
		 * @since 6.2.3
		 *
		 * @var bool $add_noindex
		 * @var Tribe__Context $context The view context.
		 */
		$add_noindex = apply_filters( "tec_events_{$view}_add_no_index_meta", $add_noindex, $context );

		if ( $add_noindex ) {
			add_action( 'wp_head', [ $this, 'print_noindex_meta' ] );
		}
	}

	/**
	 * Prints a "noindex,follow" robots tag.
	 *
	 * @since 6.2.3
	 */
	public function print_noindex_meta() :void {
		$noindex_meta = ' <meta name="robots" id="tec_noindex" content="noindex, follow" />' . "\n";

		/**
		 * Filters the noindex meta tag.
		 *
		 * @since 6.2.3
		 *
		 * @param string $noindex_meta
		 */
		$noindex_meta = apply_filters( 'tec_events_no_index_meta', $noindex_meta );

		echo wp_kses(
			$noindex_meta,
			[
				'meta' => [
					'id'      => true,
					'name'    => true,
					'content' => true,
				],
			]
		);
	}

	/**
	 * Returns the end date time object read from the current context.
	 *
	 * @since 6.2.3
	 *
	 * @param [type] $view
	 * @param [type] $start_date
	 * @param [type] $context
	 *
	 * @return DateTime|false A DateTime object or `false` if a DateTime object could not be built.
	 */
	public function get_end_date( $view, $start_date, $context ) {
		$end_date = $context->get( 'end_date' );

		switch ( $view ) {
			case 'day':
				$end_date = clone $start_date;
				$end_date->modify( '+1 day' );
				return $end_date;
				break;
			case 'week':
				$end_date = clone $start_date;
				$end_date->modify( '+6 days' );
				return $end_date;
				break;
			case 'month':
				$end_date = clone $start_date;
				$end_date->modify( '+1 month' );
				return $end_date;
				break;
			default:
				return Dates::build_date_object( $end_date );
				break;
		}
	}
}
