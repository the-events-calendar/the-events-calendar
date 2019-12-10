<?php
/**
 * The base view class.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Template\Settings\Advanced_Display;
use Tribe\Events\Views\V2\Template\Title;
use Tribe__Container as Container;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;
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
	 * @since 4.9.11 Made the property static.
	 *
	 * @var bool
	 */
	protected static $publicly_visible = false;

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
	 * Whether the View instance should manage the URL
	 *
	 * @since 4.9.7
	 *
	 * @var bool
	 */
	protected $should_manage_url = true;

	/**
	 * An collection of user-facing messages the View should display.
	 *
	 * @since 4.9.11
	 *
	 * @var Messages
	 */
	protected $messages;

	/**
	 * Whether this View should reset the page/pagination or not.
	 * This acts as an instance cache for the `View::should_reset_page` method.
	 *
	 * @since 4.9.11
	 *
	 * @var bool
	 */
	protected $should_reset_page;

	/**
	 * Whether the View should display the events bar or not.
	 *
	 * @since 4.9.11
	 *
	 * @var bool
	 */
	protected $display_events_bar = true;

	/**
	 * The instance of the rewrite handling class to use.
	 * Extending classes can override this to use more specific rewrite handlers (e.g. PRO Views).
	 *
	 * @since 4.9.13
	 *
	 * @var Rewrite
	 */
	protected $rewrite;

	/**
	 * A flag property to indicate whether the View date is part of the "pretty" URL (true) or is supported only as
	 * a query argument like. `tribe-bar-date` (false).
	 *
	 * @var bool
	 */
	protected static $date_in_url = true;

	/**
	 * View constructor.
	 *
	 * @since 4.9.11
	 *
	 * @param Messages|null $messages An instance of the messages collection.
	 */
	public function __construct( Messages $messages = null ) {
		$this->messages = $messages ?: new Messages();
		$this->rewrite = Rewrite::instance();
	}

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
		$params     = $request->get_params();
		$slug       = Arr::get( $params, 'view', false );
		$url_object = Url::from_url_and_params( Arr::get( $params, 'url' ), $params );

		$url = $url_object->__toString();
		$params['url'] = $url;
		if ( isset( $params['view_data'] ) ) {
			$params['view_data']['url'] = $url;
		}

		$params = array_merge( $params, $url_object->get_query_args() );

		// Let View data override any other data.
		if ( isset( $params['view_data'] ) && is_array( $params['view_data'] ) ) {
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

		// Let's check if we have a display mode set.
		$query_args = $url_object->query_overrides_path( true )
		                         ->parse_url()
		                         ->get_query_args();

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

		if ( false === $slug ) {
			/*
			 * If we cannot get the view slug from the request parameters let's try to get it from the URL.
			 */
			$slug = Arr::get( $params, 'eventDisplay', tribe_context()->get( 'view', 'default' ) );
		}

		$params['event_display_mode'] = Arr::get( $query_args, 'eventDisplay', false );

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

		/** @var View $view */
		$view = static::make( $slug, $context );

		$view->url = $url_object;

		// Setup whether this view should manage URL or not, based on the Rest Request Sent.
		$view->should_manage_url = tribe_is_truthy( Arr::get( $params, 'should_manage_url', true ) );

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

		/**
		 * Run an action before we start making a new View instance.
		 *
		 * @since  4.9.11
		 *
		 * @param  string  $view_class The current view class.
		 * @param  string  $view_slug The current view slug.
		 */
		do_action( 'tribe_events_views_v2_before_make_view', $view_class, $view_slug );

		/** @var \Tribe\Events\Views\V2\View_Interface $instance */
		$instance = self::$container->make( $view_class );

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
		 * @since 4.9.11
		 *
		 * @param \Tribe__Repository__Interface $view_repository The repository instance the View will use.
		 * @param string                        $view_slug       The current view slug.
		 * @param \Tribe\Events\Views\V2\View   $instance        The current View object.
		 */
		$view_repository = apply_filters( 'tribe_events_views_v2_view_repository', $view_repository, $view_slug, $instance );

		/**
		 * Filters the Repository object for a specific View.
		 *
		 * @since 4.9.11
		 *
		 * @param \Tribe__Repository__Interface $view_repository The repository instance the View will use.
		 * @param \Tribe\Events\Views\V2\View   $instance        The current View object.
		 */
		$view_repository = apply_filters( "tribe_events_views_v2_{$view_slug}_view_repository", $view_repository, $instance );

		$instance->set_repository( $view_repository );

		/**
		 * Filters the query arguments array for a View URL.
		 *
		 * @since 4.9.11
		 *
		 * @param array                        $query_args  Arguments used to build the URL.
		 * @param string                       $view_slug   The current view slug.
		 * @param \Tribe\Events\Views\V2\View  $instance    The current View object.
		 */
		$view_url_query_args = apply_filters( 'tribe_events_views_v2_view_url_query_args', [], $view_slug, $instance );

		/**
		 * Filters the query arguments array for a specific View URL.
		 *
		 * @since 4.9.11
		 *
		 * @param array                        $query_args  Arguments used to build the URL.
		 * @param \Tribe\Events\Views\V2\View  $instance    The current View object.
		 */
		$view_url_query_args = apply_filters( "tribe_events_views_v2_{$view_slug}_view_url_query_args", $view_url_query_args, $instance );

		$instance->set_url( $view_url_query_args, true );

		/**
		 * Run an action after we are done making a new View instance.
		 *
		 * @since  4.9.11
		 *
		 * @param \Tribe\Events\Views\V2\View   $instance  The current View object.
		 */
		do_action( 'tribe_events_views_v2_after_make_view', $instance );

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

		if ( $this->should_reset_page() ) {

			/**
			 * Fires when the combination of the current request and View context requires a page reset.
			 *
			 * Additional information about the View current state and context are available using the View getter
			 * methods.
			 *
			 * @since 4.9.11
			 *
			 * @param View $this The current View instance.
			 * @param Context The View current context
			 */
			do_action( 'tribe_events_views_v2_on_page_reset', $this, $this->context );

			$this->on_page_reset();
		}

		$repository_args = $this->filter_repository_args( $this->setup_repository_args() );

		/*
		 * Some Views might need to access this out of this method, let's make the filtered repository arguments
		 * available.
		 */
		$this->repository_args = $repository_args;

		if ( ! tribe_events_view_v2_use_period_repository() ) {
			// @todo @bluedevs do we still need this? It's slow and time-consuming!
			$this->setup_the_loop( $repository_args );
		}

		$template_vars = $this->filter_template_vars( $this->setup_template_vars() );

		$this->template->set_values( $template_vars, false );

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
	public function get_parents_slug() {
		$parents = class_parents( $this );
		$parents = array_map( [ tribe( Manager::class ), 'get_view_slug_by_class' ], $parents );
		$parents = array_filter( $parents );

		return array_values( $parents );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html_classes( array $classes = [] ) {
		$base_classes = [
			'tribe-common',
			'tribe-events',
			'tribe-events-view',
			'tribe-events-view--' . $this->get_slug(),
		];

		$parents = array_map( static function ( $view_slug ) {
			return 'tribe-events-view--' . $view_slug;
		}, $this->get_parents_slug() );

		$html_classes = array_merge( $base_classes, $parents, $classes );

		/**
		 * Filters the query arguments array for a View URL.
		 *
		 * @since 4.9.13
		 *
		 * @param array                        $html_classes  Array of classes used for this view.
		 * @param string                       $view_slug     The current view slug.
		 * @param \Tribe\Events\Views\V2\View  $instance      The current View object.
		 */
		$html_classes = apply_filters( 'tribe_events_views_v2_view_html_classes', $html_classes, $this->get_slug(), $this );

		/**
		 * Filters the query arguments array for a specific View URL.
		 *
		 * @since 4.9.13
		 *
		 * @param array                        $html_classes  Array of classes used for this view.
		 * @param \Tribe\Events\Views\V2\View  $instance      The current View object.
		 */
		$html_classes = apply_filters( "tribe_events_views_v2_{$this->get_slug()}_view_html_classes", $html_classes, $this );

		return $html_classes;
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
	public function get_url( $canonical = false, $force = false ) {
		$query_args = [
			'post_type'        => TEC::POSTTYPE,
			'eventDisplay'     => $this->slug,
			'tribe-bar-date'   => $this->context->get( 'event_date', '' ),
			'tribe-bar-search' => $this->context->get( 'keyword', '' ),
		];

		if ( $is_featured = tribe_is_truthy( $this->context->get( 'featured', false ) ) ) {
			$query_args['featured'] = $is_featured;
		} else {
			unset( $query_args['featured'] );
		}

		/**
		 * Filters the query arguments that will be used to build a View URL.
		 *
		 * @since 4.9.10
		 *
		 * @param array          $query_args An array of query args that will be used to build the URL for the View.
		 * @param View_Interface $this       This View instance.
		 * @param bool           $canonical  Whether the URL should be the canonical one or not.
		 */
		$query_args = apply_filters( 'tribe_events_views_v2_url_query_args', $query_args, $this, $canonical );

		if ( ! empty( $query_args['tribe-bar-date'] ) ) {
			// If the Events Bar date is the same as today's date, then drop it.
			$today          = $this->context->get( 'today', 'today' );
			$url_date_format         = $this->get_url_date_format();
			$today_date     = Dates::build_date_object( $today ) ->format( $url_date_format );
			$tribe_bar_date = Dates::build_date_object( $query_args['tribe-bar-date'] ) ->format( $url_date_format );

			if ( static::$date_in_url ) {
				if ( $today_date !== $tribe_bar_date ) {
					// Default date is already today, no need to have it.
					$query_args['eventDate'] = $tribe_bar_date;
				}
				// Replace `tribe-bar-date` with `eventDate` as that's the query var used by the rewrite rules.
				unset( $query_args['tribe-bar-date'] );
			}
		}

		// When we find nothing we're always on page 1.
		$page = $this->repository->count() > 0 ? $this->url->get_current_page() : 1;

		if ( $page > 1 ) {
			$query_args[ $this->page_key ] = $page;
		}

		$url = add_query_arg( array_filter( $query_args ), home_url() );

		if ( $canonical ) {
			$url = $this->rewrite->get_clean_url( $url, $force );
		}

		$event_display_mode = $this->context->get( 'event_display_mode', false );
		if ( 'past' === $event_display_mode && $event_display_mode !== $this->context->get( 'eventDisplay' ) ) {
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

		$url = $this->get_url();

		if ( ! empty( $passthru_vars ) ) {
			// Remove the pass-thru vars, we'll re-apply them to the URL later.
			$url = remove_query_arg( array_keys( $passthru_vars ), $url );
		}

		// Make sure the view slug is always set to correctly match rewrites.
		$url = add_query_arg( [ 'eventDisplay' => $this->slug ], $url );

		$url = $next_page->count() > 0 ?
			add_query_arg( [ $this->page_key => $this->url->get_current_page() + 1 ], $url )
			: '';

		if ( ! empty( $url ) && $canonical ) {
			$url = tribe( 'events.rewrite' )->get_clean_url( $url );
		}

		if ( ! empty( $passthru_vars ) && ! empty( $url ) ) {
			// Re-apply the pass-thru query arguments.
			$url = add_query_arg( $passthru_vars, $url );
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

		$url = $this->get_url();

		if ( ! empty( $passthru_vars ) ) {
			// Remove the pass-thru vars, we'll re-apply them to the URL later.
			$url = remove_query_arg( array_keys( $passthru_vars ), $url );
		}

		// Make sure the view slug is always set to correctly match rewrites.
		$url = add_query_arg( [ 'eventDisplay' => $this->slug ], $url );

		$url = $prev_page->count() > 0 ? add_query_arg( $query_args, $url ) : '';

		if ( ! empty( $url ) && $paged === 1 ) {
			$url = remove_query_arg( $this->page_key, $url );
		}

		if ( ! empty( $url ) && $canonical ) {
			$url = tribe( 'events.rewrite' )->get_clean_url( $url );
		}

		if ( ! empty( $passthru_vars ) && ! empty( $url ) ) {
			// Re-apply the pass-thru query arguments.
			$url = add_query_arg( $passthru_vars, $url );
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
			'wp_query'   => $wp_query,
			'$_SERVER'   => isset( $_SERVER ) ? $_SERVER : []
		];

		$args = wp_parse_args( $args, $this->repository_args );

		$this->repository->by_args( $args );

		$this->set_url( $args, true );

		$wp_query = $this->repository->get_query();

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
	 * @param array|null $args   An associative array of arguments that will be mapped to the corresponding query
	 *                           arguments by the View, or `null` to use the current URL.
	 * @param bool       $merge  Whether to merge the arguments or override them.
	 */
	public function set_url( array $args = null, $merge = false ) {
		if ( ! isset( $this->url ) ) {
			$this->url = new Url();
		}

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

		$args = [
			'posts_per_page'       => $context_arr['events_per_page'],
			'paged'                => max( Arr::get_first_set( array_filter( $context_arr ), [
				'paged',
				'page',
			], 1 ), 1 ),
			'search'               => $context->get( 'keyword', '' ),
			'hidden_from_upcoming' => false,
		];

		// Set's up category URL for all views.
		if ( ! empty( $context_arr[ TEC::TAXONOMY ] ) ) {
			$args[ TEC::TAXONOMY ] = $context_arr[ TEC::TAXONOMY ];
		}

		// Setup featured only when set to true.
		if ( $is_featured = tribe_is_truthy( $this->context->get( 'featured', false ) ) ) {
			$args['featured'] = $is_featured;
		} else {
			unset( $args['featured'] );
		}

		return $args;
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
		 * @since 4.9.11
		 *
		 * @param string         $url       The View current URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( "tribe_events_views_v2_view_{$this->slug}_url", $url, $canonical, $this );

		return $url;
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
		 * @since 4.9.11
		 *
		 * @param string         $url       The View previous (page, event, etc.) URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( "tribe_events_views_v2_view_{$this->slug}_prev_url", $url, $canonical, $this );

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
		 * @since 4.9.11
		 *
		 * @param string         $url       The View next (page, event, etc.) URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( "tribe_events_views_v2_view_{$this->slug}_next_url", $url, $canonical, $this );

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
	public static function is_publicly_visible() {
		return static::$publicly_visible;
	}

	/**
	 * Sets up the View template variables.
	 *
	 * @since 4.9.4
	 *
	 * @return array An array of Template variables for the View Template.
	 */
	protected function setup_template_vars() {
		if ( empty( $this->repository_args ) ) {
			$this->repository_args = $this->filter_repository_args( $this->setup_repository_args() );
			$this->repository->by_args( $this->repository_args );
		}

		$events = (array) $this->repository->all();

		$this->setup_messages( $events );

		$today_url      = $this->get_today_url( true );
		$today          = $this->context->get( 'today', 'today' );

		$event_date = $this->context->get( 'event_date', false );
		$url_event_date = false !== $event_date
			? Dates::build_date_object( $event_date )->format( Dates::DBDATEFORMAT )
			: false;

		$template_vars  = [
			'title'                  => $this->get_title( $events ),
			'events'                 => $events,
			'url'                    => $this->get_url( true ),
			'prev_url'               => $this->prev_url( true ),
			'next_url'               => $this->next_url( true ),
			'url_event_date'         => $url_event_date,
			'bar'                    => [
				'keyword' => $this->context->get( 'keyword', '' ),
				'date'    => $this->context->get( 'event_date', '' ),
			],
			'today'                  => $today,
			'now'                    => $this->context->get( 'now', 'now' ),
			'request_date'           => Dates::build_date_object( $this->context->get( 'event_date', $today ) ),
			'rest_url'               => tribe( Rest_Endpoint::class )->get_url(),
			'rest_nonce'             => wp_create_nonce( 'wp_rest' ),
			'should_manage_url'      => $this->should_manage_url,
			'today_url'              => $today_url,
			'prev_label'             => $this->get_link_label( $this->prev_url( false ) ),
			'next_label'             => $this->get_link_label( $this->next_url( false ) ),
			'date_formats'           => (object) [
				'compact'              => Dates::datepicker_formats( tribe_get_option( 'datepickerFormat' ) ),
				'month_and_year'       => tribe_get_date_option( 'monthAndYearFormat', 'F Y' ),
				'time_range_separator' => tribe_get_date_option( 'timeRangeSeparator', ' - ' ),
				'date_time_separator'  => tribe_get_date_option( 'dateTimeSeparator', ' @ ' ),
			],
			'messages'               => $this->get_messages( $events ),
			'start_of_week'          => get_option( 'start_of_week', 0 ),
			'breadcrumbs'            => $this->get_breadcrumbs(),
			'before_events'          => tribe( Advanced_Display::class )->get_before_events_html( $this ),
			'after_events'           => tribe( Advanced_Display::class )->get_after_events_html( $this ),
			'display_events_bar'     => $this->filter_display_events_bar( $this->display_events_bar ),
			'disable_event_search'   => tribe_is_truthy( tribe_get_option( 'tribeDisableTribeBar', false ) ),
			'live_refresh'           => tribe_is_truthy( tribe_get_option( 'liveFiltersUpdate', true ) ),
			'ical'                   => $this->get_ical_data(),
			'container_classes'      => $this->get_html_classes(),
			'is_past'                => 'past' === $this->context->get( 'event_display_mode', false ),
			'show_datepicker_submit' => $this->get_show_datepicker_submit(),
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
					$this->rewrite->get_clean_url( (string) $this->get_url() ) ),
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

	/**
	 * {@inheritDoc}
	 */
	public function get_template_vars() {
		return $this->filter_template_vars( $this->setup_template_vars() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_today_url( $canonical = false ) {
		$to_remove = [ 'tribe-bar-date', 'paged', 'page', 'eventDate' ];

		// While we want to remove the date query vars, we want to keep any other query var.
		$query_args = $this->url->get_query_args();

		// Handle the `eventDisplay` query arg due to its particular usage to indicate the mode too.
		$query_args['eventDisplay'] = $this->slug;

		$ugly_url = add_query_arg( $query_args, $this->get_url( false ) );
		$ugly_url = remove_query_arg( $to_remove, $ugly_url );

		if ( ! $canonical ) {
			return $ugly_url;
		}

		return $this->rewrite->get_canonical_url( $ugly_url );
	}

	/**
	 * Builds the link label to use from the URL.
	 *
	 * This is usually used to build the next and prev link URLs labels.
	 * Extending classes can customize the format of the the label by overriding the `get_label_format` method.
	 *
	 * @todo  @bordoni move this method to a supporting class.
	 *
	 * @see View::get_label_format(), the method child classes should override to customize the link label format.
	 *
	 * @since 4.9.9
	 *
	 * @param string $url The input URL to build the link label from.
	 *
	 * @return string The formatted and localized, but not HTML escaped, link label.
	 */
	public function get_link_label( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		$url_query = parse_url( $url, PHP_URL_QUERY );

		if ( empty( $url_query ) ) {
			return '';
		}

		parse_str( $url_query, $args );

		$date = Arr::get_first_set( $args, [ 'eventDate', 'tribe-bar-date' ], false );

		if ( false === $date ) {
			return '';
		}

		$date_object = Dates::build_date_object( $date );

		$format = $this->get_label_format();

		/**
		 * Filters the `date` format that will be used to produce a View link label for a View.
		 *
		 * @since 4.9.11
		 *
		 * @param string    $format    The label format the View will use to product a View link label; e.g. the
		 *                             previous and next links.
		 * @param \DateTime $date      The date object that is being used to build the label.
		 * @param View      $view      This View instance.
		 */
		$format = apply_filters( "tribe_events_views_v2_view_link_label_format", $format, $this, $date );

		/**
		 * Filters the `date` format that will be used to produce a View link label for a specific View.
		 *
		 * @since 4.9.11
		 *
		 * @param string    $format    The label format the View will use to product a View link label; e.g. the
		 *                             previous and next links.
		 * @param \DateTime $date      The date object that is being used to build the label.
		 * @param View      $view      This View instance.
		 */
		$format = apply_filters( "tribe_events_views_v2_view_{$this->slug}_link_label_format", $format, $this, $date );

		return date_i18n( $format, $date_object->getTimestamp() + $date_object->getOffset() );
	}

	/**
	 * Returns the date format, a valid PHP `date` function format, that should be used to build link labels.
	 *
	 * This format will, usually, apply to next and previous links.
	 *
	 * @todo  @bordoni move this method to a supporting class.
	 *
	 * @see View::get_link_label(), the method using this method to build a link label.
	 * @see date_i18n() as the formatted date will, then, be localized using this method.
	 *
	 * @since 4.9.9
	 *
	 * @return string The date format, a valid PHP `date` function format, that should be used to build link labels.
	 */
	protected function get_label_format() {
		return 'Y-m-d';
	}

	/**
	 * Gets this View title, the one that will be set in the `title` tag of the page.
	 *
	 * @since 4.9.10
	 *
	 * @param  array $events An array of events to generate the title for.
	 *
	 * @return string The filtered view title.
	 */
	public function get_title( array $events = [] ) {
		if ( ! $this->context->doing_php_initial_state() ) {
			/** @var Title $title_filter */
			$title_filter = static::$container->make( Title::class )
			                                  ->set_context( $this->context )
			                                  ->set_posts( $events );

			add_filter( 'document_title_parts', [ $title_filter, 'filter_document_title_parts' ] );
			// We disable the filter to avoid the double encoding that would come from our preparation of the data.
			add_filter( 'run_wptexturize', '__return_false' );
		}

		$title = wp_get_document_title();

		if ( isset( $title_filter ) ) {
			remove_filter( 'run_wptexturize', '__return_false' );
			remove_filter( 'document_title_parts', [ $title_filter, 'filter_document_title_parts' ] );
		}

		$slug = $this->get_slug();

		/**
		 * Filters the title for all views.
		 *
		 * @since 4.9.11
		 *
		 * @param string $title This view filtered title.
		 * @param View   $this  This view object.
		 */
		$title = apply_filters( "tribe_events_views_v2_view_title", $title, $this );

		/**
		 * Filters the title for this view.
		 *
		 * @since 4.9.11
		 *
		 * @param string $title This view filtered title.
		 * @param View   $this  This view object.
		 */
		$title = apply_filters( "tribe_events_views_v2_view_{$slug}_title", $title, $this );

		return htmlspecialchars_decode($title);
	}

	/**
	 * Returns a collection of user-facing messages the View will display on the front-end.
	 *
	 * @since 4.9.11
	 *
	 * @param array $events An array of the events found by the View that is currently rendering.
	 *
	 * @return Messages A collection of user-facing messages the View will display on the front-end.
	 */
	public function get_messages( array $events = [] ) {
		$slug = $this->get_slug();

		/**
		 * Fires before the view "renders" the array of user-facing messages.
		 *
		 * Differently from the filters below this action allow manipulating the messages handler before the messages
		 * render to, as an example, change rendering strategy and manipulate the message "ingredients".
		 *
		 * @since 4.9.11
		 *
		 * @param Messages $messages The object instance handling the messages for the View.
		 * @param array    $events   An array of the events found by the View that is currently rendering.
		 * @param View     $this     The View instance currently rendering.
		 */
		do_action( 'tribe_events_views_v2_view_messages_before_render', $this->messages, $events, $this );

		$messages = $this->messages->to_array();

		/**
		 * Filters the user-facing messages the View will print on the frontend.
		 *
		 * @since 4.9.11
		 *
		 * @param array $messages An array of messages in the shape `[ <message_type> => [ ...<messages> ] ]`.
		 * @param View $this The current View instance being rendered.
		 * @param Messages $messages_handler The messages handler object the View used to render the messages.
		 */
		$messages = apply_filters( 'tribe_events_views_v2_view_messages', $messages, $this, $this->messages );

		/**
		 * Filters the user-facing messages a specific View will print on the frontend.
		 *
		 * @since 4.9.11
		 *
		 * @param array    $messages         An array of messages in the shape `[ <message_type> => [ ...<messages> ] ]`.
		 * @param array    $events           An array of the events found by the View that is currently rendering.
		 * @param View     $this             The current View instance being rendered.
		 * @param Messages $messages_handler The messages handler object the View used to render the messages.
		 */
		$messages = apply_filters( "tribe_events_views_v2_view_{$slug}_messages", $messages, $events, $this, $this->messages );

		return $messages;
	}

	/**
	 * Sets up the user-facing messages the View will print on the frontend.
	 *
	 * @since 4.9.11
	 *
	 * @param array $events An array of the View events, if any.
	 */
	protected function setup_messages( array $events ) {
		if ( empty( $events ) ) {
			$keyword = $this->context->get( 'keyword', false );
			if ( $keyword ) {
				$this->messages->insert( Messages::TYPE_NOTICE, Messages::for_key( 'no_results_found_w_keyword', trim( $keyword ) ) );
			} else {
				$this->messages->insert( Messages::TYPE_NOTICE, Messages::for_key( 'no_results_found' ) );
			}
		}
	}

	/**
	 * Returns whether the View page should be reset or not.
	 *
	 * The View page should be reset when the View or filtering parameters that are not the page change.
	 *
	 * @since 4.9.11
	 *
	 * @return bool Whether the View page should be reset or not.
	 */
	protected function should_reset_page() {
		if ( null === $this->should_reset_page ) {
			$prev_url    = $this->context->get( 'view_prev_url', '' );
			$current_url = $this->context->get( 'view_url', '' );

			$view_data = $this->context->get( 'view_data', [] );
			$bar_data  = array_filter(
				$view_data,
				static function ( $value, $key ) {
					return 0 === strpos( $key, 'tribe-bar-' ) && ! empty( $value );
				},
				ARRAY_FILTER_USE_BOTH
			);
			if ( ! empty( $bar_data ) ) {
				$current_url = add_query_arg( $bar_data, $current_url );
			}

			$this->should_reset_page = Url::is_diff( $prev_url, $current_url, [ 'page', 'paged' ] );
		}

		return $this->should_reset_page;
	}

	/**
	 * Acts on the View variables, properties and context when a page reset is required.
	 *
	 * By default this method will reset the page in the context, but extending classes can implement their own,
	 * custom version.
	 *
	 * @since 4.9.11
	 */
	protected function on_page_reset() {
		if ( ! isset( $this->context ) || ! $this->context instanceof Context ) {
			return;
		}

		$url                      = $this->context->get( 'url', home_url() );
		$updated_url              = remove_query_arg( [ 'paged', 'page' ], $url );
		$view_data                = $this->context->get( 'view_data', [] );
		$alterations              = [
			'page'  => 1,
			'paged' => 1,
			'url'   => $updated_url,
		];
		$alterations['view_data'] = array_merge( $view_data, $alterations );

		$this->context = $this->context->alter( $alterations );
	}

	/**
	 * Returns the breadcrumbs data the View will display on the front-end.
	 *
	 * @since 4.9.11
	 *
	 * @return array
	 */
	protected function get_breadcrumbs() {
		$context     = $this->context;
		$breadcrumbs = [];
		$context_tax = $context->get( TEC::TAXONOMY, false );

		// Get term slug if taxonomy is not empty
		if ( ! empty( $context_tax ) ) {
			$taxonomy  = $context->get( 'taxonomy', false );
			$term_slug = $taxonomy ? $context->get( $taxonomy, false ) : false;

			// Set up breadcrumbs for category
			if ( ! empty( $term_slug ) ) {
				$term  = get_term_by( 'slug', $term_slug, $taxonomy );
				if ( ! empty( $term->name ) ) {
					$label = $term->name;

					$breadcrumbs[] = [
						'link'  => $this->get_today_url( true ),
						'label' => tribe_get_event_label_plural(),
					];
					$breadcrumbs[] = [
						'link'  => '',
						'label' => $label,
					];
				}
			}
		}

		// Setup breadcrumbs for when it's featured.
		if ( $is_featured = tribe_is_truthy( $this->context->get( 'featured', false ) ) ) {
			$non_featured_link = tribe_events_get_url( [ 'featured' => 0 ] );

			$breadcrumbs[] = [
				'link'  => $non_featured_link,
				'label' => tribe_get_event_label_plural(),
			];
			$breadcrumbs[] = [
				'link'  => '',
				'label' => esc_html__( 'Featured', 'the-events-calendar' ),
			];
		}

		/**
		 * Filters the breadcrumbs the View will print on the frontend.
		 *
		 * @since 4.9.11
		 *
		 * @param array $breadcrumbs An array of breadcrumbs.
		 * @param View  $this        The current View instance being rendered.
		 */
		$breadcrumbs = apply_filters( 'tribe_events_views_v2_view_breadcrumbs', $breadcrumbs, $this );

		/**
		 * Filters the breadcrumbs a specific View will print on the frontend.
		 *
		 * @since 4.9.11
		 *
		 * @param array $breadcrumbs An array of breadcrumbs.
		 * @param View  $this        The current View instance being rendered.
		 */
		$breadcrumbs = apply_filters( "tribe_events_views_v2_view_{$this->slug}_breadcrumbs", $breadcrumbs, $this );

		return $breadcrumbs;
	}

	/**
	 * Returns if the view should display the events bar.
	 *
	 * @since 4.9.11
	 *
	 * @return array
	 */
	protected function filter_display_events_bar( $display ) {

		/**
		 * Filters if the events bar should be displayed.
		 *
		 * @since 4.9.11
		 *
		 * @param bool $display An bool saying if it should be displayed or not.
		 * @param View $this    The current View instance being rendered.
		 */
		$display = apply_filters( "tribe_events_views_v2_view_display_events_bar", $display, $this );

		/**
		 * Filters if the events bar should be displayed for the specific view.
		 *
		 * @since 4.9.11
		 *
		 * @param bool $display An bool saying if it should be displayed or not.
		 * @param View $this    The current View instance being rendered.
		 */
		$display = apply_filters( "tribe_events_views_v2_view_{$this->slug}_display_events_bar", $display, $this );

		return $display;
	}

	/**
	 * Returns the iCal data we're sending to the view.
	 *
	 * @todo  @bordoni move this method to a supporting class.
	 *
	 * @since 4.9.13
	 *
	 * @return object
	 */
	protected function get_ical_data() {
		/**
		 * A filter to control whether the "iCal Import" link shows up or not.
		 *
		 * @since unknown
		 *
		 * @param boolean $show Whether to show the "iCal Import" link; defaults to true.
		 */
		$display_ical = apply_filters( 'tribe_events_list_show_ical_link', true );

		/**
		 * Allow for customization of the iCal export link "Export Events" text.
		 *
		 * @since unknown
		 *
		 * @param string $text The default link text, which is "Export Events".
		 */
		$link_text  = apply_filters( 'tribe_events_ical_export_text', __( 'Export Events', 'the-events-calendar' ) );

		$link_title = __( 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps', 'the-events-calendar' );

		$ical_data = (object) [
			'display_link' => $display_ical,
			'link'         => (object) [
				'url'   => esc_url( tribe_get_ical_link() ),
				'text'  => $link_text,
				'title' => $link_title,
			],
		];

		/**
		 * Filters the ical data.
		 *
		 * @since 4.9.13
		 *
		 * @param object $ical_data An object containing the ical data.
		 * @param View   $this      The current View instance being rendered.
		 */
		$ical_data = apply_filters( "tribe_events_views_v2_view_ical_data", $ical_data, $this );

		/**
		 * Filters the ical data for a specific view.
		 *
		 * @since 4.9.13
		 *
		 * @param object $ical_data An object containing the ical data.
		 * @param View   $this      The current View instance being rendered.
		 */
		$ical_data = apply_filters( "tribe_events_views_v2_view_{$this->slug}_ical_data", $ical_data, $this );

		return $ical_data;
	}

	/**
	 * Returns a boolean on whether to show the datepicker submit button.
	 *
	 * @since 4.9.13
	 *
	 * @return bool
	 */
	protected function get_show_datepicker_submit() {
		$live_refresh       = tribe_is_truthy( tribe_get_option( 'liveFiltersUpdate', true ) );
		$disable_events_bar = tribe_is_truthy( tribe_get_option( 'tribeDisableTribeBar', false ) );

		if ( empty( $live_refresh ) && ! empty( $disable_events_bar ) ) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function url_for_query_args( $date = null, $query_args = [] ) {
		$event_date = Dates::build_date_object( $date )->format( $this->get_url_date_format() );

		if ( ! empty( $query_args ) && is_string( $query_args ) ) {
			$str_args   = $query_args;
			$query_args = [];
			wp_parse_str( $str_args, $query_args );
		}

		$url_query_args = array_filter( array_merge( $query_args, [
			'eventDisplay' => $this->get_slug(),
			'eventDate'    => $event_date,
		] ) );

		if ( static::$date_in_url ) {
			unset( $url_query_args['tribe-bar-date'] );

			// This is the case for Views that include the date in the "pretty" URL, e.g. Month, Day or Week.
			return tribe_events_get_url( $url_query_args );
		}

		// This is the case for Views that don't include the date in the "pretty" URL, e.g. List.
		unset( $url_query_args['eventDate'] );

		return add_query_arg(
			[ 'tribe-bar-date' => $event_date ],
			tribe_events_get_url( $url_query_args )
		);
	}

	/**
	 * Returns the date format that should be used to format the date in the View URL.
	 *
	 * Extending Views cal override this to customize the URL output (e.g. Month View).
	 *
	 * @since 4.9.13
	 *
	 * @return string The date format that should be used to format the date in the View URL.
	 */
	protected function get_url_date_format() {
		return Dates::DBDATEFORMAT;
	}
}
