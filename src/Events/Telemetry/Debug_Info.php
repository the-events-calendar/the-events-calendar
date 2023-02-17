<?php

namespace TEC\Events\Telemetry;

use Tribe__Events__Main;

class Debug_Info {
	public function add_data( $info ) {
		$event_counts     = wp_count_posts( Tribe__Events__Main::POSTTYPE );
		$organizer_counts = wp_count_posts( \Tribe__Events__Organizer::POSTTYPE );
		$venue_counts     = wp_count_posts( \Tribe__Events__Venue::POSTTYPE );

		$fields = [
			'published_events' => [
				'label' => esc_html__( 'Total published events', 'the-events-calendar' ),
				'value' => empty( $event_counts->publish ) ? 0 : $event_counts->publish,
			],
			'published_organizers' => [
				'label' => esc_html__( 'Total published organizers', 'the-events-calendar' ),
				'value' => empty( $organizer_counts->publish ) ? 0 : $organizer_counts->publish,
			],
			'published_venues' => [
				'label' => esc_html__( 'Total published venues', 'the-events-calendar' ),
				'value' => empty( $venue_counts->publish ) ? 0 : $venue_counts->publish,
			],
		];

		/**
		 * Allows our other plugins to add data (fields) to the TEC Site Health section.
		 *
		 * @since TBD
		 *
		 * @param array $fields An array of fields to be added to the TEC Site Health section.
		 */
		$fields = apply_filters( 'tec_debug_info_data', $fields );

		$info[ Telemetry::$plugin_slug ] = [
			'label'       => esc_html__( 'The Events Calendar', 'the-events-calendar' ),
			'description' => esc_html__( 'This section contains information on The Events Calendar Plugin.', 'the-events-calendar' ),
			'fields'      => $fields,
		];

		return $info;
	}

	public function get_data() {

	}
}

/*

number of single events (ECP)
number of imported events
number of recurring events (ECP)
number of Series (ECP)
number of ticketed events (ET)
number of RSVPd events (ET)

Block Editor for Events
Include events in main blog loop
Enabled event views
Default view
Default mobile view
Using Events page as home page
Previous versions of TEC
 */
