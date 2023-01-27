<?php
/**
 * Handles the manipulation of the template title to correctly render it in the context of a Views v2 request.
 *
 * @since   4.9.10
 *
 * @package Tribe\Events\Views\V2\Template
 */

namespace Tribe\Events\Views\V2\Template;

use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;

/**
 * Class Title
 *
 * @since   4.9.10
 *
 * @package Tribe\Events\Views\V2\Template
 */
class Title {

	/**
	 * The instance of the Context object that will be used to build the title, the global one otherwise.
	 *
	 * @since 4.9.10
	 *
	 * @var Context
	 */
	protected $context;
	/**
	 * An array of the events matching the query the title should be built for.
	 *
	 * @since 4.9.10
	 *
	 * @var array
	 */
	protected $posts;

	/**
	 * The plural Events label.
	 *
	 * @since 4.9.10
	 *
	 * @var string
	 */
	protected $events_label_plural;

	/**
	 * Title constructor.
	 *
	 * @since 4.9.10
	 */
	public function __construct() {
		$this->events_label_plural = tribe_get_event_label_plural();
	}

	/**
	 * Builds and returns the page title, to be used to filter the `wp_title` tag.
	 *
	 * @since 4.9.10
	 *
	 * @param string      $title The page title built so far.
	 * @param null|string $sep   The separator sequence to separate the title components.
	 * @param boolean     $depth Whether to display the taxonomy hierarchy as part of the title.
	 *
	 * @return string the filtered page title.
	 */
	public function filter_wp_title( $title, $sep = null, $depth = false ) {
		$new_title = $this->build_title( $title, $depth, $sep );

		/**
		 * Filters the page title built for event single or archive pages.
		 *
		 * @since 4.9.10
		 *
		 * @param string      $new_title The new title built for the page.
		 * @param string      $title     The original title.
		 * @param null|string $sep       The separator sequence to separate the title components.
		 */
		$the_title = apply_filters( 'tribe_events_title_tag', $new_title, $title, $sep, $depth );

		return $the_title;
	}

	/**
	 * Builds the page title from a context.
	 *
	 * This method is a rewrite of the `tribe_get_events_title` function to make it leverage the local context,
	 * injectable and controllable, in place of the global one.
	 *
	 * @since 4.9.10
	 * @since 5.1.5 - Add filter for plural events label and move featured label to a method.
	 *
	 * @param string      $current_title Current Title used on the page.
	 * @param boolean     $depth         Whether to display the taxonomy hierarchy as part of the title.
	 * @param null|string $sep           The separator sequence to separate the title components.
	 *
	 * @return string The page title.
	 */
	public function build_title( $current_title = '', $depth = true, $sep = null ) {
		$context = $this->context ?: tribe_context();
		$posts   = $this->get_posts();

		/**
		 * Filter the plural Events label for Views Title.
		 *
		 * @since 5.1.5
		 *
		 * @param string  $events_label_plural The plural events label as it's been generated thus far.
		 * @param Context $context             The context used to build the title, it could be the global one, or one externally
		 *                                     set.
		 */
		$this->events_label_plural = apply_filters( 'tribe_events_filter_views_v2_wp_title_plural_events_label', $this->events_label_plural, $context );

		if ( $context->is( 'single' ) && $context->is( 'event_post_type' ) ) {
			// For single events, the event title itself is required
			$title = get_the_title( $context->get( 'post_id' ) );
		} else {
			// For all other cases, start with 'upcoming events'
			$title = sprintf( esc_html__( 'Upcoming %s', 'the-events-calendar' ), $this->events_label_plural );
		}

		// If there's a date selected in the tribe bar, show the date range of the currently showing events
		$event_date = $context->get( 'event_date', false );

		$event_display_mode = $context->get( 'event_display_mode' );
		if ( $event_date && count( $posts ) ) {
			$title = $this->build_post_range_title( $context, $event_date );
		} elseif ( 'past' === $event_display_mode ) {
			$title = sprintf( esc_html__( 'Past %s', 'the-events-calendar' ), $this->events_label_plural );
		}

		if ( Month_View::get_view_slug() === $event_display_mode ) {
			$title = $this->build_month_title( $event_date );
		}

		if ( Day_View::get_view_slug() === $event_display_mode ) {
			$title = $this->build_day_title( $event_date );
		}

		$taxonomy = TEC::TAXONOMY;
		$term     = $context->get( $taxonomy, false );

		if ( false === $term ) {
			$taxonomy    = 'post_tag';
			$term = $context->get( $taxonomy, false );
		}

		if ( false !== $term ) {
			// Don't pass arrays to get_term_by()!
			if ( is_array( $term ) ) {
				$term = array_pop( $term );
			}

			$tax = get_term_by( 'slug', $term, $taxonomy );

			if ( $tax instanceof \WP_Term ) {
				$title = $this->build_category_title( $title, $tax, $depth, $sep );
			}
		}

		/**
		 * Allows for customization of the "Events" page title.
		 *
		 * This is the same filter used in the `tribe_get_events_title` function.
		 * This is by design, to allow the same filtering to apply. Since this method built the value using the context
		 * that is passed to filtering functions as a third parameter.
		 *
		 * @since 4.9.10
		 *
		 * @param string  $title   The "Events" page title as it's been generated thus far.
		 * @param bool    $depth   Whether to include the linked title or not.
		 * @param Context $context The context used to build the title, it could be the global one, or one externally
		 *                         set.
		 */
		$title = apply_filters( 'tribe_get_events_title', $title, $depth, $context );

		/**
		 * Filters the view title, specific to Views V2.
		 *
		 * While the `tribe_get_events_title` is called above this one for back-compatibility purposes, this filter
		 * is exclusive to the Views V2 implementation.
		 *
		 * @since 4.9.10
		 *
		 * @param string  $title   The "Events" page title as it's been generated thus far.
		 * @param bool    $depth   Whether to include the linked title or not.
		 * @param Context $context The context used to build the title, it could be the global one, or one externally
		 *                         set.
		 * @param array $posts An array of posts fetched by the View.
		 */
		return apply_filters( 'tribe_events_v2_view_title', $title, $depth, $context, $posts );
	}

	/**
	 * Builds the title for a range of posts.
	 *
	 * @since 4.9.10
	 *
	 * @param Context $context    The context to use to build the title.
	 * @param mixed   $event_date The event date object, string or timestamp.
	 *
	 * @return array The built post range title.
	 */
	public function build_post_range_title( Context $context, $event_date ) {
		$event_date = Dates::build_date_object( $event_date )->format( Dates::DBDATEFORMAT );
		$posts      = $this->get_posts();

		$first = reset( $posts );
		$last  = end( $posts );

		$first_returned_date = tribe_get_start_date( $first, false, Dates::DBDATEFORMAT );
		$first_event_date    = tribe_get_start_date( $first, false );
		$last_event_date     = tribe_get_end_date( $last, false );

		/*
		 * If we are on page 1 then we may wish to use the *selected* start date in place of the
		 * first returned event date.
		 */
		$page = $context->get( 'paged', 1 );
		if ( 1 == $page && $event_date < $first_returned_date ) {
			$first_event_date = tribe_format_date( $event_date, false );
		}

		$title = sprintf( __( '%1$s for %2$s - %3$s', 'the-events-calendar' ), $this->events_label_plural, $first_event_date, $last_event_date );

		return $title;
	}

	/**
	 * Filters and returns the `title` part of the array produced by the  `wp_get_document_title` function.
	 *
	 * @since 4.9.10
	 *
	 * @param array $title The document title parts.
	 *
	 * @return array The filtered document title parts.
	 */
	public function filter_document_title_parts( array $title = [] ) {
		$sep       = apply_filters( 'document_title_separator', '-' );
		$the_title = $title['title'];

		$new_title = $this->build_title( $title['title'] );

		/**
		 * Filters the page title built for event single or archive pages.
		 *
		 * @since 4.9.10
		 *
		 * @param string      $new_title The new title built for the page.
		 * @param string      $title     The original title.
		 * @param null|string $sep       The separator sequence to separate the title components.
		 */
		$the_title = apply_filters( 'tribe_events_title_tag', $new_title, $the_title, $sep );

		$title['title'] = $the_title;

		return $title;
	}

	/**
	 * Sets the context this title object should use to build the title.
	 *
	 * @since 4.9.10
	 *
	 * @param Context|null $context The context to use, `null` values will unset it causing the object ot use the
	 *                              global context.
	 *
	 * @return $this For chaining.
	 */
	public function set_context( Context $context = null ) {
		$this->context = $context;

		return $this;
	}

	/**
	 * Sets the posts this object should reference to build the title.
	 *
	 * We build some title components with notion of what events we found for a View. Here we set them.
	 *
	 * @since 4.9.10
	 *
	 * @param array|null $posts  An array of posts matching the context query, `null` will unset it causing the object
	 *                           to use the posts found by the global `$wp_query` object.
	 *
	 * @return $this For chaining.
	 */
	public function set_posts( array $posts = null ) {
		$this->posts = $posts;

		return $this;
	}

	/**
	 * Returns the post the title should use to build some title fragments.
	 *
	 * @since 4.9.10
	 *
	 * @return array An array of injected posts, or the globally found posts.
	 */
	protected function get_posts() {
		$posts = $this->posts;

		if ( null === $this->posts ) {
			global $wp_query;
			$posts = null !== $wp_query->posts ? $wp_query->posts : $wp_query->get_posts();
		}

		return $posts;
	}

	/**
	 * Builds the Month view title.
	 *
	 * @since 4.9.10
	 *
	 * @param mixed $event_date The date to use to build the title.
	 *
	 * @return string The Month view title.
	 */
	public function build_month_title( $event_date ) {
		$event_date = Dates::build_date_object( $event_date )->format( Dates::DBDATEFORMAT );

		$title = sprintf(
			esc_html_x( '%1$s for %2$s', 'month view', 'the-events-calendar' ),
			$this->events_label_plural,
			date_i18n( tribe_get_date_option( 'monthAndYearFormat', 'F Y' ), strtotime( $event_date ) )
		);

		/**
		 * Filters the Month view title.
		 *
		 * @since 4.9.10
		 *
		 * @param string $title The Month view title.
		 * @param string The date to use to build the title, in the `Y-m-d` format.
		 */
		return apply_filters( 'tribe_events_views_v2_month_title', $title, $event_date );
	}

	/**
	 * Builds the Day view title.
	 *
	 * @since 4.9.10
	 *
	 * @param mixed $event_date The date to use to build the title.
	 *
	 * @return string The Day view title.
	 */
	protected function build_day_title( $event_date ) {
		$title = sprintf(
			esc_html_x( '%1$s for %2$s', 'day_view', 'the-events-calendar' ),
			$this->events_label_plural,
			date_i18n( tribe_get_date_format( true ), strtotime( $event_date ) )
		);

		/**
		 * Filters the Day view title.
		 *
		 * @since 4.9.10
		 *
		 * @param string $title The Day view title.
		 * @param string The date to use to build the title, in the `Y-m-d` format.
		 */
		return apply_filters( 'tribe_events_views_v2_day_title', $title, $event_date );
}

	/**
	 * Builds, wrapping the current title, the Event Category archive title.
	 *
	 * @since 4.9.10
	 * @since 5.12.3 Added params, refined logic around category archive titles.
	 *
	 * @param string      $title     The input title.
	 * @param  \WP_Term   $cat       The category term to use to build the title.
	 * @param boolean     $depth     Whether to display the taxonomy hierarchy as part of the title.
	 * @param null|string $separator The separator sequence to separate the title components.
	 *
	 * @return string The built category archive title.
	 */
	protected function build_category_title( $title, $cat, $depth = true, $separator = ' &#8250; ' ) {
		$separator = is_null( $separator ) ? ' &#8250; ' : $separator;

		/**
		 * Allow folks to hook in and alter the option to show parent taxonomies in the title.
		 *
		 * @since 5.12.3
		 *
	 	 * @param boolean     $depth Whether to display the taxonomy hierarchy as part of the title.
		 * @param string      $title The input title.
		 * @param  \WP_Term   $cat   The category term to use to build the title.
		 */
		$depth = apply_filters( 'tec_events_views_v2_display_tax_hierarchy_in_title', $depth, $title, $cat );

		// This list includes the child taxonomy!
		if ( $depth ) {
			$term_parents = get_term_parents_list(
				$cat->term_id,
				$cat->taxonomy,
				[
					'link'      => false,
					'separator' => $separator
				]
			);
		}

		if ( empty( $term_parents ) || is_wp_error( $term_parents ) ) {
			$term_parents = $cat->name;
		}

		$new_title =  $title . $separator . $term_parents;

		/**
		 * Filters the Event Category Archive title.
		 *
		 * @since 4.9.10
		 *
		 *
		 * @param string    $new_title The Event Category archive title.
		 * @param string    $title     The original title.
		 * @param \WP_Term  $cat       The Event Category term used to build the title.
		 * @param boolean   $depth     Whether to display the taxonomy hierarchy as part of the title.
		 * @param string    $separator The separator character for the title parts.
		 */
		return apply_filters( 'tribe_events_views_v2_category_title', $new_title, $title, $cat, $depth, $separator );
	}
}
