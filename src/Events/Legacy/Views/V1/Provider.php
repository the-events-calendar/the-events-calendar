<?php
/**
 * Manages the legacy view removal and messaging.
 *
 * @since   5.13.0
 *
 * @package TEC\Events\Legacy\Views\V1
 */

namespace TEC\Events\Legacy\Views\V1;

use TEC\Common\Contracts\Service_Provider;
use Tribe__Utils__Array as Arr;

/**
 * Class Provider
 *
 * @since   5.13.0

 * @package TEC\Events\Legacy\Views\V1
 */
class Provider extends Service_Provider {
	/**
	 * Registers the handlers and modifiers for notifying the site
	 * that Legacy views are removed.
	 *
	 * @since 5.13.0
	 */
	public function register() {
		add_action( 'init', [ $this, 'check_theme_for_removed_paths' ] );
	}

	/**
	 * Gets the files and paths that have been removed from the plugin.
	 *
	 * @since 5.13.0
	 *
	 * @return array<string>
	 */
	public function get_removed_paths() {
		$paths = [
			'tribe-events/day/',
			'tribe-events/day.php',
			'tribe-events/list/',
			'tribe-events/list.php',
			'tribe-events/month/',
			'tribe-events/month.php',
			'tribe-events/pro/map/',
			'tribe-events/pro/map.php',
			'tribe-events/pro/map-basic.php',
			'tribe-events/pro/photo/',
			'tribe-events/pro/photo.php',
			'tribe-events/pro/week/',
			'tribe-events/pro/week.php',
			'tribe-events/widgets/',
		];

		/**
		 * Filters the paths that have been removed from the plugin.
		 *
		 * @since 5.13.0
		 *
		 * @param array<string> $paths The paths that have been removed from the plugin.
		 */
		$paths = apply_filters( 'tec_events_legacy_views_v1_removed_paths', $paths );

		return $paths;
	}

	/**
	 * Gets the files and paths that have been removed from the plugin.
	 *
	 * @since 5.13.0
	 */
	public function check_theme_for_removed_paths() {
		if ( ! tec_events_views_v1_should_display_deprecated_notice() ) {
			return;
		}

		$transient_key = 'tec_events_legacy_views_v1_removed_paths_checked';
		$cached_data   = (array) get_transient( $transient_key );

		$identical_abspath        = ABSPATH === Arr::get( $cached_data, 'ABSPATH', null );
		$identical_stylesheetpath = STYLESHEETPATH === Arr::get( $cached_data, 'STYLESHEETPATH', null );
		$identical_templatepath   = TEMPLATEPATH === Arr::get( $cached_data, 'TEMPLATEPATH', null );

		// We don't need to check again if the transient exists and key paths are identical.
		if (
			$identical_abspath
			&& $identical_stylesheetpath
			&& $identical_templatepath
		) {
			return;
		}

		$data = [
			'ABSPATH'        => ABSPATH,
			'STYLESHEETPATH' => STYLESHEETPATH,
			'TEMPLATEPATH'   => TEMPLATEPATH,
			'paths'          => [],
		];

		$paths = $this->get_removed_paths();

		foreach ( $paths as $path ) {
			if ( $template_path = $this->check_theme_for_removed_path( $path ) ) {
				$data['paths'][ $path ] = $template_path;
			}
		}

		foreach ( $data['paths'] as $path => $template_path ) {
			_deprecated_file( $path, '5.13.0', null, 'On version 6.0.0 this file will be removed. Please refer to <a href="https://evnt.is/v1-removal">https://evnt.is/v1-removal</a> for template customization assistance.' );
		}

		set_transient( 'tec_events_legacy_views_v1_removed_paths_checked', $data, DAY_IN_SECONDS );
	}

	/**
	 * Locate the template path for a given path.
	 *
	 * @since 5.13.0
	 *
	 * @param $string $path
	 * @return string|null
	 */
	public function check_theme_for_removed_path( $path ) {
		return locate_template( $path );
	}
}
