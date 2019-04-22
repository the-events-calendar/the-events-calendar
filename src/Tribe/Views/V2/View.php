<?php
/**
 * The base view class.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

use Tribe__Container as Container;
use Tribe__Context as Context;
use Tribe__Utils__Array as Arr;

/**
 * Class View
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
class View implements View_Interface {

	/**
	 * An instance of the DI container.
	 *
	 * @var \tad_DI52_Container
	 */
	protected static $container;

	/**
	 * The slug of the not found view.
	 *
	 * @var string
	 */
	protected $not_found_slug;

	/**
	 * An instance of the context the View will use to render, if any.
	 *
	 * @var Context
	 */
	protected $context;

	/**
	 *
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \Tribe\Events\Views\V2\View_Interface
	 * @since TBD
	 */
	public static function make_for_rest( \WP_REST_Request $request ) {
		// Try to read the slug from the REST request.
		$slug = isset( $request['view'] ) ? $request['view'] : false;

		if ( false === $slug ) {
			$url = isset( $request['url'] ) ? $request['url'] : false;
			$slug = ( new Url( $url ) )->get_view_slug();
		}

		return static::make( $slug );
	}

	/**
	 * Builds and returns an instance of a View by slug or class.
	 *
	 * @param string $view The view slug, as registered in the `tribe_events_views` filter, or class.
	 *
	 * @return \Tribe\Events\Views\V2\View_Interface An instance of the built view.
	 * @since TBD
	 *
	 */
	public static function make( $view = 'default' ) {
		$views = self::get_registered_views();
		$view_class = class_exists( $view ) ? $view : Arr::get( $views, $view, false );

		if ( $view_class ) {
			$instance = self::$container->make( $view_class );
		} else {
			$instance = new static();
			$instance->not_found_slug = $view;
		}

		return $instance;
	}

	/**
	 * Sets the DI container the class should use to build views.
	 *
	 * @param \tad_DI52_Container $container The DI container instance to use.
	 *
	 * @since TBD
	 *
	 */
	public static function set_container( Container $container ) {
		static::$container = $container;
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
	public static function get_view_slug( string $view ) {
		$views = self::get_registered_views();

		return array_search( $view, $views, true );
	}

	/**
	 * Returns an associative array of Views currently registered.
	 *
	 * @since TBD
	 *
	 * @return array An array in the shape `[ <slug> => <View Class> ]`.
	 *
	 */
	public static function get_registered_views() {
		/**
		 * Filters the list of views available.
		 *
		 * Both classes and built objects can be associated with a slug; if bound in the container the classes
		 * will be built according to the binding rules; objects will be returned as they are.
		 *
		 * @param array $views An associative  array of views in the shape `[ <slug> => <class> ]`.
		 *
		 * @since TBD
		 *
		 */
		$views = apply_filters( 'tribe_events_views', [] );

		return (array) $views;
	}

	/**
	 * {@inheritDoc}
	 * @throws \Tribe\Events\Views\V2\Implementation_Error If a class extending this one does not implement this method.
	 */
	public function get_html() {
		throw Implementation_Error::because_extending_view_should_define_this_method( 'get_html', $this );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \Tribe\Events\Views\V2\Implementation_Error If a class extending this one does not implement this method.
	 */
	public function get_slug() {
		throw Implementation_Error::because_extending_view_should_define_this_method( 'get_slug', $this );
	}

	/**
	 * Sends, echoing it and exiting, the view HTML on the page.
	 *
	 * @since TBD
	 *
	 * @param null|string $html A specific HTML string to print on the page or the HTML produced by the view
	 *                          `get_html` method.
	 *
	 * @throws \Tribe\Events\Views\V2\Implementation_Error If the `get_html` method has not been implemented.
	 */
	public function send_html( $html = null ) {
		$html = null === $html ? $this->get_html() : $html;
		echo $html;
		tribe_exit( 200 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_context() {
		return null !== $this->context ? $this->context : tribe_context();
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_context( Context $context = null ) {
		$this->context = $context;
	}
}