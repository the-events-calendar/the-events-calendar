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
use Tribe__Utils__Array as Arr;

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

	public function bool_to_text( $bool ): string {
		return empty( $bool ) ? 'false' : 'true';
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
		$view_manager     = tribe( \Tribe\Events\Views\V2\Manager::class );
		$import_query = new \WP_Query(
			[
				'post_type' => 'tribe_events',
				'meta_key' => '_EventOrigin',
				'meta_value' => 'event-aggregator'
			]
		);

		$fields = [
			'event_counts' => [
				'label' => sprintf(
					esc_html__( '%1$s counts', 'the-events-calendar' ),
					tribe_get_event_label_plural()
				),
				'value' => $this->clean_status_counts( $event_counts ),
			],
			'published_organizers' => [
				'label' => sprintf(
					esc_html__( '%1$s counts', 'the-events-calendar' ),
					tribe_get_organizer_label_plural()
				),
				'value' => $this->clean_status_counts( $organizer_counts ),
			],
			'published_venues' => [
				'label' => sprintf(
					esc_html__( '%1$s counts', 'the-events-calendar' ),
					tribe_get_venue_label_plural()
				),
				'value' => $this->clean_status_counts( $venue_counts ),
			],
			'imported_events' => [
				'label' => sprintf(
					esc_html__( 'Total imported %1$s', 'the-events-calendar' ),
					tribe_get_event_label_plural_lowercase()
				),
				'value' => $import_query->found_posts,
			],
			'event_block_editor' => [
				'label' => sprintf(
					esc_html__( 'Block Editor enabled for %1$s', 'the-events-calendar' ),
					tribe_get_event_label_plural_lowercase()
				),
				'value' => $this->bool_to_text( tribe_get_option( 'toggle_blocks_editor', false ) ),
			],
			'include_events_in_loop' => [
				'label' => sprintf(
					esc_html__( 'Include %1$s in main blog loop', 'the-events-calendar' ),
					tribe_get_event_label_plural_lowercase()
				),
				'value' => $this->bool_to_text( tribe_get_option( 'showEventsInMainLoop', false ) ),
			],
			'enabled_views' => [
				'label' => esc_html__( 'Enabled views', 'the-events-calendar' ),
				'value' => Arr::to_list( array_flip( $view_manager->get_publicly_visible_views() ), ', ' ),
			],
			'default_view' => [
				'label' => esc_html__( 'Default view', 'the-events-calendar' ),
				'value' => $view_manager->get_default_view_slug(),
			],
			'front_page' => [
				'label' => esc_html__( 'Front page calendar', 'the-events-calendar' ),
				'value' => $this->bool_to_text( '-10' === get_option( 'page_on_front' ) )
			],
			'previous_versions' => [
				'label' => esc_html__( 'Previous TEC versions', 'the-events-calendar' ),
				'value' => Arr::to_list( array_filter( (array) tribe_get_option( 'previous_ecp_versions', [] ) ), ', ' )
			],
		];

		return $fields;
	}

	private function clean_status_counts( $obj ) {
		$obj = (array) $obj;
		$stati = [
			'publish',
			'future',
			'draft',
			'pending',
		];

		/**
		 * Allows other plugins to add stati to track.
		 *
		 * @param array<string|bool> $stati An array of stati to track.
		 */
		apply_filters( 'tec_events_site_heath_event_stati', $stati );

		$keys = array_keys( $obj );
		foreach( $keys as $key ) {
			if ( ! in_array( $key, $stati ) ) {
				unset( $obj[ $key ] );
			}
		}

		return $obj;
	}
}
