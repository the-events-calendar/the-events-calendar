<?php
/**
 * Class that handles interfacing with core Site Health.
 *
 * @since   TBD
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Site_Health;

use Tribe__Events__Main;
/**
 * Class Site_Health
 *
 * @since   TBD

 * @package TEC\Events\Site_Health
 */
class Site_Health {
	/**
	 * Slug used for insertion.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $slug = 'the-events-calendar';
	/**
	 * Adds our Events Calendar section to the Site Health Info tab.
	 *
	 * @since TBD
	 *
	 * @param array $info The debug information to be added to the core information page.
	 *
	 * @return array The debug information to be added to the core information page.
	 */
	public function add_data( $info ) {
		$event_counts     = wp_count_posts( Tribe__Events__Main::POSTTYPE );
		$organizer_counts = wp_count_posts( \Tribe__Events__Organizer::POSTTYPE );
		$venue_counts     = wp_count_posts( \Tribe__Events__Venue::POSTTYPE );

		$fields = [
			'published_events' => [
				'label' => sprintf(
					esc_html__( 'Total published %1$s', 'the-events-calendar' ),
					tribe_get_event_label_plural_lowercase()
				),
				'value' => empty( $event_counts->publish ) ? 0 : $event_counts->publish,
			],
			'published_organizers' => [
				'label' => sprintf(
					esc_html__( 'Total published %1$s', 'the-events-calendar' ),
					strtolower( tribe_get_organizer_label_plural() )
				),
				'value' => empty( $organizer_counts->publish ) ? 0 : $organizer_counts->publish,
			],
			'published_venues' => [
				'label' => sprintf(
					esc_html__( 'Total published %1$s', 'the-events-calendar' ),
					strtolower( tribe_get_venue_label_plural() )
				),
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

		$section[ static::$slug ] = [
			'label'       => esc_html__( 'The Events Calendar', 'the-events-calendar' ),
			'description' => esc_html__( 'This section contains information on The Events Calendar Plugin.', 'the-events-calendar' ),
			'fields'      => $fields,
		];

		// Insert before media? (wp-media)
		$info = \Tribe__Main::array_insert_before_key(
			'wp-media',
			$info,
			$section
		);
		;

		return $info;
	}
}

/*



Block Editor for Events
Include events in main blog loop
Enabled event views
Default view
Default mobile view
Using Events page as home page
Previous versions of TEC
 */
