<?php
/**
 * The base view class.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

use Tribe__Container as Container;
use Tribe__Context as Context;
use Tribe__Events__Main as TEC;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Rewrite as Rewrite;
use Tribe__Events__Venue as Venue;
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
	 * The template slug the View instance will use to locate its template files.
	 *
	 * This value will be set by the `View::make()` method while building a View instance.
	 *
	 * @var string
	 */
	protected $template_slug;

	/**
	 * The Template instance the view will use to locate, manage and render its template.
	 *
	 * This value will be set by the `View::make()` method while building a View instance.
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
	 * Whether a given View is visible publicly or not.
	 *
	 * @since 4.9.4
	 *
	 * @var bool
	 */
	protected $publicly_visible = false;

	/**
	 * An associative array of the arguments used to setup the repository filters.
	 *
	 * @since 4.9.3
	 *
	 * @var array
	 */
	protected $repository_args = [];

	/**
	 * The key that should be used to indicate the page in an archive.
	 * Extending classes should not need to modify this.
	 *
	 * @since 4.9.4
	 *
	 * @var string
	 */
	protected $page_key = 'paged';

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

		// Let View data override any other data.
		if ( isset( $params['view_data'] ) ) {
			$params = array_merge( $params, $params['view_data'] );
		}

		/*
		 * WordPress would replicate the `post_name`, when resolving the request, both as `name` and as the post type.
		 * We emulate this behavior here hydrating the request context to provide a `name` alongside the post type.
		 */
		$post_name = array_intersect( array_keys( $params ), [ TEC::POSTTYPE, Venue::POSTTYPE, Organizer::POSTTYPE ] );
		if ( ! empty( $post_name ) && count( $post_name ) === 1 ) {
			$params['name'] = $params[ reset( $post_name ) ];
		}

		if ( false === $slug ) {
			/*
			 * If we cannot get the view slug from the request parameters let's try to get it from the URL.
			 */
			$slug = Arr::get( $params, 'eventDisplay', tribe_context()->get( 'view', 'default' ) );
		}

		// Let's check if we have a display mode set.
		$query_args = $url_object->query_overrides_path( true )
		                         ->parse_url()
		                         ->get_query_args();

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

		// Determine context based on the request parameters.
		$do_not_override = [ 'event_display_mode' ];
		$not_overrideable_params = array_intersect_key( $params, array_combine( $do_not_override, $do_not_override ) );
		$context = tribe_context()
			->alter(
				array_merge(
					$params,
					tribe_context()->translate_sub_locations( $params, \Tribe__Context::REQUEST_VAR ),
					$not_overrideable_params
				)
			);

		$view = static::make( $slug, $context );

		$view->url = $url_object;

		// Setup whether this view should manage URL or not, based on the Rest Request Sent.
		$view->get_template()->set( 'should_manage_url', tribe_is_truthy( Arr::get( $params, 'should_manage_url', true ) ) );

		return $view;
	}

	/**
	 * Builds and returns an instance of a View by slug or class.
	 *
	 * @since  4.9.2
	 *
	 * @param  string  $view  The view slug, as registered in the `tribe_events_views` filter, or class.
	 * @param  \Tribe__Context|null  $context  The context this view should render from; if not set then the global
	 *                                         one will be used.
	 *
	 * @return \Tribe\Events\Views\V2\View_Interface An instance of the built view.
	 */
	public static function make( $view = null, \Tribe__Context $context = null ) {
		$manager = tribe( Manager::class );

		$default_view = $manager->get_default_view();
		if (
			null === $view
			|| 'default' === $view
		) {
			$view = $default_view;
		}

		list( $view_slug, $view_class ) = $manager->get_view( $view );

		// When not found use the default view.
		if ( ! $view_class ) {
			list( $view_slug, $view_class ) = $manager->get_view( $default_view );
		}

		// Make sure we are using Reflector when it fails
		if ( ! class_exists( $view_class ) ) {
			list( $view_slug, $view_class ) = $manager->get_view( 'reflector' );
		}

		if ( ! self::$container instanceof Container ) {
			$message = 'The ' . __CLASS__ . '::$container property is not set: was the class initialized by the service provider?';
			throw new \RuntimeException( $message );
		}

		/** @var \Tribe\Events\Views\V2\View_Interface $instance */
		$instance  = self::$container->make( $view_class );

		$template = new Template( $instance );

		/**
		 * Filters the Template object for a View.
		 *
		 * @since  4.9.3
		 *
		 * @param  \Tribe\Events\Views\V2\Template  $template  The template object for the View.
		 * @param  string                           $view_slug The current view slug.
		 * @param  \Tribe\Events\Views\V2\View      $instance  The current View object.
		 */
		$template = apply_filters( 'tribe_events_views_v2_view_template', $template, $view_slug, $instance );

		/**
		 * Filters the Template object for a specific View.
		 *
		 * @since  4.9.3
		 *
		 * @param  \Tribe\Events\Views\V2\Template  $template  The template object for the View.
		 * @param  \Tribe\Events\Views\V2\View      $instance  The current View object.
		 */
		$template = apply_filters( "tribe_events_views_v2_{$view_slug}_view_template", $template, $instance );

		$instance->set_template( $template );
		$instance->set_slug( $view_slug );
		$instance->set_template_slug( $view_slug );

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
		$view_context = apply_filters( 'tribe_events_views_v2_view_context', $view_context, $view_slug, $instance );

		/**
		 * Filters the Context object for a specific View.
		 *
		 * @since 4.9.3
		 *
		 * @param  \Tribe__Context              $view_context  The context abstraction object that will be passed to the
		 *                                                     view.
		 * @param  \Tribe\Events\Views\V2\View  $instance      The current View object.
		 */
		$view_context = apply_filters( "tribe_events_views_v2_{$view_slug}_view_context", $view_context, $instance );

		$instance->set_context( $view_context );

		// This code is coupled with the idea of viewing events: that's fine as Events are the default view content.
		$view_repository = tribe_events();
		$view_repository->order_by( 'event_date', 'ASC' );

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
		$view_repository = apply_filters( "tribe_events_views_v2_{$view_slug}_view_context", $view_repository, $instance );

		$instance->set_repository( $view_repository );

		$instance->set_url();

		return $instance;
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
	 * Sends, echoing it and exiting, the view HTML on the page.
	 *
	 * @since 4.9.2
	 *
	 * @param null|string $html A specific HTML string to print on the page or the HTML produced by the view
	 *                          `get_html` method.
	 *
	 */
	public function send_html( $html = null ) {
		$html = null === $html ? $this->get_html() : $html;
		echo $html;
		tribe_exit( 200 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		if ( self::class === static::class ) {
			return $this->template->render();
		}

		$repository_args = $this->filter_repository_args( $this->setup_repository_args() );
		$this->setup_repository_args();

		$this->setup_the_loop( $repository_args );

		$this->setup_repository_args();

		$template_vars = $this->filter_template_vars( $this->setup_template_vars() );

		$this->template->set_values( $template_vars, false );

		$this->setup_repository_args();

		$html = $this->template->render();

		$this->restore_the_loop();

		return $html;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		$label = ucfirst( $this->slug );

		/**
		 * Pass by the translation engine, dont remove.
		 */
		$label = __( $label, 'the-events-calendar' );

		/**
		 * Filters the label that will be used on the UI for views listing.
		 *
		 * @since 4.9.4
		 *
		 * @param string         $label  Label of the Current view.
		 * @param View_Interface $view   The current view whose template variables are being set.
		 */
		$label = apply_filters( 'tribe_events_views_v2_view_label', $label, $this );

		/**
		 * Filters the label that will be used on the UI for views listing.
		 *
		 * @since 4.9.4
		 *
		 * @param string         $label  Label of the Current view.
		 * @param View_Interface $view   The current view whose template variables are being set.
		 */
		$label = apply_filters( "tribe_events_views_v2_view_{$this->slug}_label", $label, $this );

		return $label;
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
			'post_type'        => TEC::POSTTYPE,
			'eventDisplay'     => $this->slug,
			'tribe-bar-date'   => $this->context->get( 'event_date', '' ),
			'tribe-bar-search' => $this->context->get( 'keyword', '' ),
		];

		// When we find nothing we're always on page 1.
		$page = $this->repository->count() > 0 ? $this->url->get_current_page() : 1;

		if ( $page > 1 ) {
			$query_args[ $this->page_key ] = $page;
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

		$url            = $next_page->count() > 0 ?
			add_query_arg( [ $this->page_key => $this->url->get_current_page() + 1 ], $this->get_url() )
			: '';

		if ( ! empty( $url ) && $canonical ) {
			$input_url = $url;

			if ( ! empty( $passthru_vars ) ) {
				$input_url = remove_query_arg( array_keys( $passthru_vars ), $url );
			}

			// Make sure the view slug is always set to correctly match rewrites.
			$input_url = add_query_arg( [ 'eventDisplay' => $this->slug ], $input_url );

			$canonical_url = tribe( 'events.rewrite' )->get_clean_url( $input_url );

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
			? [ $this->page_key => $paged ]
			: [];

		$url = $prev_page->count() > 0 ?
			add_query_arg( $query_args, $this->get_url() )
			: '';

		if ( ! empty( $url ) && $paged === 1 ) {
			$url = remove_query_arg( $this->page_key, $url );
		}

		if ( ! empty( $url ) && $canonical ) {
			$input_url = $url;

			if ( ! empty( $passthru_vars ) ) {
				$input_url = remove_query_arg( array_keys( $passthru_vars ), $url );
			}

			// Make sure the view slug is always set to correctly match rewrites.
			$input_url = add_query_arg( [ 'eventDisplay' => $this->slug ], $input_url );

			$canonical_url = tribe( 'events.rewrite' )->get_clean_url( $input_url );

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
			'wp_query' => $wp_query,
			'$_SERVER' => isset( $_SERVER ) ? $_SERVER : [],
		];

		$args = wp_parse_args( $args, $this->repository_args );

		$this->repository->by_args( $args );

		$this->set_url( $args, true );

		/**
		 * Problematic replacement as context relies on that to have access to the variables
		 * in the global context, which creates a hard problem to do navigation.
		 *
		 * @todo  have conversation with @lucatume about this
		 */
		// $wp_query = $this->repository->get_query();
		wp_reset_postdata();

		// Set the $_SERVER['REQUEST_URI'] as many WordPress functions rely on it to correctly work.
		$_SERVER['REQUEST_URI'] = $this->get_request_uri();

		// Make the template global to power template tags.
		global $tribe_template;
		$tribe_template = $this->template;
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

			// We use both `paged` and `page` for pagination: let's make sure to keep the required one only.
			if ( isset( $args['paged'] ) ) {
				unset( $query_args['page'] );
			}
			if ( isset( $args['page'] ) ) {
				unset( $query_args['paged'] );
			}

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
		 * @since 4.9.4
		 *
		 * @param array          $template_vars An associative array of template variables. Variables will be extracted in the
		 *                                      template hence the key will be the name of the variable available in the
		 *                                      template.
		 * @param View_Interface $view          The current view whose template variables are being set.
		 */
		$template_vars = apply_filters( 'tribe_events_views_v2_view_template_vars', $template_vars, $this );

		/**
		 * Filters the variables that will be set on the View template.
		 *
		 * @since 4.9.3
		 * @since 4.9.4 Renamed the filter to be aligned with other filters on this class.
		 *
		 * @param array          $template_vars An associative array of template variables. Variables will be extracted in the
		 *                                      template hence the key will be the name of the variable available in the
		 *                                      template.
		 * @param View_Interface $view          The current view whose template variables are being set.
		 */
		$template_vars = apply_filters( "tribe_events_views_v2_view_{$this->slug}_template_vars", $template_vars, $this );

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
	protected function filter_prev_url( $canonical, $url ) {
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
	protected function filter_next_url( $canonical, $url ) {
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
	 */
	protected function setup_repository_args( \Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		$context_arr = $context->to_array();

		return [
			'posts_per_page' => $context_arr['posts_per_page'],
			'paged'          => max( Arr::get_first_set( $context_arr, [ 'paged', 'page' ], 1 ), 1 ),
			'search'         => $context->get( 'keyword', '' ),
		];
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
	protected function filter_view_url( $canonical, $url ) {
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

	/**
	 * {@inheritDoc}
	 */
	public function found_post_ids() {
		return $this->repository->get_ids();
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_publicly_visible() {
		return $this->publicly_visible;
	}

	/**
	 * Sets up the View template variables.
	 *
	 * @since 4.9.4
	 *
	 * @return array An array of Template variables for the View Template.
	 */
	protected function setup_template_vars() {
		$template_vars = [
			'title'    => wp_title( null, false ),
			'events'   => $this->repository->all(),
			'url'      => $this->get_url( true ),
			'prev_url' => $this->prev_url( true ),
			'next_url' => $this->next_url( true ),
			'bar'      => [
				'keyword' => $this->context->get( 'keyword', '' ),
				'date'    => $this->context->get( 'event_date', '' ),
			],
		];

		return $template_vars;
	}


	/**
	 * Filters the repository arguments that will be used to set up the View repository instance.
	 *
	 * @since 4.9.5
	 *
	 * @param array        $repository_args The repository arguments that will be used to set up the View repository instance.
	 * @param Context|null $context Either a specific Context or `null` to use the View current Context.
	 *
	 * @return array The filtered repository arguments.
	 */
	protected function filter_repository_args( array $repository_args, \Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		/**
		 * Filters the repository args for a View.
		 *
		 * @since 4.9.5
		 *
		 * @param array           $repository_args An array of repository arguments that will be set for all Views.
		 * @param \Tribe__Context $context         The current render context object.
		 * @param View_Interface  $this            The View that will use the repository arguments.
		 */
		$repository_args = apply_filters( 'tribe_events_views_v2_view_repository_args', $repository_args, $context, $this );

		/**
		 * Filters the repository args for a specific View.
		 *
		 * @since 4.9.5
		 *
		 * @param array           $repository_args An array of repository arguments that will be set for a specific View.
		 * @param \Tribe__Context $context         The current render context object.
		 * @param View_Interface  $this            The View that will use the repository arguments.
		 */
		$repository_args = apply_filters(
			"tribe_events_views_v2_view_{$this->slug}_repository_args",
			$repository_args,
			$context,
			$this
		);

		return $repository_args;
	}

	/**
	 * Returns the View request URI.
	 *
	 * This value can be used to set the `$_SERVER['REQUEST_URI']` global when rendering the View to make sure WordPress
	 * functions relying on that value will work correctly.
	 *
	 * @since 4.9.5
	 *
	 * @return string The View request URI, a value suitable to be used to set the `$_SERVER['REQUEST_URI']` value.
	 */
	protected function get_request_uri() {
		$request_uri = '/' . ltrim(
				str_replace(
					home_url(),
					'',
					Rewrite::$instance->get_clean_url( (string) $this->get_url() ) ),
				'/'
			);

		return $request_uri;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template_slug() {
		if ( null !== $this->template_slug ) {
			return $this->template_slug;
		}

		return $this->get_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_template_slug( $template_slug ) {
		$this->template_slug = $template_slug;
	}
}
