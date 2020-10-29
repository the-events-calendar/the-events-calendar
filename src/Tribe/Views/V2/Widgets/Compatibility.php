<?php
/**
 * Compatibility for Advanced List Widgets and List Widget.
 * In V2 Advanced List Widgets are merged with List Widgets, it is reversed with V1.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

/**
 * Class Compatibility
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Compatibility {

	/**
	 * The default primary list widget id base string.
	 *
	 * @var string
	 */
	protected $primary_id_base = 'tribe-events-list-widget';

	/**
	 * The default alternative list widget id base string.
	 *
	 * @var string
	 */
	protected $alternative_id_base = 'tribe-events-adv-list-widget';

	/**
	 * Adds the filters for V2 Widgets
	 *
	 * @since TBD
	 */
	public function hooks() {
		add_action( 'tribe_plugins_loaded', [ $this, 'switch_compatibility' ] );
		add_filter( 'option_sidebars_widgets', [ $this, 'remap_list_widget_id_bases' ] );
		add_filter( 'option_widget_tribe-events-list-widget', [ $this, 'merge_list_widget_options' ] );
	}

	/**
	 * Switches the primary and alternative id base when v1 is active
	 * or v2 widgets are disabled, this enables support for upgrading from
	 * v1 free list widget to the v1 Pro advanced list widget.
	 *
	 * @since TBD
	 */
	public function switch_compatibility() {
		// if Pro is disabled, use the defaults.
		if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
			return;
		}

		if (
			! tribe_events_views_v2_is_enabled() ||
			tribe_events_widgets_v2_is_disabled()
		) {
			$this->primary_id_base     = 'tribe-events-adv-list-widget';
			$this->alternative_id_base = 'tribe-events-list-widget';
		}
	}

	/**
	 * Remap the widget id_base for the Pro Advanced List Widget.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $widget_areas An array of widgets areas with the saved widgets in each location.
	 *
	 * @return array<string,mixed> $widget_areas An array of widgets areas with the saved widgets in each location.
	 */
	public function remap_list_widget_id_bases( $widget_areas ) {

		if ( ! is_array( $widget_areas ) ) {
			return $widget_areas;
		}

		foreach ( $widget_areas as $key => $widget_location ) {

			if ( ! is_array( $widget_location ) ) {
				continue;
			}

			foreach ( $widget_location as $widget_key => $widget ) {
				$widget_areas[ $key ][ $widget_key ] = str_replace( $this->alternative_id_base, $this->primary_id_base, $widget );
			}
		}

		return $widget_areas;
	}

	/**
	 * Merge the Event List and Advanced List Widget Options.
	 *
	 * @since TBD
	 *
	 * @param array<int,mixed> $widgets An array of saved widgets.
	 *
	 * @return array<int,mixed> $widgets An array of saved widgets.
	 */
	public function merge_list_widget_options( $widgets ) {

		if ( ! is_array( $widgets ) ) {
			return $widgets;
		}

		// Get the saved alternative widgets.
		$alternative_options = get_option( "widget_{$this->alternative_id_base}" );
		if ( ! is_array( $alternative_options ) ) {
			return $widgets;
		}

		// Combine arrays and key the array keys.
		return $widgets + $alternative_options;
	}
}
