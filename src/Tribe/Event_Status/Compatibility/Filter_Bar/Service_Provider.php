<?php
/**
 * Handles the compatibility with the Filter Bar plugin.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

use Tribe\Events\Event_Status\Status_Labels;

/**
 * Class Service_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Register the bindings and filters required to ensure compatibility w/Filter Bar.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->container->singleton( self::class, $this );
		$this->container->singleton( 'tec.compatibility.tribe-filter-bar', $this );

		if ( ! class_exists( 'Tribe__Events__Filterbar__View' ) ) {
			// For whatever reason the plugin is not active but we still got here, bail.
			return;
		}

		// Add to the admin list.
		add_action( 'tribe_events_filters_create_filters', [ $this, 'create_filter' ] );

		// Make it work in v2.
		add_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ], 15 );
		add_filter( 'tribe_events_filter_bar_context_to_filter_map', [ $this, 'filter_context_to_filter_map' ] );
		add_filter( 'tribe_events_filter_bar_default_filter_names_map', [ $this, 'filter_default_filter_names_map' ] );
	}

	/**
	 * Includes the custom filter class and creates an instance of it.
	 *
	 * @since TBD
	 */
	public function create_filter() {
		return $this->container->make( Events_Status_Filter::class );
	}

	/**
	 * Filters the map of filters available on the front-end to include one for event status.
	 *
	 * @since TBD
	 *
	 * @param array<string,string> $map A map relating the filter slugs to their respective classes.
	 *
	 * @return array<string,string> The filtered slug to filter class map.
	 */
	public function filter_context_to_filter_map( array $map ) {
		$map['filterbar_event_status'] = Events_Status_Filter::class;

		return $map;
	}

	/**
	 * Filters the list of default Filter Bar filters to add the ones provided by the plugin.
	 *
	 * @since TBD
	 *
	 * @param array<string,string> $map A map relating the filter classes to their default names.
	 *
	 * @return array<string,string> The filtered map relating the filter classes to their default names.
	 */
	public function filter_default_filter_names_map( $map ) {
		$map[ Events_Status_Filter::class ] = $this->container->make( Status_Labels::class )->get_event_status_label();

		return $map;
	}

	/**
	 * Filters the Context locations to let the Context know how to fetch the value of the filter from a request.
	 *
	 * @param array<string,array> $locations A map of the locations the Context supports and is able to read from and write
	 *                                       to.
	 *
	 * @return array<string,array> The filtered map of Context locations, with the one required from the filter added to it.
	 */
	public function filter_context_locations( array $locations ) {
		$get_fb_val_from_view_data = static function ( $key ) {
			return static function ( $view_data ) use ( $key ) {
				return ! empty( $view_data[ 'tribe_filterbar_events_' . $key ] ) ? $view_data[ 'tribe_filterbar_events_' . $key ] : null;
			};
		};

		// Read the filter selected values, if any, from the URL request vars.
		$locations['filterbar_event_status'] = [
			'read' => [
				\Tribe__Context::QUERY_VAR     => [ 'tribe_filterbar_event_status' ],
				\Tribe__Context::REQUEST_VAR   => [ 'tribe_filterbar_event_status' ],
				\Tribe__Context::LOCATION_FUNC => [ 'view_data', $get_fb_val_from_view_data( 'event_status' ) ],
			],
		];

		return $locations;
	}
}
