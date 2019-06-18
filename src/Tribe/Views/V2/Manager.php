<?php
namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Events\Views\V2\Views\Reflector_View;
use Tribe__Container as Container;
use Tribe__Context as Context;
use Tribe__Events__Main as TEC;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Rewrite as Rewrite;
use Tribe__Events__Venue as Venue;
use Tribe__Repository__Interface as Repository;
use Tribe__Utils__Array as Arr;

/**
 * Class Views Manager
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
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
	 * @since  TBD
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
	 * @since  TBD
	 *
	 * @return string
	 */
	public function get_default_view_option() {
		return (string) tribe_get_option( static::$option_default, 'default' );
	}

	/**
	 * Get the class name for the default registered view.
	 *
	 * @since  TBD
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
	 * @since  TBD
	 *
	 * @return array An array in the shape `[ <slug> => <View Class> ]`.
	 */
	public function get_publicly_visible_views() {
		$views = $this->get_registered_views();

		foreach ( $views as $slug => $view_class ) {
			$view = View::make( $slug );

			// Remove all "private" views
			if ( ! $view->is_publicly_visible() ) {
				unset( $views[ $slug ] );
			}
		}

		return $views;
	}

	/**
	 * Returns the slug currently associated to a View class, if any.
	 *
	 * @since TBD
	 *
	 * @param string $view The view fully qualified class name.
	 *
	 * @return int|string|false The slug currently associated to a View class if it is found, `false` otherwise.
	 */
	public function get_view_slug( string $view ) {
		$registered_views = $this->get_registered_views();

		return array_search( $view, $registered_views, true );
	}

	/**
	 * Returns the class currently associated to a View slug, if any.
	 *
	 * @since TBD
	 *
	 * @param  string $slug The view fully qualified class name.
	 *
	 * @return string|false The class currently associated to a View slug if it is found, `false` otherwise.
	 */
	public function get_view_class( string $slug ) {
		$registered_views = $this->get_registered_views();

		return Arr::get( $registered_views, $slug, false );
	}
}