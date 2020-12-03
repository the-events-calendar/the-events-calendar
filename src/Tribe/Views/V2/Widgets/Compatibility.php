<?php
/**
 * Compatibility for Advanced List Widgets and List Widget.
 * Scenarios:
 * * TEC with Pro Activated in V1 ( Reverse )
 * * Pro Disabled to TEC in V1 ( Default ) - not supported.
 * * V1 to V2 ( Default )
 * * Pro Disabled V2 Active ( Default )
 * * Pro Active V2 to V1 with constant ( Reverse )
 * * Pro Disabled V2 back to V1 with constant ( Default ) - not supported.
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

/**
 * Class Compatibility
 *
 * @since   5.3.0
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
	 * Switches the primary and alternative id base when v1 is active
	 * or v2 widgets are disabled, this enables support for upgrading from
	 * v1 free list widget to the v1 Pro advanced list widget.
	 * This class lives here as it could support the v1 advanced list widget
	 * turning back to the free widget.
	 *
	 * @since 5.3.0
	 */
	public function switch_compatibility() {
		if ( ! $this->is_v2_adv_list_widget() ) {
			return;
		}

		/**
		 * Allow filtering of whether the event list or the advanced event list widget should be primary.
		 *
		 * @since 5.3.0
		 *
		 * @param bool $adv_primary Whether the advanced list widget is primary.
		 */
		$advanced_primary = apply_filters( 'tribe_events_views_v2_advanced_list_widget_primary', false );

		if (
			$advanced_primary &&
			! tribe_events_views_v2_is_enabled()
		) {
			$this->primary_id_base     = 'tribe-events-adv-list-widget';
			$this->alternative_id_base = 'tribe-events-list-widget';
		}

		add_filter( "option_widget_{$this->primary_id_base}", [ $this, 'merge_list_widget_options' ] );
	}

	/**
	 * Function that determines which version of the widget we should load based on the ECP version.
	 *
	 * @since 5.3.0
	 *
	 * @return boolean
	 */
	public function is_v2_adv_list_widget() {
		if ( ! defined( 'Tribe__Events__Pro__Main::VERSION' ) ) {
			return true;
		}

		return version_compare( \Tribe__Events__Pro__Main::VERSION, '5.3.0-dev', '>=' );
	}

	/**
	 * Remap the widget id_base for the Pro Advanced List Widget.
	 *
	 * @since 5.3.0
	 *
	 * @param array<string,mixed> $widget_areas An array of widgets areas with the saved widgets in each location.
	 *
	 * @return array<string,mixed> $widget_areas A modified array of widgets areas with the saved widgets in each location.
	 */
	public function remap_list_widget_id_bases( $widget_areas ) {
		if ( ! is_array( $widget_areas ) ) {
			return $widget_areas;
		}

		if ( ! $this->is_v2_adv_list_widget() ) {
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
	 * @since 5.3.0
	 *
	 * @param array<int,mixed> $widgets An array of saved widgets.
	 *
	 * @return array<int,mixed> $widgets The modified array of saved widgets.
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

		// Combine arrays and keep the array keys.
		return $widgets + $alternative_options;
	}
}
