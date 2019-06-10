<?php
/**
 * The base view class.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Events\Views\V2\Views\Reflector_View;
use Tribe__Container as Container;
use Tribe__Context as Context;
use Tribe__Events__Main as TEC;
use Tribe__Events__Rewrite as Rewrite;
use Tribe__Repository__Interface as Repository;
use Tribe__Utils__Array as Arr;

/**
 * Class View
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
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
	 * The repository object the View is currently using.
	 *
	 * @var Repository
	 */
	protected $repository;

	/**
	 * The URL object the View is currently.
	 *
	 * @var \Tribe\Events\Views\V2\Url
	 */
	protected $url;

	/**
	 * An associative array of global variables backed up by the view before replacing the global loop.
	 *
	 * @since 4.9.3
	 *
	 * @var array
	 */
	protected $global_backup;

	/**
	 * An associative array of the arguments used to setup the repository filters.
	 *
	 * @since 4.9.3
	 *
	 * @var array
	 */
	protected $repository_args = [];

	/**
	 * Builds a View instance in response to a REST request to the Views endpoint.
	 *
	 * @since 4.9.2
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \Tribe\Events\Views\V2\View_Interface
	 */
	public static function make_for_rest( \WP_REST_Request $request ) {
		// Try to read the slug from the REST request.
		$params = $request->get_params();
		$slug = Arr::get( $params, 'view', false );
		$url = Arr::get( $params, 'url' );
		$url_object = new Url( $url );
		$params = array_merge( $params, $url_object->get_query_args() );

		if ( false === $slug ) {
			/*
			 * If we cannot get the view slug from the request parameters let's try to get it from the URL.
			 */
			$slug = Arr::get( $params, 'eventDisplay', tribe_context()->get( 'view', 'default' ) );
		}

		// Let's check if we have a display mode set.
		$query_args                   = $url_object->query_overrides_path( true )->parse_url()->get_query_args();
		$params['event_display_mode'] = Arr::get( $query_args, 'eventDisplay', false );

		/**
		 * Filters the parameters that will be used to build the View class for a REST request.
		 *
		 * This filter will trigger for all Views.
		 *
		 * @since 4.9.3
		 *
		 * @param array $params An associative array of parameters from the REST request.
		 * @param \WP_REST_Request $request The current REST request.
		 */
		$params = apply_filters( 'tribe_events_views_v2_rest_params', $params, $request );

		if ( ! empty( $slug ) ) {
			/**
			 * Filters the parameters that will be used to build a specific View class for a REST request.
			 *
			 * @since 4.9.3
			 *
			 * @param  array             $params   An associative array of parameters from the REST request.
			 * @param  \WP_REST_Request  $request  The current REST request.
			 */
			$params = apply_filters( "tribe_events_views_v2_{$slug}_rest_params", $params, $request );
		}

		// Determine context based on params given
		$context = tribe_context()->alter( $params );

		$view =  static::make( $slug, $context );

		$view->url = $url_object;

		return $view;
	}

	/**
	 * Builds and returns an instance of a View by slug or class.
	 *
	 * @since 4.9.2
	 *
	 * @param  string  $view  The view slug, as registered in the `tribe_events_views` filter, or class.
	 * @param  \Tribe__Context|null  $context  The context this view should render from; if not set then the global
	 *                                         one will be used.
	 *
	 * @return \Tribe\Events\Views\V2\View_Interface An instance of the built view.
	 */
	public static function make( $view = null, \Tribe__Context $context = null ) {
		$view = null !== $view
			? $view
			: tribe_get_option( static::$option_default, 'default' );

		$views = self::get_registered_views();

		if ( 'default' === $view && count( $views ) ) {
			$view = reset( $views );
		}

		if ( class_exists( $view ) ) {
			$view_class = $view;
			$slug       = static::get_view_slug( $view );
		} else {
			$view_class = Arr::get( $views, $view, false );
			$slug       = $view;
		}

		$request_slug = $slug;

		if ( $view_class ) {
			if ( ! self::$container instanceof Container ) {
				$message = 'The ' . __CLASS__ . '::$container property is not set:'
				           . ' was the class initialized by the service provider?';
				throw new \RuntimeException( $message );
			}

			/** @var \Tribe\Events\Views\V2\View_Interface $instance */
			$instance = self::$container->make( $view_class );
		} else {
			$view_class = static::class;
			$instance   = new static();
			$slug       = 'not-found';
		}

		$template = new Template( $slug );

		/**
		 * Filters the Template object for a View.
		 *
		 * @since 4.9.3
		 *
		 * @param  \Tribe\Events\Views\V2\Template  $template  The template object for the View.
		 * @param  string                           $view      The current view slug.
		 * @param  \Tribe\Events\Views\V2\View      $instance  The current View object.
		 */
		$template = apply_filters( 'tribe_events_views_v2_view_template', $template, $view, $instance );

		/**
		 * Filters the Template object for a specific View.
		 *
		 * @since 4.9.3
		 *
		 * @param  \Tribe\Events\Views\V2\Template  $template  The template object for the View.
		 * @param  \Tribe\Events\Views\V2\View      $instance  The current View object.
		 */
		$template = apply_filters( "tribe_events_views_v2_{$slug}_view_template", $template, $instance );

		// Set some defaults on the template.
		$template->set( 'view_class', $view_class );
		$template->set( 'request_slug', $request_slug );

		$instance->set_template( $template );
		$instance->set_slug( $slug );

		// Let's set the View context from either the global context or the provided one.
		$view_context = null === $context ? tribe_context() : $context;

		/**
		 * Filters the Context object for a View.
		 *
		 * @since 4.9.3
		 *
		 * @param  \Tribe__Context  $view_context  The context abstraction object that will be passed to the
		 *                                         view.
		 * @param  string                       $view          The current view slug.
		 * @param  \Tribe\Events\Views\V2\View  $instance      The current View object.
		 */
		$view_context = apply_filters( 'tribe_events_views_v2_view_context', $view_context, $view, $instance );

		/**
		 * Filters the Context object for a specific View.
		 *
		 * @since 4.9.3
		 *
		 * @param  \Tribe__Context              $view_context  The context abstraction object that will be passed to the
		 *                                                     view.
		 * @param  \Tribe\Events\Views\V2\View  $instance      The current View object.
		 */
		$view_context = apply_filters( "tribe_events_views_v2_{$slug}_view_context", $view_context, $instance );

		$instance->set_context( $view_context );

		$view_repository = tribe_events();

		/**
		 * Filters the Repository object for a View.
		 *
		 * @since 4.9.3
		 *
		 * @param \Tribe__Repository__Interface $view_repository The repository instance the View will use.
		 * @param string                        $view            The current view slug.
		 * @param \Tribe\Events\Views\V2\View   $instance        The current View object.
		 */
		$view_repository = apply_filters( 'tribe_events_views_v2_view_context', $view_repository, $view, $instance );

		/**
		 * Filters the Repository object for a specific View.
		 *
		 * @since 4.9.3
		 *
		 * @param \Tribe__Repository__Interface $view_repository The repository instance the View will use.
		 * @param \Tribe\Events\Views\V2\View   $instance        The current View object.
		 */
		$view_repository = apply_filters( "tribe_events_views_v2_{$slug}_view_context", $view_repository, $instance );

		$instance->set_repository( $view_repository );

		$instance->set_url();

		return $instance;
	}

	/**
	 * Returns an associative array of Views currently registered.
	 *
	 * @return array An array in the shape `[ <slug> => <View Class> ]`.
	 *
	 * @since 4.9.2
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
		 * @since 4.9.2
		 *
		 */
		$views = apply_filters( 'tribe_events_views', [
			'month'     => Month_View::class,
			'list'      => List_View::class,
			'past'      => List_View::class,
			'reflector' => Reflector_View::class,
		] );

		return (array) $views;
	}

	/**
	 * Returns the slug currently associated to a View class, if any.
	 *
	 * @param string $view The view fully qualified class name.
	 *
	 * @return int|string|false The slug currently associated to a View class if it is found, `false` otherwise.
	 * @since 4.9.2
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
	 * @since 4.9.2
	 *
	 */
	public static function set_container( Container $container ) {
		static::$container = $container;
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
	 * @since 4.9.2
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

	/**
	 * {@inheritDoc}
	 */
	public function get_url( $canonical = false ) {
		$query_args = [
			'post_type'    => TEC::POSTTYPE,
			'eventDisplay' => $this->slug,
		];

		$page = $this->url->get_current_page();

		if ( $page > 1 ) {
			$query_args['paged'] = $page;
		}

		$url = add_query_arg( array_filter( $query_args ), home_url() );

		if ( $canonical ) {
			$url = Rewrite::instance()->get_clean_url( $url );
		}

		$event_display_mode = $this->context->get( 'event_display_mode', false );
		if ( false !== $event_display_mode && $event_display_mode !== $this->context->get( 'eventDisplay' ) ) {
			$url = add_query_arg( [ 'eventDisplay' => $event_display_mode ], $url );
		}

		$url = $this->filter_view_url( $canonical, $url );

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function next_url( $canonical = false, array $passthru_vars = [] ) {
		$next_page = $this->repository->next();

		$url = $next_page->count() > 0 ?
			add_query_arg( [ 'paged' => $this->url->get_current_page() + 1 ], $this->get_url() )
			: '';

		if ( ! empty( $url ) && $canonical ) {
			$input_url = $url;

			if ( ! empty( $passthru_vars ) ) {
				$input_url = remove_query_arg( array_keys( $passthru_vars ), $url );
			}

			// Make sure the view slug is always set to correctly match rewrites.
			$input_url = add_query_arg( [ 'eventDisplay' => $this->slug ], $input_url );

			$canonical_url = Rewrite::instance()->get_clean_url( $input_url );

			if ( ! empty( $passthru_vars ) ) {
				$canonical_url = add_query_arg( $passthru_vars, $canonical_url );
			}

			$url = $canonical_url;
		}

		$url = $this->filter_next_url( $canonical, $url );

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prev_url( $canonical = false, array $passthru_vars = [] ) {
		$prev_page  = $this->repository->prev();
		$paged      = $this->url->get_current_page() - 1;
		$query_args = $paged > 1
			? [ 'paged' => $paged ]
			: [];

		$url = $prev_page->count() > 0 ?
			add_query_arg( $query_args, $this->get_url() )
			: '';

		if ( ! empty( $url ) && $paged === 1 ) {
			$url = remove_query_arg( 'paged', $url );
		}

		if ( ! empty( $url ) && $canonical ) {
			$input_url = $url;

			if ( ! empty( $passthru_vars ) ) {
				$input_url = remove_query_arg( array_keys( $passthru_vars ), $url );
			}

			// Make sure the view slug is always set to correctly match rewrites.
			$input_url = add_query_arg( [ 'eventDisplay' => $this->slug ], $input_url );

			$canonical_url = Rewrite::instance()->get_clean_url( $input_url );

			if ( ! empty( $passthru_vars ) ) {
				$canonical_url = add_query_arg( $passthru_vars, $canonical_url );
			}

			$url = $canonical_url;
		}

		$url = $this->filter_prev_url( $canonical, $url );

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_url_object() {
		return $this->url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_repository( Repository $repository = null ) {
		$this->repository = $repository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_repository() {
		return $this->repository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup_the_loop( array $args = [] ) {
		global $wp_query;

		$this->global_backup = [
			'wp_query'  => $wp_query,
		];

		/**
		 * Filters the arguments that will be used to build the View repository.
		 *
		 * @since 4.9.3
		 *
		 * @param  array  $args  An array of arguments that should be used to build the repository instance.
		 * @param  View   $this  The current View object.
		 */
		$this->repository_args = apply_filters( "tribe_events_views_v2_{$this->slug}_repository_args", $args, $this );

		$this->set_repository( $this->build_repository( $this->repository_args ) );
		$this->set_url( $this->repository_args, true );

		$wp_query = $this->repository->get_query();
		wp_reset_postdata();
	}

	/**
	 * {@inheritDoc}
	 */
	public function restore_the_loop() {
		if ( empty( $this->global_backup ) ) {
			return;
		}

		foreach ( $this->global_backup as $key => $value ) {
			$GLOBALS[ $key ] = $value;
		}

		wp_reset_postdata();
	}

	/**
	 * Builds the repository the View will use to get the loop posts.
	 *
	 * @since 4.9.3
	 *
	 * @param  array  $args An associative array of arguments that will be used to build the repository.
	 *
	 * @return \Tribe__Repository__Interface
	 */
	protected function build_repository( array $args ) {
		return tribe_events()->by_args( $args );
	}

	/**
	 * Sets a View URL object either from some arguments or from the current URL.
	 *
	 * @since 4.9.3
	 *
	 * @param  array|null  $args An associative array of arguments that will be mapped to the corresponding query
	 *                           arguments by the View, or `null` to use the current URL.
	 */
	public function set_url( array $args = null, $merge = false ) {
		if ( null !== $args ) {
			$query_args = $this->map_args_to_query_args( $args );
			$this->url = false === $merge ?
				new Url( add_query_arg( $query_args ) )
				: $this->url->add_query_args( $query_args );

			return;
		}

		$this->url = new Url();
	}

	/**
	 * Maps a set of arguments to query arguments, ready to be appended to a URL.
	 *
	 * @since 4.9.3
	 *
	 * @param  array  $args An associative array of arguments to map (translate) to query arguments.
	 *
	 * @return array An associative array of query arguments mapped from the input ones.
	 */
	protected function map_args_to_query_args( array $args = null ) {
		if ( empty( $args ) ) {
			return [];
		}

		// By default let's use the locations set in the Context to map the arguments to query args.
		$query_args = tribe_context()->map_to_read( $args, Context::REQUEST_VAR );

		global $wp;

		return array_intersect_key( $query_args, array_combine( $wp->public_query_vars, $wp->public_query_vars ) );
	}

	/**
	 * Filters the array of values that a View will set on the Template before rendering it.
	 *
	 * Template variables are exported, alongside being set, in the template context: the keys of the variables array
	 * will become the names of the exported variables.
	 *
	 * @since 4.9.3
	 *
	 * @param array $template_vars An associative array of variables that will be set, and exported, in the template.
	 *
	 * @return array An associative array of variables that will be set, and exported, in the template.
	 */
	protected function filter_template_vars( array $template_vars ) {
		/**
		 * Filters the variables that will be set on the View template.
		 *
		 * @since 4.9.3
		 *
		 * @param array          $template_vars An associative array of template variables. Variables will be extracted in the
		 *                                      template hence the key will be the name of the variable available in the
		 *                                      template.
		 * @param View_Interface $this          The current view whose template variables are being set.
		 */
		$template_vars = apply_filters( "tribe_events_views_v2_{$this->slug}_template_vars", $template_vars, $this );

		return $template_vars;
	}

	/**
	 * Filters the previous (page, event, etc.) URL returned for a specific View.
	 *
	 * @since 4.9.3
	 *
	 * @param  bool $canonical Whether the normal or canonical version of the next URL is being requested.
	 * @param string $url The previous URL, this could be an empty string if the View does not have a next.
	 *
	 * @return string The filtered previous URL.
	 */
	protected function filter_prev_url( $canonical, string $url ) {
		/**
		 * Filters the previous (page, event, etc.) URL returned for a View.
		 *
		 * @since 4.9.3
		 *
		 * @param string         $url       The View previous (page, event, etc.) URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( 'tribe_events_views_v2_view_prev_url', $url, $canonical, $this );

		/**
		 * Filters the previous (page, event, etc.) URL returned for a specific View.
		 *
		 * @since 4.9.3
		 *
		 * @param string         $url       The View previous (page, event, etc.) URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( "tribe_events_views_v2_{$this->slug}_prev_url", $url, $canonical, $this );

		return $url;
	}

	/**
	 * Filters the next (page, event, etc.) URL returned for a specific View.
	 *
	 * @since 4.9.3
	 *
	 * @param  bool $canonical Whether the normal or canonical version of the next URL is being requested.
	 * @param string $url The next URL, this could be an empty string if the View does not have a next.
	 *
	 * @return string The filtered next URL.
	 */
	protected function filter_next_url( $canonical, string $url ) {
		/**
		 * Filters the next (page, event, etc.) URL returned for a View.
		 *
		 * @since 4.9.3
		 *
		 * @param string         $url       The View next (page, event, etc.) URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( 'tribe_events_views_v2_view_next_url', $url, $canonical, $this );

		/**
		 * Filters the next (page, event, etc.) URL returned for a specific View.
		 *
		 * @since 4.9.3
		 *
		 * @param string         $url       The View next (page, event, etc.) URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( "tribe_events_views_v2_{$this->slug}_next_url", $url, $canonical, $this );

		return $url;
	}

	/**
	 * Sets up the View repository arguments from the View context or a provided Context object.
	 *
	 * @since 4.9.3
	 *
	 * @param \Tribe__Context|null $context A context to use to setup the args, or `null` to use the View Context.
	 *
	 * @return array The arguments, ready to be set on the View repository instance.
	 * @throws Implementation_Error If an extending View does not implement this method.
	 */
	protected function setup_repository_args( \Tribe__Context $context = null ) {
		throw Implementation_Error::because_extending_view_should_define_this_method( 'setup_repository_args', $this );
	}

	/**
	 * Filters the current URL returned for a specific View.
	 *
	 * @since 4.9.3
	 *
	 * @param  bool $canonical Whether the normal or canonical version of the next URL is being requested.
	 * @param string $url The previous URL, this could be an empty string if the View does not have a next.
	 *
	 * @return string The filtered previous URL.
	 */
	protected function filter_view_url( $canonical, string $url ) {
		/**
		 * Filters the URL returned for a View.
		 *
		 * @since 4.9.3
		 *
		 * @param string         $url       The View current URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( 'tribe_events_views_v2_view_url', $url, $canonical, $this );

		/**
		 * Filters the URL returned for a specific View.
		 *
		 * @since 4.9.3
		 *
		 * @param string         $url       The View current URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( "tribe_events_views_v2_{$this->slug}_url", $url, $canonical, $this );

		return $url;
	}
}