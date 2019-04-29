<?php
/**
 * The base view class.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\List_View;
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
	 * The slug of the View instance, usually the one it was registered with in the `tribe_events_views`filter.
	 *
	 * This value will be set by the `View::make()` method while building a View instance.
	 *
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The Template instance the view will use to locate, manage and render its template.
	 *
	 * @var \Tribe\Events\Views\V2\Template
	 */
	protected $template;

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
	public static function make( $view = null ) {
		$view = null !== $view
			? $view
			: tribe_get_option( static::$option_default, 'default' );

		$views = self::get_registered_views();

		if ( 'default' === $view && count( $views ) ) {
			$view = reset( $views );
		}

		if ( class_exists( $view ) ) {
			$view_class = $view;
			$registration_slug = static::get_view_slug( $view );
		} else {
			$view_class = Arr::get( $views, $view, false );
			$registration_slug = $view;
		}

		if ( $view_class ) {
			if ( ! self::$container instanceof Container ) {
				$message = 'The ' . __CLASS__ . '::$container property is not set:'
				           . ' was the class initialized by the service provider?';
				throw new \RuntimeException( $message );
			}

			/** @var \Tribe\Events\Views\V2\View_Interface $instance */
			$instance = self::$container->make( $view_class );
			$template = new Template( $registration_slug );
		} else {
			$view_class = static::class;
			$instance = new static();
			$template = new Template( 'not-found' );
		}

		// Set some defaults on the template.
		$template->set( 'view_class', $view_class );

		$instance->set_template( $template );
		$instance->set_slug( $registration_slug );

		return $instance;
	}

	/**
	 * Returns an associative array of Views currently registered.
	 *
	 * @return array An array in the shape `[ <slug> => <View Class> ]`.
	 *
	 * @since TBD
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
		$views = apply_filters( 'tribe_events_views', [
			'list' => List_View::class,
		] );

		return (array) $views;
	}

	/**
	 * Returns the slug currently associated to a View class, if any.
	 *
	 * @param string $view The view fully qualified class name.
	 *
	 * @return int|string|false The slug currently associated to a View class if it is found, `false` otherwise.
	 * @since TBD
	 *
	 */
	public static function get_view_slug( $view ) {
		$views = self::get_registered_views();

		return array_search( $view, $views, true );
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

	public static function locate_template( $template ) {
		$template = locate_template( [ 'tribe/views/v2/router.php' ] );

	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \Tribe\Events\Views\V2\Implementation_Error If a class extending this one does not implement this method.
	 */
	public function registration_slug() {
		return $this->slug;
	}

	/**
	 * Sends, echoing it and exiting, the view HTML on the page.
	 *
	 * @param null|string $html A specific HTML string to print on the page or the HTML produced by the view
	 *                          `get_html` method.
	 *
	 * @throws \Tribe\Events\Views\V2\Implementation_Error If the `get_html` method has not been implemented.
	 * @since TBD
	 *
	 */
	public function send_html( $html = null ) {
		$html = null === $html ? $this->get_html() : $html;
		echo $html;
		tribe_exit( 200 );
	}

	/**
	 * {@inheritDoc}
	 * @throws \Tribe\Events\Views\V2\Implementation_Error If a class extending this one does not implement this method.
	 */
	public function get_html() {
		if ( self::class === static::class ) {
			return $this->template->render();
		}

		throw Implementation_Error::because_extending_view_should_define_this_method( 'get_html', $this );
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

	/**
	 * {@inheritDoc}
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;
		$this->template->set( 'slug', $slug );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_template( Template $template ) {
		$this->template = $template;
	}
}