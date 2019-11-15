<?php
namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Events\Views\V2\Views\Reflector_View;
use Tribe__Utils__Array as Arr;

/**
 * Class Views Manager
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.4
 */
class Manager {
	/**
	 * The name of the Tribe option the enabled/disabled flag for
	 * View v2 will live in.
	 *
	 * @var string
	 */
	public static $option_enabled = 'views_v2_enabled';

	/**
	 * The name of the Tribe option the default Views v2 slug will live in.
	 *
	 * @since 4.9.11 Use v1 option.
	 *
	 * @var string
	 */
	public static $option_default = 'viewOption';

	/**
	 * The name of the Tribe option the default mobile Views v2 slug will live in.
	 *
	 * @since 4.9.11 Use v1 option.
	 *
	 * @var string
	 */
	public static $option_mobile_default = 'mobile_default_view';

	/**
	 * Returns an associative array of Views currently registered.
	 *
	 * @since  4.9.4
	 *
	 * @return array An array in the shape `[ <slug> => <View Class> ]`.
	 */
	public function get_registered_views() {
		/**
		 * Filters the list of views available.
		 *
		 * Both classes and built objects can be associated with a slug; if bound in the container the classes
		 * will be built according to the binding rules; objects will be returned as they are.
		 *
		 * @since 4.9.2
		 *
		 * @param array $views An associative  array of views in the shape `[ <slug> => <class> ]`.
		 */
		$views = (array) apply_filters( 'tribe_events_views', [
			'list'      => List_View::class,
			'month'     => Month_View::class,
			'day'       => Day_View::class,
		] );

		// Make sure the Reflector View is always available.
		$views['reflector'] = Reflector_View::class;

		return $views;
	}

	/**
	 * Get the class name for the default registered view.
	 *
	 * The use of the `wp_is_mobile` function is not about screen width, but about payloads and how "heavy" a page is.
	 * All the Views are responsive, what we want to achieve here is serving users a version of the View that is
	 * less "heavy" on mobile devices (limited CPU and connection capabilities).
	 * This allows users to, as an example, serve the Month View to desktop users and the day view to mobile users.
	 *
	 * @since  4.9.4
	 *
	 * @param string|null $type The type of default View to return, either 'desktop' or 'mobile'; defaults to `mobile`.
	 *
	 * @return string The default View slug, this value could be different depending on the requested `$type` or
	 *                the context.
	 *
	 * @see wp_is_mobile()
	 * @link https://developer.wordpress.org/reference/functions/wp_is_mobile/
	 */
	public function get_default_view_option( $type = null ) {
		if ( null === $type ) {
			$type = wp_is_mobile() ? 'mobile' : 'desktop';
		}

		return ( 'mobile' === $type )
			? (string) tribe_get_option( static::$option_mobile_default, 'default' )
			: (string) tribe_get_option( static::$option_default, 'default' );
	}

	/**
	 * Get the class name for the default registered view.
	 *
	 * @since  4.9.4
	 *
	 * @return bool|string Returns boolean false when no views are registered or default not found.
	 */
	public function get_default_view() {
		$registered_views = $this->get_registered_views();
		$view_slug = $this->get_default_view_option();
		$view_class = Arr::get( $registered_views, $view_slug, reset( $registered_views ) );

		// Class for the view doesnt exist we bail with false.
		if ( ! class_exists( $view_class ) ) {
			return false;
		}

		/**
		 * Allows overwriting the default view.
		 *
		 * @since  4.9.11
		 *
		 * @param string $view_class Fully qualified class name for default view.
		 * @param string $view_slug  Default view slug.
		 */
		return apply_filters( 'tribe_events_views_v2_manager_default_view', (string) $view_class, $view_slug );
	}

	/**
	 * Returns an associative array of Views currently registered that are publicly visible.
	 *
	 * @since  4.9.4
	 *
	 * @return array An array in the shape `[ <slug> => <View Class> ]`.
	 */
	public function get_publicly_visible_views() {
		$views = $this->get_registered_views();

		/*
		 * Remove the Views that are not enabled, if the setting has been set.
		 * This applies the setting Events > Settings > "Enable event views".
		 * Default to all available views if the option is not set.
		 */
		$enabled_views = tribe_get_option( 'tribeEnableViews', array_keys( $views ) );

		$views = array_filter(
			$views,
			static function ( $view_class, $slug ) use ( $enabled_views )
			{
				return in_array( $slug, $enabled_views, true )
				       && (bool) call_user_func( [ $view_class, 'is_publicly_visible' ] );
			},
			ARRAY_FILTER_USE_BOTH
		);

		return $views;
	}

	/**
	 * Returns the slug and class of a given view, accepts slug or class.
	 * Will return false for both in case both fail.
	 *
	 * @since 4.9.4
	 *
	 * @param string $requested_view The view slug or fully qualified class name
	 *
	 * @return array  Formatted [ (string|bool) $view_slug, (string|bool) $view_class ]
	 */
	public function get_view( $requested_view ) {
		$view_slug = $this->get_view_slug_by_class( $requested_view );
		$view_class = $this->get_view_class_by_slug( $requested_view );

		// Bail, we had no matches for the slug or class.
		if ( ! $view_slug && ! $view_class ) {
			return [ false, false ];
		}

		// Requested with slug so save it there
		if ( $view_class && ! $view_slug ) {
			$view_slug = $requested_view;
		}

		// Requested with class so save it there
		if ( $view_slug && ! $view_class ) {
			$view_class = $requested_view;
		}

		return [ $view_slug, $view_class ];
	}

	/**
	 * Returns the slug currently associated to a View class, if any.
	 *
	 * @since 4.9.4
	 *
	 * @param string $view_class The view fully qualified class name.
	 *
	 * @return int|string|false  The slug currently associated to a View class if it is found, `false` otherwise.
	 */
	public function get_view_slug_by_class( $view_class ) {
		$registered_views = $this->get_registered_views();

		return array_search( $view_class, $registered_views, true );
	}

	/**
	 * Returns the class currently associated to a View slug, if any.
	 *
	 * @since 4.9.4
	 *
	 * @param  string $slug The view fully qualified class name.
	 *
	 * @return string|false The class currently associated to a View slug if it is found, `false` otherwise.
	 */
	public function get_view_class_by_slug( $slug ) {
		$registered_views = $this->get_registered_views();

		return Arr::get( $registered_views, $slug, false );
	}
}
