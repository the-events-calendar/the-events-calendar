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

		// The View iCalendar export link will be just the View URL with `ical=1` added to it.
		$url = add_query_arg( [ 'ical' => 1 ], $this->get_url( true ) );

		$ical_data     = (object) [
			'display_link' => $display_ical,
			'link'         => (object) [
				'url' => esc_url( $url ),
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
