<?php
/**
 * Provides methods for getting iCal data for views
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views\Traits;

use Tribe\Events\Views\V2\View;

/**
 * Trait iCal_Data
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 *
 * @property string $string The slug of the View instance.
 */
trait iCal_Data {
	/**
	 * Returns the iCal data we're sending to the view.
	 *
	 * @since TBD
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

		switch( $slug ) {
			case 'month':
				$url = $this->get_month_view_url();
				break;
			default:
				$url = tribe_get_ical_link();
				break;
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
		 * Filters the ical data.
		 *
		 * @since 4.9.13
		 *
		 * @param object $ical_data An object containing the ical data.
		 * @param View   $this      The current View instance being rendered.
		 */
		$ical_data = apply_filters( "tribe_events_views_v2_view_ical_data", $ical_data, $this );

		/**
		 * Filters the ical data for a specific view.
		 *
		 * @since 4.9.13
		 *
		 * @param object $ical_data An object containing the ical data.
		 * @param View   $this      The current View instance being rendered.
		 */
		$ical_data = apply_filters( "tribe_events_views_v2_view_{$slug}_ical_data", $ical_data, $this );

		return $ical_data;
	}

	/**
	 * Gets the iCal url for the month view.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_month_view_url() {
		$event_date = $this->get_event_date();

		// If we don't have a date for some reason, give them the default iCal link.
		$url = ! empty( $event_date ) ? tribe( 'tec.iCal' )->month_view_ical_link( $event_date ) : null;
		return $url;
	}

	/**
	 * Gets the event date from the Context.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_event_date() {
		$event_date = null;
		$context = $this->get_context();

		if ( ! empty( $context->get( 'eventDate' ) ) ) {
			$event_date = $context->get( 'eventDate' );
		}

		// On page refresh, 'eventDate' is set as 'event_date'.
		if ( empty( $event_date ) && ! empty( $context->get( 'event_date' ) ) ) {
			$event_date = $context->get( 'event_date' );
		}

		return $event_date;
	}
}