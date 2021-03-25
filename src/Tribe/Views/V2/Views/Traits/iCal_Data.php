<?php
/**
 * Provides methods for getting iCal data for views
 *
 * @since   5.1.0
 *
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views\Traits;

use Tribe\Events\Views\V2\View;
use Tribe__Date_Utils as Dates;

/**
 * Trait iCal_Data
 *
 * @since   5.1.0
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 *
 * @property string $string The slug of the View instance.
 */
trait iCal_Data {
	/**
	 * Returns the iCal data we're sending to the view.
	 *
	 * @since 5.1.0
	 *
	 * @return object
	 */
	protected function get_ical_data() {
		$slug = $this->slug;

		/**
		 * A filter to control whether the "iCal Import" link shows up or not.
		 *
		 * @since unknown
		 *
		 * @param boolean $show Whether to show the "iCal Import" link; defaults to true.
		 */
		$display_ical = apply_filters( 'tribe_events_list_show_ical_link', true );

		/**
		 * Allow for customization of the iCal export link "Export Events" text.
		 *
		 * @since unknown
		 *
		 * @param string $text The default link text, which is "Export Events".
		 */
		$link_text = apply_filters(
			'tribe_events_ical_export_text',
			sprintf(
				/* translators: %s: Events (plural). */
				__( 'Export %s', 'the-events-calendar' ),
				tribe_get_event_label_plural()
			)
		);

		$link_title = __( 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps', 'the-events-calendar' );

		$url = tribe_get_ical_link();

		/*
		 * Whether the request comes in the context of a PHP initial state request or an
		 * AJAX-driven, the request URI will be correctly set.
		 */
		$unsafe_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';
		$request_uri        = filter_var( $unsafe_request_uri, FILTER_SANITIZE_URL );

		// From the Request URI read only the query arguments that start with `tribe`.
		$query_string = wp_parse_url( $request_uri, PHP_URL_QUERY );
		parse_str( $query_string, $query_args );
		$query_args = array_filter( $query_args, static function ( $query_arg_name ) {
			return strpos( $query_arg_name, 'tribe' ) === 0;
		}, ARRAY_FILTER_USE_KEY );

		// Set the View explicitly; will be read with `Context::get( 'ical_view' )`.
		$query_args['view'] = $this->slug;

		// Set the display mode, if any, explicitly; will be read with Context::get( 'ical_view_mode' )`.
		if ( $this->context->get( 'event_display_mode', false ) === 'past' ) {
			$query_args['mode'] = 'past';
		}

		// Set the request date explicitly; will be read with `Context::get( 'event_date' )`.
		$event_date = $this->context->get( 'event_date', null );
		if ( null !== $event_date && 'now' !== $event_date ) {
			unset( $query_args['tribe-bar-date'] );
			$query_args['eventDate'] = $event_date;
		}

		$passthrough_map = [
			'tribe_events_cat' => 'tribe_events_cat',
			'name' => 'name',
		];

		foreach ( $passthrough_map as $context_key => $query_arg ) {
			$context_value = $this->context->get( $context_key, null );
			if ( null !== $context_value ) {
				$query_args[ $query_arg ] = $context_value;
			}
		}

		// Put it all together and create a URL that will request what we just required.
		if ( count( $query_args ) ) {
			$url = add_query_arg( $query_args, $url );
		}

		$ical_data = (object) [
			'display_link' => $display_ical,
			'link'         => (object) [
				'url'   => esc_url( $url ),
				'text'  => $link_text,
				'title' => $link_title,
			],
		];

		/**
		 * Filters the iCal data.
		 *
		 * @since 4.9.13
		 *
		 * @param object $ical_data An object containing the iCal data.
		 * @param View   $this      The current View instance being rendered.
		 */
		$ical_data = apply_filters( 'tribe_events_views_v2_view_ical_data', $ical_data, $this );

		/**
		 * Filters the iCal data for a specific view.
		 *
		 * @since 4.9.13
		 *
		 * @param object $ical_data An object containing the iCal data.
		 * @param View   $this      The current View instance being rendered.
		 */
		$ical_data = apply_filters( "tribe_events_views_v2_view_{$slug}_ical_data", $ical_data, $this );

		return $ical_data;
	}

	/**
	 * Gets the iCal url for the month view.
	 *
	 * @since 5.1.0
	 *
	 * @return string The iCAl URL for the month view.
	 */
	public function get_month_view_url() {
		$event_date = $this->context->get( 'event_date', Dates::build_date_object()->format( Dates::DBYEARMONTHTIMEFORMAT ) );

		// If we don't have a date for some reason, give them the default iCal link.
		$url = ! empty( $event_date )
		? tribe( 'tec.iCal' )->month_view_ical_link( $event_date )
		: tribe( 'tec.iCal' )->get_ical_link();

		return $url;
	}
}
