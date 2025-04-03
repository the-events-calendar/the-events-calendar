<?php
/**
 * Class that handles interfacing with core Site Health.
 *
 * @since   6.1.0
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Site_Health;

use TEC\Common\Site_Health\Info_Section_Abstract;
use TEC\Common\Site_Health\Factory;

/**
 * Class Site_Health
 *
 * @since   6.1.0
 * @package TEC\Events\Site_Health
 */
class Info_Section extends Info_Section_Abstract {
	/**
	 * Slug for the section.
	 *
	 * @since 6.1.0
	 *
	 * @var string $slug
	 */
	protected static string $slug = 'the-events-calendar';

	/**
	 * Label for the section.
	 *
	 * @since 6.1.0
	 *
	 * @var string $label
	 */
	protected string $label;

	/**
	 * If we should show the count of fields in the site health info page.
	 *
	 * @since 6.1.0
	 *
	 * @var bool $show_count
	 */
	protected bool $show_count = false;

	/**
	 * If this section is private.
	 *
	 * @since 6.1.0
	 *
	 * @var bool $is_private
	 */
	protected bool $is_private = false;

	/**
	 * Description for the section.
	 *
	 * @since 6.1.0
	 *
	 * @var string $description
	 */
	protected string $description;

	/**
	 * Sets up the section and internally add the fields.
	 *
	 * @since 6.1.0
	 */
	public function __construct() {
		$this->label       = esc_html__( 'The Events Calendar', 'the-events-calendar' );
		$this->description = esc_html__( 'This section contains information on The Events Calendar Plugin.', 'the-events-calendar' );
		$this->add_fields();
	}

	/**
	 * Generates and adds our fields to the section.
	 *
	 * @since 6.1.0
	 *
	 * @param array $info The debug information to be added to the core information page.
	 *
	 * @return array The debug information to be added to the core information page.
	 */
	public function add_fields(): void {
		$plural_events_label = tribe_get_event_label_plural_lowercase();

		$this->add_field(
			Factory::generate_post_status_count_field(
				'event_counts',
				\Tribe__Events__Main::POSTTYPE,
				10
			)
		);

		$this->add_field(
			Factory::generate_post_status_count_field(
				'published_organizers',
				\Tribe__Events__Organizer::POSTTYPE,
				20
			)
		);

		$this->add_field(
			Factory::generate_post_status_count_field(
				'published_venues',
				\Tribe__Events__Venue::POSTTYPE,
				30
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'event_block_editor',
				sprintf(
					esc_html__( 'Block Editor enabled for %1$s', 'the-events-calendar' ),
					$plural_events_label
				),
				tec_bool_to_string( tribe_get_option( 'toggle_blocks_editor', false ) ),
				40
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'include_events_in_loop',
				sprintf(
					esc_html__( 'Include %1$s in main blog loop', 'the-events-calendar' ),
					$plural_events_label
				),
				tec_bool_to_string( tribe_get_option( 'showEventsInMainLoop', false ) ),
				50
			)
		);

		$view_manager     = tribe( \Tribe\Events\Views\V2\Manager::class );
		$views            = array_flip( array_keys( $view_manager->get_registered_views() ) );
		$active_views     = array_keys( $view_manager->get_publicly_visible_views() );
		foreach( $views as $view => $value ) {
			if (
				'widget-events-list' === $view
				|| 'latest-past' === $view
				|| 'reflector' === $view
			) {
				unset( $views[ $view ] );
				continue;
			}

			if ( in_array( $view, $active_views ) ) {
				$views[ $view ] = true;
			} else {
				$views[ $view ] = false;
			}
		}

		$this->add_field(
			Factory::generate_generic_field(
				'enabled_views',
				esc_html__( 'Views', 'the-events-calendar' ),
				$views,
				60
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'default_view',
				esc_html__( 'Default view', 'the-events-calendar' ),
				$view_manager->get_default_view_slug(),
				70
			)
		);

		$import_query = new \WP_Query(
			[
				'post_type' => 'tribe_events',
				'meta_key' => '_EventOrigin',
				'meta_value' => 'event-aggregator'
			]
		);

		$this->add_field(
			Factory::generate_generic_field(
				'imported_events',
				sprintf(
					esc_html__( 'Total imported %1$s', 'the-events-calendar' ),
					$plural_events_label
				),
				$import_query->found_posts,
				80
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'front_page',
				esc_html__( 'Front page calendar', 'the-events-calendar' ),
				tec_bool_to_string( '-10' === get_option( 'page_on_front' ) ),
				90
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'previous_versions',
				esc_html__( 'Previous TEC versions', 'the-events-calendar' ),
				array_filter( (array) tribe_get_option( 'previous_ecp_versions', [] ) ),
				100
			)
		);
	}
}
