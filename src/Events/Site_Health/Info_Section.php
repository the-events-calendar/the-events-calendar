<?php
/**
 * Class that handles interfacing with core Site Health.
 *
 * @since   TBD
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Site_Health;

use TEC\Common\Site_Health\Info_Section_Abstract;

/**
 * Class Site_Health
 *
 * @since   TBD

 * @package TEC\Events\Site_Health
 */
class Info_Section extends Info_Section_Abstract {
	/**
	 * Slug for the section.
	 *
	 * @since TBD
	 *
	 * @var string $slug
	 */
	protected static string $slug = 'the-events-calendar';

	/**
	 * Label for the section.
	 *
	 * @since TBD
	 *
	 * @var string $label
	 */
	protected string $label;

	/**
	 * If we should show the count of fields in the site health info page.
	 *
	 * @since TBD
	 *
	 * @var bool $show_count
	 */
	protected bool $show_count = false;

	/**
	 * If this section is private.
	 *
	 * @since TBD
	 *
	 * @var bool $is_private
	 */
	protected bool $is_private = false;

	/**
	 * Description for the section.
	 *
	 * @since TBD
	 *
	 * @var string $description
	 */
	protected string $description;

	public function __construct() {
		$this->label       = esc_html__( 'The Events Calendar', 'the-events-calendar' );
		$this->description = esc_html__( 'This section contains information on The Events Calendar Plugin.', 'the-events-calendar' );
	}

	/**
	 * Adds our default section to the Site Health Info tab.
	 *
	 * @since TBD
	 *
	 * @param array $info The debug information to be added to the core information page.
	 *
	 * @return array The debug information to be added to the core information page.
	 */
	public function add_fields() {
		$event_counts     = wp_count_posts( \Tribe__Events__Main::POSTTYPE );
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

		return $fields;
	}
}
