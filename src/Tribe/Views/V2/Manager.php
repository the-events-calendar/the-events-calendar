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
	 * @var string
	 */
	public static $option_default = 'views_v2_default_view';

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

		// Make sure reflector is always available.
		$views['reflector'] = Reflector_View::class;

		return $views;
	}

	/**
	 * Get the class name for the default registered view.
	 *
	 * @since  4.9.4
	 *
	 * @return string
	 */
	public function get_default_view_option() {
		return (string) tribe_get_option( static::$option_default, 'default' );
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

		return (string) $view_class;
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

		foreach ( $views as $slug => $view_class ) {
			$view = View::make( $slug );

			// Remove all "private" views
			if ( $view->is_publicly_visible() ) {
				continue;
			}

			unset( $views[ $slug ] );
		}

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