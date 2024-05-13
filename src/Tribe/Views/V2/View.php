<?php
/**
 * The base view class.
 *
 * @since   4.9.2
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

use TEC\Common\Configuration\Configuration;
use Tribe\Events\Models\Post_Types\Event;
use Tribe\Events\Views\V2\Template\Settings\Advanced_Display;
use Tribe\Events\Views\V2\Template\Title;
use Tribe\Events\Views\V2\Utils;
use Tribe\Events\Views\V2\Views\Latest_Past_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Events\Views\V2\Views\Reflector_View;
use Tribe\Events\Views\V2\Views\Traits\Breakpoint_Behavior;
use Tribe\Events\Views\V2\Views\Traits\HTML_Cache;
use Tribe\Events\Views\V2\Views\Traits\iCal_Data;
use Tribe\Events\Views\V2\Views\Traits\Json_Ld_Data;
use Tribe\Utils\Taxonomy;
use Tribe__Cache;
use Tribe__Cache_Listener;
use Tribe__Container as Container;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Rewrite as TEC_Rewrite;
use Tribe__Events__Venue as Venue;
use Tribe__Repository__Interface as Repository;
use Tribe__Utils__Array as Arr;
use WP_Post;

/**
 * Class View
 *
 * @since   4.9.2
 * @package Tribe\Events\Views\V2
 */
class View implements View_Interface {

	use Breakpoint_Behavior;
	use HTML_Cache;
	use iCal_Data;
	use Json_Ld_Data;

	/**
	 * An instance of the DI container.
	 *
	 * @var Container
	 */
	protected static $container;

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
	 * @deprecated 6.0.7
	 *
	 * @var string
	 */
	protected $slug = 'view';

	/**
	 * The static instance of the View instance slug, usually the one it was registered with in the `tribe_events_views`filter.
	 *
	 * This value will be set by the `View::make()` method while building a View instance.
	 *
	 * @since 6.0.7
	 *
	 * @var string
	 */
	protected static $view_slug = 'view';

	/**
	 * The template slug the View instance will use to locate its template files.
	 *
	 * This value will be set by the `View::make()` method while building a View instance.
	 *
	 * @var string
	 */
	protected $template_slug;

	/**
	 * The template path will be used as a prefix for template slug when locating its template files.
	 *
	 * @since 5.2.1
	 *
	 * @var string
	 */
	protected $template_path = '';

	/**
	 * The Template instance the view will use to locate, manage and render its template.
	 *
	 * This value will be set by the `View::make()` method while building a View instance.
	 *
	 * @var Template
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
	 * @var Url
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
	 * Stores the filtered global repository args. These args will be baked into all event queries.
	 *
	 * @since 6.0.5
	 *
	 * @var null|array<string,mixed> Will be null until compiled by the getter.
	 */
	protected $global_repository_args = null;

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
	 * Indicates whether there are more events beyond the current view
	 *
	 * @since 5.0.0
	 *
	 * @var bool
	 */
	protected $has_next_event = false;

	/**
	 * Whether the View instance should manage the URL
	 *
	 * @since 4.9.7
	 *
	 * @var bool
	 */
	protected $should_manage_url = true;

	/**
	 * A collection of user-facing messages the View should display.
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
	 * @var TEC_Rewrite
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
	 * Cached URLs
	 *
	 * @since 5.0.0
	 *
	 * @var array
	 */
	protected $cached_urls = [];

	/**
	 * The translated label string for the view.
	 * Subject to later filters.
	 *
	 * @since 6.0.4
	 *
	 * @var string
	 */
	protected static $label = 'View';

	/**
	 * Configuration instance.
	 *
	 * @since 6.1.3
	 *
	 * @var Configuration
	 */
	protected Configuration $config;

	/**
	 * View constructor.
	 *
	 * @since 4.9.11
	 *
	 * @param Messages|null $messages An instance of the messages collection.
	 */
	public function __construct( Messages $messages = null ) {
		$this->messages = $messages ?: new Messages();
		$this->rewrite  = TEC_Rewrite::instance();
		$this->slug     = static::$view_slug; // @todo Kept for back-compat, remove when we finally remove the non-static prop.
		$this->config   = tribe( Configuration::class );

		// For plain permalinks, the pagination variable is "page".
		if ( $this->rewrite->is_plain_permalink() ) {
			$this->page_key = 'page';
		}
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
		$params = $request->get_params();
		if ( isset( $params['url'] ) ) {
			$params['url'] = untrailingslashit( $params['url'] );
		}

		if ( isset( $params['prev_url'] ) ) {
			$params['prev_url'] = untrailingslashit( $params['prev_url'] );
		}

		$slug = Arr::get( $params, 'view', false );

		// Convert the URL to lowercase to make sure the rewrite rules, all lowercase, will match it.
		$url        = Arr::get( $params, 'url' );
		$url_object = Url::from_url_and_params( $url, $params );

		$url           = $url_object->__toString();
		$params['url'] = $url;
		if ( isset( $params['view_data'] ) ) {
			$params['view_data']['url'] = $url;
		}

		$params = array_merge( $params, $url_object->get_query_args() );

		/**
		 * Run an action before we start making a new View instance for rest requests.
		 *
		 * @since  5.5.0
		 *
		 * @param string           $slug    The current view Slug.
		 * @param array            $params  Params so far that will be used to build this view.
		 * @param \WP_REST_Request $request The rest request that generated this call.
		 */
		do_action( 'tribe_events_views_v2_before_make_view_for_rest', $slug, $params, $request );

		// Let View data override any other data.
		if ( isset( $params['view_data'] ) && is_array( $params['view_data'] ) ) {
			$params = array_merge( $params, $params['view_data'] );
		}

		// Ensure plain permalink is covered.
		if (
			TEC_Rewrite::instance()->is_plain_permalink()
			&& ! empty( $params['eventDate'] )
			&& (
				! empty( $params['tribe-event-date'] )
				|| ! empty( $params['tribe-bar-date'] )
			)
		) {
			unset( $params['eventDate'] );
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
		 * @param array            $params  An associative array of parameters from the REST request.
		 * @param \WP_REST_Request $request The current REST request.
		 */
		$params = apply_filters( 'tribe_events_views_v2_rest_params', $params, $request );

		if ( false === $slug ) {
			/*
			 * If we cannot get the view slug from the request parameters let's try to get it from the URL.
			 */
			$slug = Arr::get( $params, 'eventDisplay', tribe_context()->get( 'view', 'default' ) );
		}

		$params['event_display_mode'] = Arr::get( $query_args, Utils\View::get_past_event_display_key(), 'default' );

		if ( ! empty( $slug ) ) {
			/**
			 * Filters the parameters that will be used to build a specific View class for a REST request.
			 *
			 * @since 4.9.3
			 *
			 * @param array            $params  An associative array of parameters from the REST request.
			 * @param \WP_REST_Request $request The current REST request.
			 */
			$params = apply_filters( "tribe_events_views_v2_{$slug}_rest_params", $params, $request );
		}

		// Determine context based on the request parameters.
		$do_not_override        = [ 'event_display_mode' ];
		$not_overridable_params = array_intersect_key( $params, array_combine( $do_not_override, $do_not_override ) );
		$context                = tribe_context()
			->alter(
				array_merge(
					$params,
					tribe_context()->translate_sub_locations( $params, Context::REQUEST_VAR ),
					$not_overridable_params
				)
			);

		/** @var View $view */
		$view = static::make( $slug, $context );

		$view->url = $url_object;

		// Setup whether this view should manage URL or not, based on the Rest Request Sent.
		$view->should_manage_url = tribe_is_truthy( Arr::get( $params, 'should_manage_url', true ) );

		/**
		 * Run an action after we finish making a new View instance for rest requests.
		 *
		 * @since  5.5.0
		 *
		 * @param View             $view    The current view Slug.
		 * @param \WP_REST_Request $request Request that generated this view.
		 */
		do_action( 'tribe_events_views_v2_after_make_view_for_rest', $view, $request );

		return $view;
	}

	/**
	 * Builds and returns an instance of a View by slug or class.
	 *
	 * @since  4.9.2
	 *
	 * @param string       $view       The view slug, as registered in the `tribe_events_views` filter, or class.
	 * @param Context|null $context    The context this view should render from; if not set then the global
	 *                                 one will be used.
	 *
	 * @return View_Interface An instance of the built view.
	 */
	public static function make( $view = null, Context $context = null ) {
		$manager = tribe( Manager::class );

		$default_view = $manager->get_default_view();
		if (
			null === $view
			|| 'default' === $view
		) {
			$view = $default_view;
		}

		[ $view_slug, $view_class ] = $manager->get_view( $view );

		// When not found use the default view.
		if ( ! $view_class ) {
			[ $view_slug, $view_class ] = $manager->get_view( $default_view );
		}

		// Make sure we are using Reflector when it fails
		if ( ! class_exists( $view_class ) ) {
			[ $view_slug, $view_class ] = $manager->get_view( 'reflector' );
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
		 * @param string $view_class The current view class.
		 * @param string $view_slug  The current view slug.
		 */
		do_action( 'tribe_events_views_v2_before_make_view', $view_class, $view_slug );

		/** @var View_Interface $instance */
		$instance = self::$container->make( $view_class );

		$template = new Template( $instance );

		/**
		 * Filters the Template object for a View.
		 *
		 * @since  4.9.3
		 *
		 * @param Template $template  The template object for the View.
		 * @param string   $view_slug The current view slug.
		 * @param View     $instance  The current View object.
		 */
		$template = apply_filters( 'tribe_events_views_v2_view_template', $template, $view_slug, $instance );

		/**
		 * Filters the Template object for a specific View.
		 *
		 * @since  4.9.3
		 *
		 * @param Template $template The template object for the View.
		 * @param View     $instance The current View object.
		 */
		$template = apply_filters( "tribe_events_views_v2_{$view_slug}_view_template", $template, $instance );

		$instance->set_template( $template );
		$instance->set_template_slug( $view_slug );

		// Let's set the View context from either the global context or the provided one.
		$view_context = null === $context ? tribe_context() : $context;

		/**
		 * Filters the Context object for a View.
		 *
		 * @since 4.9.3
		 *
		 * @param Context $view_context The context abstraction object that will be passed to the view.
		 * @param string  $view         The current view slug.
		 * @param View    $instance     The current View object.
		 */
		$view_context = apply_filters( 'tribe_events_views_v2_view_context', $view_context, $view_slug, $instance );

		/**
		 * Filters the Context object for a specific View.
		 *
		 * @since 4.9.3
		 *
		 * @param Context $view_context The context abstraction object that will be passed to the view.
		 * @param View    $instance     The current View object.
		 */
		$view_context = apply_filters( "tribe_events_views_v2_{$view_slug}_view_context", $view_context, $instance );

		$instance->set_context( $view_context );

		// This code is coupled with the idea of viewing events: that's fine as Events are the default view content.
		$view_repository = tribe_events();
		// Sort events  by start date first and by duration second, this is equivalent to sorting them by end date.
		$view_repository->order_by(
			[
				'event_date'     => 'ASC',
				'event_duration' => 'ASC',
			]
		);

		/**
		 * Filters the Repository object for a View.
		 *
		 * @since 4.9.11
		 *
		 * @param \Tribe__Repository__Interface $view_repository The repository instance the View will use.
		 * @param string                        $view_slug       The current view slug.
		 * @param View                          $instance        The current View object.
		 */
		$view_repository = apply_filters( 'tribe_events_views_v2_view_repository', $view_repository, $view_slug, $instance );

		/**
		 * Filters the Repository object for a specific View.
		 *
		 * @since 4.9.11
		 *
		 * @param \Tribe__Repository__Interface $view_repository The repository instance the View will use.
		 * @param View                          $instance        The current View object.
		 */
		$view_repository = apply_filters( "tribe_events_views_v2_{$view_slug}_view_repository", $view_repository, $instance );

		$instance->set_repository( $view_repository );

		/**
		 * Filters the query arguments array for a View URL.
		 *
		 * @since 4.9.11
		 *
		 * @param array  $query_args Arguments used to build the URL.
		 * @param string $view_slug  The current view slug.
		 * @param View   $instance   The current View object.
		 */
		$view_url_query_args = apply_filters( 'tribe_events_views_v2_view_url_query_args', [], $view_slug, $instance );

		/**
		 * Filters the query arguments array for a specific View URL.
		 *
		 * @since 4.9.11
		 *
		 * @param array $query_args Arguments used to build the URL.
		 * @param View  $instance   The current View object.
		 */
		$view_url_query_args = apply_filters( "tribe_events_views_v2_{$view_slug}_view_url_query_args", $view_url_query_args, $instance );

		$instance->set_url( $view_url_query_args, true );

		/**
		 * Run an action after we are done making a new View instance.
		 *
		 * @since  4.9.11
		 *
		 * @param View $instance The current View object.
		 */
		do_action( 'tribe_events_views_v2_after_make_view', $instance );

		return $instance;
	}

	/**
	 * Gets an inheritance list for the current class, will only include valid Views, so the abstract and interface are
	 * not included.
	 *
	 * @since 6.2.0
	 *
	 * @param bool $with_current     Should include the current class in the inheritance list or not.
	 * @param bool $ignore_reflector Ignore reflector class in the inheritance list.
	 *
	 * @return array
	 */
	public function get_inheritance( bool $with_current = true, bool $ignore_reflector = true ): array {
		$parent_class_names = [];
		$parent_class_name  = get_class( $this );
		if ( $with_current ) {
			$parent_class_names[] = $parent_class_name;
		}

		while ( $parent_class_name = get_parent_class( $parent_class_name ) ) {
			// We only care up until the "abstract" of Views.
			if ( View::class === $parent_class_name ) {
				break;
			}

			$parent_class_names[] = $parent_class_name;
		}

		// Filter the Reflector_View class if needed.
		if ( $ignore_reflector ) {
			$parent_class_names = array_filter( $parent_class_names, static function ( $class_name ) {
				return Reflector_View::class !== $class_name;
			} );
		}

		return $parent_class_names;
	}

	/**
	 * Sets the DI container the class should use to build views.
	 *
	 * @since 4.9.2
	 *
	 * @param Container $container The DI container instance to use.
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
		add_filter( 'tec_events_get_current_view', [ $this, 'filter_set_current_view' ] );

		if ( self::class === static::class ) {
			$html = $this->template->render();
			remove_filter( 'tec_events_get_current_view', [ $this, 'filter_set_current_view' ] );

			return $html;
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

		// Need our nonces for AJAX requests.
		$nonce_html = Rest_Endpoint::get_rest_nonce_html( Rest_Endpoint::get_rest_nonces() );

		/*
		 * Some Views might need to access this out of this method, let's make the filtered repository arguments
		 * available.
		 */
		$this->repository_args = $repository_args;

		// If HTML_Cache is a class trait and we have content to display, display it.
		if (
			method_exists( $this, 'maybe_get_cached_html' )
			&& $cached_html = $this->maybe_get_cached_html()
		) {
			remove_filter( 'tec_events_get_current_view', [ $this, 'filter_set_current_view' ] );

			return $cached_html . $nonce_html;
		}

		if ( ! tribe_events_view_v2_use_period_repository() ) {
			$this->setup_the_loop( $repository_args );
		}


		/**
		 * Fire new action on the views.
		 *
		 * @since 5.7.0
		 *
		 * @param View $this A reference to the View instance that is currently setting up the loop.
		 */
		do_action( 'tribe_views_v2_after_setup_loop', $this );
		$template_vars = $this->filter_template_vars( $this->setup_template_vars() );
		$this->template->set_values( $template_vars, false );

		$html = $this->template->render();
		$this->restore_the_loop();

		// If HTML_Cache is a class trait, perhaps the markup should be cached.
		if ( method_exists( $this, 'maybe_cache_html' ) ) {
			$this->maybe_cache_html( $html );
		}

		remove_filter( 'tribe_repository_query_arg_offset_override', [ $this, 'filter_repository_query_arg_offset_override' ], 10, 2 );
		remove_filter( 'tec_events_get_current_view', [ $this, 'filter_set_current_view' ] );

		return $html . $nonce_html;
	}

	/**
	 * Returns the view label value after filtering.
	 *
	 * This is the method you want to overwrite to replace the label for a view with translations.
	 *
	 * @since 6.0.4
	 *
	 * @return string
	 */
	public static function get_view_label(): string {
		return static::filter_view_label( static::$label );
	}

	/**
	 * Filters the view label value allowing changes to be made.
	 *
	 * @since 6.0.4
	 *
	 * @param string $label Which label we are filtering for.
	 *
	 * @return string
	 */
	protected static function filter_view_label( string $label ): string {
		/**
		 * On the next feature version we need to remove.
		 */
		$slug = tribe( Manager::class )->get_view_slug_by_class( self::class );

		/**
		 * Filters the label that will be used on the UI for views listing.
		 *
		 * @since 6.0.4
		 *
		 * @param string $label Label of the Current view.
		 * @param string $slug  Slug of the view we are getting the label for.
		 */
		$label = apply_filters( 'tec_events_views_v2_view_label', $label, $slug );

		/**
		 * Filters the label that will be used on the UI for views listing.
		 *
		 * @since 6.0.4
		 *
		 * @param string $label Label of the Current view.
		 */
		return (string) apply_filters( "tec_events_views_v2_{$slug}_view_label", $label );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return static::get_view_label();
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
		_deprecated_function( __METHOD__, '6.0.7', 'Use static get_view_slug()' );

		return static::get_view_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_view_slug(): string {
		return static::$view_slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template_path() {
		return $this->template_path;
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
			'tribe-events-view--' . static::$view_slug,
		];

		$parents = array_map( static function ( $view_slug ) {
			return 'tribe-events-view--' . $view_slug;
		}, $this->get_parents_slug() );

		$html_classes = array_merge( $base_classes, $parents, $classes );
		$view_slug    = static::$view_slug;

		/**
		 * Filters the HTML classes applied to a View top-level container.
		 *
		 * @since 4.9.13
		 *
		 * @param array  $html_classes Array of classes used for this view.
		 * @param string $view_slug    The current view slug.
		 * @param View   $instance     The current View object.
		 */
		$html_classes = apply_filters( 'tribe_events_views_v2_view_html_classes', $html_classes, $view_slug, $this );

		/**
		 * Filters the HTML classes applied to a specific View top-level container.
		 *
		 * @since 4.9.13
		 *
		 * @param array $html_classes Array of classes used for this view.
		 * @param View  $instance     The current View object.
		 */
		$html_classes = apply_filters( "tribe_events_views_v2_{$view_slug}_view_html_classes", $html_classes, $this );

		return $html_classes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_slug( $slug ) {
		_deprecated_function( __METHOD__, '6.0.7' );
		static::$view_slug = $slug;
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
		$category = $this->context->get( 'event_category', false );

		if ( is_array( $category ) ) {
			$category = Arr::to_list( reset( $category ) );
		}

		$tag = $this->context->get( 'post_tag', false );

		if ( is_array( $tag ) ) {
			$tag = Arr::to_list( reset( $tag ) );
		}

		$query_args = [
			'post_type'        => TEC::POSTTYPE,
			'eventDisplay'     => static::$view_slug,
			'tribe-bar-date'   => $this->context->get( 'event_date', '' ),
			'tribe-bar-search' => $this->context->get( 'keyword', '' ),
			TEC::TAXONOMY      => $category,
			'tag'              => $tag,
		];

		if ( $is_featured = tribe_is_truthy( $this->context->get( 'featured', false ) ) ) {
			$query_args['featured'] = $is_featured;
		} else {
			unset( $query_args['featured'] );
		}

		$query_args = $this->filter_query_args( $query_args, $canonical );

		if ( ! empty( $query_args['tribe-bar-date'] ) ) {
			// If the Events Bar date is the same as today's date, then drop it.
			$today           = $this->context->get( 'today', 'today' );
			$url_date_format = $this->get_url_date_format();
			$today_date      = Dates::build_date_object( $today )->format( $url_date_format );
			$tribe_bar_date  = Dates::build_date_object( $query_args['tribe-bar-date'] )->format( $url_date_format );

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
		$page = $this->url->get_current_page();
		if ( ! $page ) {
			$page = 1;
		}

		if ( $page > 1 ) {
			$query_args[ $this->page_key ] = $page;
		}

		$url = add_query_arg( array_filter( $query_args ), home_url() );

		if ( $canonical ) {
			$url = $this->rewrite->get_clean_url( $url, $force );
		}

		$event_display_mode = $this->context->get( 'event_display_mode', false );
		if (
			'past' === $event_display_mode
			&& $event_display_mode !== $this->context->get( 'eventDisplay' )
		) {
			$url = add_query_arg( [ Utils\View::get_past_event_display_key() => $event_display_mode ], $url );
		}

		$url = $this->filter_view_url( $canonical, $url );

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function next_url( $canonical = false, array $passthru_vars = [] ) {
		$cache_key = __METHOD__ . '_' . md5( wp_json_encode( func_get_args() ) );

		if ( isset( $this->cached_urls[ $cache_key ] ) ) {
			return $this->cached_urls[ $cache_key ];
		}

		$url = $this->get_url();

		$query_args = [];

		if ( ! empty( $passthru_vars ) ) {
			// Remove the pass-thru vars, we'll re-apply them to the URL later.
			$url = remove_query_arg( array_keys( $passthru_vars ), $url );
		}

		// Make sure the view slug is always set to correctly match rewrites.
		$query_args['eventDisplay'] = static::$view_slug;

		if ( $this->has_next_event ) {
			$query_args[ $this->page_key ] = $this->url->get_current_page() + 1;

			// Default to the current URL.
			$url = $url ?: home_url( add_query_arg( [] ) );

			$query_args = $this->filter_query_args( $query_args, $url );
			$query_args = array_filter( $query_args );

			if ( ! empty( $query_args ) ) {
				$url = add_query_arg( $query_args, $url );
			}

			// Remove the inverse of the page key we are using.
			$url = remove_query_arg( 'page' === $this->page_key ? 'paged' : 'page', $url );

			if ( $canonical ) {
				$url = tribe( 'events.rewrite' )->get_clean_url( $url );
			}

			if ( ! empty( $passthru_vars ) && ! empty( $url ) ) {
				// Re-apply the pass-thru query arguments.
				$url = add_query_arg( $passthru_vars, $url );
			}
		} else {
			$url = '';
		}

		$url = $this->filter_next_url( $canonical, $url );

		$this->cached_urls['next_url'] = $url;

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prev_url( $canonical = false, array $passthru_vars = [] ) {
		$cache_key = __METHOD__ . '_' . md5( wp_json_encode( func_get_args() ) );

		if ( isset( $this->cached_urls[ $cache_key ] ) ) {
			return $this->cached_urls[ $cache_key ];
		}

		$prev_page = $this->repository->prev()->order_by( '__none' );

		$paged           = $this->url->get_current_page() - 1;
		$query_args      = [];
		$page_query_args = $paged > 1
			? [ $this->page_key => $paged ]
			: [];

		$url = $this->get_url();

		if ( ! empty( $passthru_vars ) ) {
			// Remove the pass-thru vars, we'll re-apply them to the URL later.
			$url = remove_query_arg( array_keys( $passthru_vars ), $url );
		}

		// Make sure the view slug is always set to correctly match rewrites.
		$query_args['eventDisplay'] = static::$view_slug;

		if ( $prev_page->count() > 0 ) {
			$query_args = array_merge( $query_args, $page_query_args );

			// Default to the current URL.
			$url = $url ?: home_url( add_query_arg( [] ) );

			if ( $paged === 1 ) {
				$url = remove_query_arg( $this->page_key, $url );
				unset( $query_args[ $this->page_key ] );
			}

			$query_args = $this->filter_query_args( $query_args, $url );
			$query_args = array_filter( $query_args );

			if ( ! empty( $query_args ) ) {
				$url = add_query_arg( $query_args, $url );
			}

			// Remove the inverse of the page key we are using.
			$url = remove_query_arg( 'page' === $this->page_key ? 'paged' : 'page', $url );

			if ( $canonical ) {
				$url = tribe( 'events.rewrite' )->get_clean_url( $url );
			}

			if ( ! empty( $passthru_vars ) ) {
				// Re-apply the pass-thru query arguments.
				$url = add_query_arg( $passthru_vars, $url );
			}
		} else {
			$url = '';
		}

		$url = $this->filter_prev_url( $canonical, $url );

		$this->cached_urls['prev_url'] = $url;

		return $url;
	}

	/**
	 * Filters URL query args with a predictable filter
	 *
	 * @since 5.0.0
	 *
	 * @param array $query_args An array of query args that will be used to build the URL for the View.
	 * @param bool  $canonical  Whether the URL should be the canonical one or not.
	 *
	 * @return array            Filtered array of query arguments.
	 */
	public function filter_query_args( $query_args, $canonical ) {
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

		return $query_args;
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
			'globals::wp_query'   => $wp_query,
			'server::request_uri' => isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '',
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

		if ( isset( $this->global_backup['globals::wp_query'] ) ) {
			$GLOBALS['wp_query'] = $this->global_backup['globals::wp_query'];
		}
		if ( isset( $this->global_backup['server::request_uri'] ) ) {
			$_SERVER['REQUEST_URI'] = $this->global_backup['server::request_uri'];
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
		}
	}

	/**
	 * Maps a set of arguments to query arguments, ready to be appended to a URL.
	 *
	 * @since 4.9.3
	 *
	 * @param array $args An associative array of arguments to map (translate) to query arguments.
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
		$events = $template_vars['events'] ?: [];

		/*
		 * Add the JSON-LD data here as all Views will pass from this code, but not all Views will call the
		 * `View::setup_template_vars` method.
		 *
		 * Filters to control the data are available in the `Tribe__JSON_LD__Abstract` object and its extending classes.
		 */
		$template_vars['json_ld_data'] = $this->build_json_ld_data( $events );
		$this->setup_additional_views( (array) $events, $template_vars );

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
		$view_slug     = static::$view_slug;

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
		$template_vars = apply_filters( "tribe_events_views_v2_view_{$view_slug}_template_vars", $template_vars, $this );

		return $template_vars;
	}

	/**
	 * Sets up the View repository arguments from the View context or a provided Context object.
	 *
	 * @since 4.9.3
	 * @since 6.0.5 Now will merge a "global" repository arg filter, which will be applied elsewhere as well as this
	 *                primary repository query.
	 *
	 * @param Context|null $context A context to use to setup the args, or `null` to use the View Context.
	 *
	 * @return array The arguments, ready to be set on the View repository instance.
	 */
	protected function setup_repository_args( Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		$context_arr = $context->to_array();

		/*
		 * Note: we are setting events_per_page to +1 so we don't need to query twice to
		 * determine if there are subsequent pages. When running setup_template_vars, we pop
		 * the last item off the array if the returned posts are > events_per_page.
		 *
		 * @since 5.0.0
		*/
		$args = [
			'posts_per_page'       => $context_arr['events_per_page'] + 1,
			'paged'                => max( Arr::get_first_set( array_filter( $context_arr ), [
				'paged',
				'page',
			], 1 ), 1 ),
			'search'               => $context->get( 'keyword', '' ),
			'hidden_from_upcoming' => false,
			/*
			 * Passing this parameter that is only used in this object to control whether or not the
			 * offset value should be overridden with the `tribe_repository_query_arg_offset_override` filter.
			 */
			'view_override_offset' => true,
		];

		// Merge our global repository args with the primary event args.
		$args = array_merge( $args, $this->get_global_repository_args() );

		add_filter( 'tribe_repository_query_arg_offset_override', [ $this, 'filter_repository_query_arg_offset_override' ], 10, 2 );

		// Sets up category URL for all views.
		if ( ! empty( $context_arr[ TEC::TAXONOMY ] ) ) {
			$args[ TEC::TAXONOMY ] = $context_arr[ TEC::TAXONOMY ];
		}

		if ( ! empty( $context_arr['event_category'] ) ) {
			$args['event_category'] = $context_arr['event_category'];
		}

		// Sets up post tag URL for all views.
		if ( ! empty( $context_arr['post_tag'] ) ) {
			$args['tag'] = $context_arr['post_tag'];
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
	 * Filters the offset value separate from the posts_per_page/paged calculation.
	 *
	 * This allows us to save a query when determining pagination for list-like views.
	 *
	 * @since 5.0.0
	 * @since 5.15.2 Ensure our max() gets all ints, for math reasons.
	 *
	 * @param null|int  $offset_override Offset override value.
	 * @param \WP_Query $query           WP Query object.
	 *
	 * @return null|int
	 */
	public function filter_repository_query_arg_offset_override( $offset_override, $query ) {
		if ( ! isset( $query['view_override_offset'] ) ) {
			return $offset_override;
		}

		$context = $this->get_context();

		$current_page = max(
			(int) $context->get( 'page' ),
			(int) $context->get( 'paged' ),
			1
		);

		return ( $current_page - 1 ) * $context->get( 'events_per_page' );
	}

	/**
	 * Filters the current URL returned for a specific View.
	 *
	 * @since 4.9.3
	 *
	 * @param bool   $canonical Whether the normal or canonical version of the next URL is being requested.
	 * @param string $url       The previous URL, this could be an empty string if the View does not have a next.
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

		$view_slug = static::$view_slug;

		/**
		 * Filters the URL returned for a specific View.
		 *
		 * @since 4.9.11
		 *
		 * @param string         $url       The View current URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( "tribe_events_views_v2_view_{$view_slug}_url", $url, $canonical, $this );

		return $url;
	}

	/**
	 * Filters the previous (page, event, etc.) URL returned for a specific View.
	 *
	 * @since 4.9.3
	 *
	 * @param bool   $canonical Whether the normal or canonical version of the next URL is being requested.
	 * @param string $url       The previous URL, this could be an empty string if the View does not have a next.
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

		$view_slug = static::$view_slug;

		/**
		 * Filters the previous (page, event, etc.) URL returned for a specific View.
		 *
		 * @since 4.9.11
		 *
		 * @param string         $url       The View previous (page, event, etc.) URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( "tribe_events_views_v2_view_{$view_slug}_prev_url", $url, $canonical, $this );

		return $url;
	}

	/**
	 * Filters the next (page, event, etc.) URL returned for a specific View.
	 *
	 * @since 4.9.3
	 *
	 * @param bool   $canonical Whether the normal or canonical version of the next URL is being requested.
	 * @param string $url       The next URL, this could be an empty string if the View does not have a next.
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

		$view_slug = static::$view_slug;

		/**
		 * Filters the next (page, event, etc.) URL returned for a specific View.
		 *
		 * @since 4.9.11
		 *
		 * @param string         $url       The View next (page, event, etc.) URL.
		 * @param bool           $canonical Whether the URL is a canonical one or not.
		 * @param View_Interface $this      This view instance.
		 */
		$url = apply_filters( "tribe_events_views_v2_view_{$view_slug}_next_url", $url, $canonical, $this );

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function found_post_ids() {
		$events = $this->repository->get_ids();
		if ( $this->has_next_event( $events ) ) {
			array_pop( $events );
		}

		return $events;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function is_publicly_visible() {
		return static::$publicly_visible;
	}

	/**
	 * Sets the has_next_event boolean flag, which determines if we have events in the next page.
	 *
	 * This flag is required due to being required to optimize the determination of whether
	 * there are future events, we increased events_per_page by +1 during setup_repository_args. Because of that
	 * if the number of events returned are greater than events_per_page, we need to
	 * pop an element off the end and set a boolean.
	 *
	 * @since 5.0.0
	 *
	 * @param boolean $value Which value will be set to has_next_event, will be casted as boolean.
	 *
	 * @return mixed         Value passed after being saved and casted as boolean.
	 */
	public function set_has_next_event( $value ) {
		return $this->has_next_event = (bool) $value;
	}

	/**
	 * Determines from a given array of events if we have next events or not.
	 *
	 * @since 5.0.0
	 *
	 * @param array   $events         Array that will be counted to verify if we have events.
	 * @param boolean $overwrite_flag If we should overwrite the flag when we discover the result.
	 *
	 * @return mixed                   Weather the array of events has a next page.
	 */
	public function has_next_event( array $events, $overwrite_flag = true ) {
		$has_next_events = count( $events ) > $this->get_context()->get( 'events_per_page', 12 );
		if ( (bool) $overwrite_flag ) {
			$this->set_has_next_event( $has_next_events );
		}

		return $has_next_events;
	}

	/**
	 * Get if we have events in the next page.
	 *
	 * @since 5.16.0
	 *
	 * @return boolean Weather the View has events in the next page.
	 */
	public function get_has_next_event() {
		return (boolean) $this->has_next_event;
	}

	/**
	 * Sets up the View template variables.
	 *
	 * @since 4.9.4
	 * @since 5.2.1 Add the `rest_method` to the template variables.
	 *
	 * @return array An array of Template variables for the View Template.
	 */
	protected function setup_template_vars() {
		if ( empty( $this->repository_args ) ) {
			$this->repository_args = $this->get_repository_args();
			$this->repository->by_args( $this->repository_args );
		}

		// Pre build query, so we can determine the unique hash for this event query.
		$this->repository->build_query();
		$repository_query_hash = $this->repository->hash();

		// Check if we memoized this query and if memoize was enabled.
		$memoize_key = tribe_cache()->make_key( $this->context->to_array(), __METHOD__ ) . $repository_query_hash;
		$vars        = tribe_cache()->get( $memoize_key, Tribe__Cache_Listener::TRIGGER_SAVE_POST, null, Tribe__Cache::NON_PERSISTENT );
		if ( ! empty( $vars ) ) {
			return $vars;
		}

		$events = (array) $this->repository->all();
		$events = array_filter( $events, static function ( $event ) {
			return $event instanceof \WP_Post;
		} );

		Taxonomy::prime_term_cache( $events );
		Event::prime_cache( $events );

		/**
		 * Action triggered right after pulling all the Events from the DB, allowing cache to be primed correctly.
		 *
		 * @since 6.0.0
		 *
		 * @param array<WP_Post> $events Which events were just selected.
		 * @param self           $view   Which view we are dealing with.
		 */
		do_action( 'tec_events_views_v2_after_get_events', $events, $this );

		$is_paginated = isset( $this->repository_args['posts_per_page'] ) && - 1 !== $this->repository_args['posts_per_page'];

		/*
		 * To optimize the determination of whether there are future events, we
		 * increased events_per_page by +1 during setup_repository_args. Because of that
		 * if the number of events returned is greater than events_per_page, we need to
		 * pop an element off the end and set a boolean.
		 *
		 * @since 5.0.0
		 */
		if ( $is_paginated && $this->has_next_event( $events ) ) {
			array_pop( $events );
		}

		$this->setup_messages( $events );

		$today_url = $this->get_today_url( true );
		$today     = $this->context->get( 'today', 'today' );
		// The "Today" button title and aria-label text.
		$today_title = _x(
			'Click to select today\'s date',
			'The default title text for the today button.',
			'the-events-calendar'
		);

		/**
		 * Allows filtering of the "Today" button title and aria-label.
		 *
		 * @since 6.0.2
		 *
		 * @param string                                $today_title The title string.
		 * @param \Tribe\Events\Views\V2\View_Interface $view        The View currently rendering.
		 */
		$today_title = apply_filters(
			'tec_events_today_button_title',
			$today_title,
			$this
		);

		$view_slug = static::$view_slug;

		/**
		 * Allows filtering of the "Today" button title and aria-label.
		 *
		 * @since 6.0.2
		 *
		 * @param string                                $today_title The title string.
		 * @param \Tribe\Events\Views\V2\View_Interface $view        The View currently rendering.
		 */
		$today_title = apply_filters(
			"tec_events_view_{$view_slug}_today_button_title",
			$today_title,
			$this
		);

		$today_label = tec_events_get_today_button_label( $this );
		$event_date  = $this->context->get( 'event_date', false );

		// Set the URL event date only if it's not empty or "now": both are implicit, default, date selections.
		$url_event_date = ( ! empty( $event_date ) && 'now' !== $event_date )
			? Dates::build_date_object( $event_date )->format( Dates::DBDATEFORMAT )
			: false;

		/** @var Rest_Endpoint $endpoint */
		$endpoint = tribe( Rest_Endpoint::class );

		$template_vars = [
			'title'                => $this->get_title( $events ),
			'events'               => $events,
			'url'                  => $this->get_url( true ),
			'prev_url'             => $this->prev_url( true ),
			'next_url'             => $this->next_url( true ),
			'url_event_date'       => $url_event_date,
			'bar'                  => [
				'keyword' => $this->context->get( 'keyword', '' ),
				'date'    => $this->context->get( 'event_date', '' ),
			],
			'today'                => $today,
			'now'                  => $this->context->get( 'now', 'now' ),
			'request_date'         => Dates::build_date_object( $this->context->get( 'event_date', $today ) ),
			'rest_url'             => $endpoint->get_url(),
			'rest_method'          => $endpoint->get_method(),
			'rest_nonce'           => '', // For backwards compatibility in views. No longer used.
			'should_manage_url'    => $this->should_manage_url,
			'today_url'            => $today_url,
			'today_title'          => $today_title,
			'today_label'          => $today_label,
			'prev_label'           => $this->get_link_label( $this->prev_url( false ) ),
			'next_label'           => $this->get_link_label( $this->next_url( false ) ),
			'date_formats'         => (object) [
				'compact'                => Dates::datepicker_formats( tribe_get_option( 'datepickerFormat' ) ),
				'month_and_year_compact' => Dates::datepicker_formats( 'm' . tribe_get_option( 'datepickerFormat' ) ),
				'month_and_year'         => tribe_get_date_option( 'monthAndYearFormat', 'F Y' ),
				'time_range_separator'   => tribe_get_date_option( 'timeRangeSeparator', ' - ' ),
				'date_time_separator'    => tribe_get_date_option( 'dateTimeSeparator', ' @ ' ),
			],
			'messages'             => $this->get_messages( $events ),
			'start_of_week'        => get_option( 'start_of_week', 0 ),
			'header_title'         => $this->get_header_title(),
			'header_title_element' => $this->get_header_title_element(),
			'content_title'        => $this->get_content_title(),
			'breadcrumbs'          => $this->get_breadcrumbs(),
			'before_events'        => tribe( Advanced_Display::class )->get_before_events_html( $this ),
			'after_events'         => tribe( Advanced_Display::class )->get_after_events_html( $this ),
			'display_events_bar'   => $this->filter_display_events_bar( $this->display_events_bar ),
			/**
			 * Allow filtering to determine whether or not to apply the `tribeDisableTribeBar` setting on the Events Manager page.
			 *
			 * @since 5.12.1
			 */
			'disable_event_search' => apply_filters( 'tec_events_views_v2_disable_tribe_bar', tribe_get_option( 'tribeDisableTribeBar', false ) ),
			'live_refresh'         => tribe_is_truthy( 'automatic' === tribe_get_option( 'liveFiltersUpdate', 'automatic' ) ),
			'ical'                 => $this->get_ical_data(),
			'container_classes'    => $this->get_html_classes(),
			'container_data'       => $this->get_container_data(),
			'is_past'              => 'past' === $this->context->get( 'event_display_mode', false ),
			'breakpoints'          => $this->get_breakpoints(),
			'breakpoint_pointer'   => $this->get_breakpoint_pointer(),
			'is_initial_load'      => $this->context->doing_php_initial_state(),
			'public_views'         => $this->get_public_views( $url_event_date ),
			'show_latest_past'     => $this->should_show_latest_past_events_view(),
		];

		if ( ! $this->config->get( 'TEC_NO_MEMOIZE_VIEW_VARS' ) ) {
			tribe_cache()->set( $memoize_key, $template_vars, Tribe__Cache::NON_PERSISTENT, Tribe__Cache_Listener::TRIGGER_SAVE_POST );
		}

		return $template_vars;
	}

	/**
	 * Filters the repository arguments that will be used to set up the View repository instance.
	 *
	 * @since 4.9.5
	 *
	 * @param array        $repository_args The repository arguments that will be used to set up the View repository instance.
	 * @param Context|null $context         Either a specific Context or `null` to use the View current Context.
	 *
	 * @return array The filtered repository arguments.
	 */
	protected function filter_repository_args( array $repository_args, Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		/**
		 * Filters the repository args for a View.
		 *
		 * @since 4.9.5
		 *
		 * @param array          $repository_args An array of repository arguments that will be set for all Views.
		 * @param Context        $context         The current render context object.
		 * @param View_Interface $this            The View that will use the repository arguments.
		 */
		$repository_args = apply_filters( 'tribe_events_views_v2_view_repository_args', $repository_args, $context, $this );

		$view_slug = static::$view_slug;

		/**
		 * Filters the repository args for a specific View.
		 *
		 * @since 4.9.5
		 *
		 * @param array          $repository_args An array of repository arguments that will be set for a specific View.
		 * @param Context        $context         The current render context object.
		 * @param View_Interface $this            The View that will use the repository arguments.
		 */
		$repository_args = apply_filters(
			"tribe_events_views_v2_view_{$view_slug}_repository_args",
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
		$plain_url   = (string) $this->get_url();
		$clean_url   = $this->rewrite->get_clean_url( $plain_url );
		$url_frags   = wp_parse_url( $clean_url );
		$path        = isset( $url_frags['path'] ) ? trim( $url_frags['path'], '/' ) . '/' : '';
		$query       = isset( $url_frags['query'] ) ? '?' . $url_frags['query'] : '';
		$fragment    = isset( $url_frags['fragment'] ) ? '#' . $url_frags['fragment'] : '';
		$request_uri = '/' . $path . $query . $fragment;

		/**
		 * Allows filtering the Views request URI that will be used to set up the loop.
		 *
		 * @since 5.2.1
		 *
		 * @param string $request_uri The parsed request URI.
		 */
		$request_uri = apply_filters( 'tribe_events_views_v2_request_uri', $request_uri );

		return $request_uri;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template_slug() {
		if ( null !== $this->template_slug ) {
			return $this->template_slug;
		}

		return static::$view_slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_template_slug( $template_slug ) {
		$this->template_slug = $template_slug;
		$this->template->set( 'slug', $template_slug );
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
		$to_remove = [ 'tribe-bar-date', 'paged', 'page', 'eventDate', 'tribe_event_display' ];

		// While we want to remove the date query vars, we want to keep any other query var.
		$query_args = $this->url->get_query_args();

		// Handle the `eventDisplay` query arg due to its particular usage to indicate the mode too.
		$query_args['eventDisplay'] = static::$view_slug;

		$category = $this->context->get( 'event_category', false );

		if ( is_array( $category ) ) {
			$category                       = Arr::to_list( reset( $category ) );
			$query_args['tribe_events_cat'] = $category;
		}

		$query_args = $this->filter_query_args( $query_args, $canonical );

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
	 * @see   View::get_label_format(), the method child classes should override to customize the link label format.
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

		$view_slug = static::$view_slug;

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
		$format = apply_filters( "tribe_events_views_v2_view_{$view_slug}_link_label_format", $format, $this, $date );

		return date_i18n( $format, $date_object->getTimestamp() + $date_object->getOffset() );
	}

	/**
	 * Returns the date format, a valid PHP `date` function format, that should be used to build link labels.
	 *
	 * This format will, usually, apply to next and previous links.
	 *
	 * @todo  @bordoni move this method to a supporting class.
	 *
	 * @see   View::get_link_label(), the method using this method to build a link label.
	 * @see   date_i18n() as the formatted date will, then, be localized using this method.
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
	 * @param array $events An array of events to generate the title for.
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

		/**
		 * Filters the title for all views.
		 *
		 * @since 4.9.11
		 *
		 * @param string $title This view filtered title.
		 * @param View   $this  This view object.
		 */
		$title = apply_filters( "tribe_events_views_v2_view_title", $title, $this );

		$view_slug = static::$view_slug;

		/**
		 * Filters the title for this view.
		 *
		 * @since 4.9.11
		 *
		 * @param string $title This view filtered title.
		 * @param View   $this  This view object.
		 */
		$title = apply_filters( "tribe_events_views_v2_view_{$view_slug}_title", $title, $this );

		return html_entity_decode( $title, ENT_QUOTES );
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
		 * @param array    $messages         An array of messages in the shape `[ <message_type> => [ ...<messages> ] ]`.
		 * @param View     $this             The current View instance being rendered.
		 * @param Messages $messages_handler The messages handler object the View used to render the messages.
		 */
		$messages = apply_filters( 'tribe_events_views_v2_view_messages', $messages, $this, $this->messages );

		$view_slug = static::$view_slug;

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
		$messages = apply_filters( "tribe_events_views_v2_view_{$view_slug}_messages", $messages, $events, $this, $this->messages );

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
				$this->messages->insert( Messages::TYPE_NOTICE, Messages::for_key( 'no_results_found_w_keyword', esc_html( trim( $keyword ) ) ) );
			} else {
				$message_key = $this->upcoming_events_count() ? 'no_results_found' : 'no_upcoming_events';
				$this->messages->insert( Messages::TYPE_NOTICE, Messages::for_key( $message_key ) );
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

			/**
			 * Filters the ignored params for resetting page number the View will do when paginating via AJAX.
			 *
			 * @see   Url::is_diff()
			 *
			 * @since 5.4.0
			 *
			 * @param array $page_reset_ignored_params An array of params to be ignored.
			 * @param View  $this                      The current View instance being rendered.
			 */
			$page_reset_ignored_params = apply_filters(
				'tribe_events_views_v2_view_page_reset_ignored_params',
				[ 'page', 'paged' ],
				$this
			);

			$view_slug = static::$view_slug;

			/**
			 * Filters the ignored params for resetting page number a specific View will do when paginating via AJAX.
			 *
			 * @see   Url::is_diff()
			 *
			 * @since 5.4.0
			 *
			 * @param array $page_reset_ignored_params An array of params to be ignored.
			 * @param View  $this                      The current View instance being rendered.
			 */
			$page_reset_ignored_params = apply_filters(
				"tribe_events_views_v2_view_{$view_slug}_page_reset_ignored_params",
				$page_reset_ignored_params,
				$this
			);

			$this->should_reset_page = Url::is_diff( $prev_url, $current_url, $page_reset_ignored_params );
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
		$taxonomy    = TEC::TAXONOMY;
		$context_tax = $context->get( $taxonomy, false );
		if ( empty( $context_tax ) ) {
			$taxonomy    = 'post_tag';
			$context_tax = $context->get( $taxonomy, false );
		}

		// Get term slug if taxonomy is not empty
		if ( ! empty( $context_tax ) ) {
			// Don't pass arrays to get_term_by()!
			if ( is_array( $context_tax ) ) {
				$context_tax = array_pop( $context_tax );
			}

			$term = get_term_by( 'slug', $context_tax, $taxonomy );
			if ( ! empty( $term->name ) ) {
				$label = $term->name;

				$breadcrumbs[] = [
					'link'  => tribe_events_get_url(),
					'label' => tribe_get_event_label_plural(),
				];
				$breadcrumbs[] = [
					'link'  => '',
					'label' => $label,
				];
			}
		}

		// Setup breadcrumbs for when it's featured.
		if ( $is_featured = tribe_is_truthy( $this->context->get( 'featured', false ) ) ) {
			$non_featured_link = tribe_events_get_url( [ 'featured' => 0 ] );

			if ( empty( $context_tax ) ) {
				$breadcrumbs[] = [
					'link'  => $non_featured_link,
					'label' => tribe_get_event_label_plural(),
				];
			}

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

		$view_slug = static::get_view_slug();

		/**
		 * Filters the breadcrumbs a specific View will print on the frontend.
		 *
		 * @since 4.9.11
		 *
		 * @param array $breadcrumbs An array of breadcrumbs.
		 * @param View  $this        The current View instance being rendered.
		 */
		$breadcrumbs = apply_filters( "tribe_events_views_v2_view_{$view_slug}_breadcrumbs", $breadcrumbs, $this );

		return $breadcrumbs;
	}

	/**
	 * Header Title Element, allowing better control over the title tag.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	protected function get_header_title_element(): string {
		/**
		 * Filters the header title element the View will print on the frontend.
		 *
		 * @since 6.2.0
		 *
		 * @param string $header_title_element The header title to be displayed.
		 * @param View   $this                 The current View instance being rendered.
		 */
		$header_title_element = (string) apply_filters( 'tec_events_views_v2_view_header_title_element', 'h1', $this );

		$view_slug = static::get_view_slug();

		/**
		 * Filters the header title element a specific View will print on the frontend.
		 *
		 * @since 6.2.0
		 *
		 * @param string $header_title_element The header title element to be displayed.
		 * @param View   $this                 The current View instance being rendered.
		 */
		return (string) apply_filters( "tec_events_views_v2_view_{$view_slug}_header_title_element", $header_title_element, $this );
	}

	/**
	 * Returns the header title the View will display on the front-end, normally above the breadcrumbs.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	protected function get_header_title(): string {
		$context      = $this->get_context();
		$header_title = '';
		$taxonomy     = TEC::TAXONOMY;
		$context_tax  = $context->get( $taxonomy, false );
		if ( empty( $context_tax ) ) {
			$taxonomy    = 'post_tag';
			$context_tax = $context->get( $taxonomy, false );
		}

		// Get term slug if taxonomy is not empty
		if ( ! empty( $context_tax ) ) {
			// Don't pass arrays to get_term_by()!
			if ( is_array( $context_tax ) ) {
				$context_tax = array_pop( $context_tax );
			}

			$term = get_term_by( 'slug', $context_tax, $taxonomy );
			if ( ! empty( $term->name ) ) {
				$header_title = $term->name;
			}
		}

		// Setup breadcrumbs for when it's featured.
		if ( tribe_is_truthy( $this->context->get( 'featured', false ) ) ) {
			$header_title = esc_html__( 'Featured', 'the-events-calendar' );
		}

		/**
		 * Filters the header title the View will print on the frontend.
		 *
		 * @since 6.2.0
		 *
		 * @param string $header_title The header title to be displayed.
		 * @param View   $this         The current View instance being rendered.
		 */
		$header_title = (string) apply_filters( 'tec_events_views_v2_view_header_title', $header_title, $this );

		$view_slug = static::get_view_slug();

		/**
		 * Filters the header title a specific View will print on the frontend.
		 *
		 * @since 6.2.0
		 *
		 * @param string $header_title The header title to be displayed.
		 * @param View   $this         The current View instance being rendered.
		 */
		return (string) apply_filters( "tec_events_views_v2_view_{$view_slug}_header_title", $header_title, $this );
	}

	/**
	 * Returns the content title the View will display on the front-end, normally above the date selector.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	protected function get_content_title(): string {
		/**
		 * Filters the content title the View will print on the frontend.
		 *
		 * @since 6.2.0
		 *
		 * @param string $content_title The content title to be displayed.
		 * @param View   $this          The current View instance being rendered.
		 */
		$content_title = (string) apply_filters( 'tec_events_views_v2_view_content_title', '', $this );

		$view_slug = static::get_view_slug();

		/**
		 * Filters the content title a specific View will print on the frontend.
		 *
		 * @since 6.2.0
		 *
		 * @param string $content_title The content title to be displayed.
		 * @param View   $this          The current View instance being rendered.
		 */
		return (string) apply_filters( "tec_events_views_v2_view_{$view_slug}_content_title", $content_title, $this );
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

		$view_slug = static::$view_slug;

		/**
		 * Filters if the events bar should be displayed for the specific view.
		 *
		 * @since 4.9.11
		 *
		 * @param bool $display An bool saying if it should be displayed or not.
		 * @param View $this    The current View instance being rendered.
		 */
		$display = apply_filters( "tribe_events_views_v2_view_{$view_slug}_display_events_bar", $display, $this );

		return $display;
	}

	/**
	 * Returns a boolean on whether to show the datepicker submit button.
	 *
	 * @since 4.9.13
	 *
	 * @return bool
	 */
	protected function get_show_datepicker_submit() {
		$live_refresh       = tribe_is_truthy( 'automatic' === tribe_get_option( 'liveFiltersUpdate', 'automatic' ) );
		$disable_events_bar = tribe_is_truthy( tribe_get_option( 'tribeDisableTribeBar', false ) );

		$show_datepicker_submit = empty( $live_refresh ) && ! empty( $disable_events_bar );

		/**
		 * Filters the show datepicker submit value.
		 *
		 * @since 5.0.0
		 *
		 * @param object $show_datepicker_submit The show datepicker submit value.
		 * @param View   $this                   The current View instance being rendered.
		 */
		$show_datepicker_submit = apply_filters( "tribe_events_views_v2_view_show_datepicker_submit", $show_datepicker_submit, $this );

		$view_slug = static::$view_slug;

		/**
		 * Filters the show datepicker submit value for a specific view.
		 *
		 * @since 5.0.0
		 *
		 * @param object $show_datepicker_submit The show datepicker submit value.
		 * @param View   $this                   The current View instance being rendered.
		 */
		$show_datepicker_submit = apply_filters( "tribe_events_views_v2_view_{$view_slug}_show_datepicker_submit", $show_datepicker_submit, $this );

		return $show_datepicker_submit;
	}

	/**
	 * Manipulates public views data, if necessary, and returns result.
	 *
	 * @since 5.0.0
	 *
	 * @param string|bool $url_event_date The value, `Y-m-d` format, of the `eventDate` request variable to
	 *                                    append to the view URL, if any.
	 *
	 * @return array
	 */
	protected function get_public_views( $url_event_date ) {
		$public_views = tribe( Manager::class )->get_publicly_visible_views_data();
		$query_args   = wp_parse_url( $this->get_url(), PHP_URL_QUERY );

		if ( ! empty( $url_event_date ) || ! empty( $query_args ) ) {
			// Each View expects the event date in a specific format, here we account for it.

			array_walk(
				$public_views,
				static function ( &$view_data ) use ( $url_event_date, $query_args ) {
					$view_instance       = View::make( $view_data->view_class );
					$view_data->view_url = $view_instance->url_for_query_args( $url_event_date, $query_args );
				}
			);
		}

		/**
		 * Filters the public views.
		 *
		 * @since 5.0.0
		 *
		 * @param object $public_views The public views.
		 * @param View   $this         The current View instance being rendered.
		 */
		$public_views = apply_filters( "tribe_events_views_v2_view_public_views", $public_views, $this );

		$view_slug = static::$view_slug;

		/**
		 * Filters the public views for a specific view.
		 *
		 * @since 5.0.0
		 *
		 * @param object $public_views The public views.
		 * @param View   $this         The current View instance being rendered.
		 */
		$public_views = apply_filters( "tribe_events_views_v2_view_{$view_slug}_public_views", $public_views, $this );

		return $public_views;
	}

	/**
	 * {@inheritDoc}
	 */
	public function url_for_query_args( $date = null, $query_args = [] ) {
		if ( ! empty( $query_args ) && is_string( $query_args ) ) {
			$str_args   = $query_args;
			$query_args = [];

			wp_parse_str( $str_args, $query_args );
		}

		// For "dateless" queries (today).
		if ( empty( $date ) ) {
			$query_args = array_filter( array_merge( $query_args, [ 'eventDisplay' => static::$view_slug ] ) );

			return tribe_events_get_url( $query_args );
		}

		$event_date = Dates::build_date_object( $date )->format( $this->get_url_date_format() );

		$url_query_args = array_filter( array_merge( $query_args, [
			'eventDisplay' => static::$view_slug,
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

	/**
	 * Returns the filtered container data attributes for the View top-level container.
	 *
	 * @since 5.0.0
	 *
	 * @return array<string,string> The filtered list of data attributes for the View top-level container.
	 */
	protected function get_container_data() {
		$view_slug = static::$view_slug;

		/**
		 * Filters the data for a View top-level container.
		 *
		 * @since 5.0.0
		 *
		 * @param array<string,string> $data      Associative array of data for the View top-level container.
		 * @param string               $view_slug The current view slug.
		 * @param View                 $instance  The current View object.
		 */
		$data = apply_filters( 'tribe_events_views_v2_view_container_data', [], $view_slug, $this );

		/**
		 * Filters the data for a specific View top-level container.
		 *
		 * @since 4.9.13
		 *
		 * @param array<string,string> $data     Associative array of data for the View top-level container.
		 * @param View                 $instance The current View object.
		 */
		$data = apply_filters( "tribe_events_views_v2_{$view_slug}_view_container_data", $data, $this );

		return $data;
	}

	/**
	 * Filters Whether the Latest Past Events Should Show for a specific View.
	 *
	 * @since 5.1.0
	 *
	 * @return boolean If we should display Latest Past Events.
	 */
	protected function should_show_latest_past_events_view() {
		$show      = $this->context->get( 'show_latest_past', true );
		$view_slug = static::$view_slug;

		/**
		 * Filters Whether the Latest Past Events Should Show for all Views.
		 *
		 * @since 5.1.0
		 *
		 * @param boolean $show      If we should display Latest Past Events.
		 * @param string  $view_slug The current view slug.
		 * @param View    $instance  The current View object.
		 */
		$show = apply_filters( 'tribe_events_views_v2_show_latest_past_events_view', $show, $view_slug, $this );

		/**
		 * Filters Whether the Latest Past Events Should Show for a specific View.
		 *
		 * @since 5.1.0
		 *
		 * @param boolean $show     If we should display Latest Past Events.
		 * @param View    $instance The current View object.
		 */
		$show = apply_filters( "tribe_events_views_v2_{$view_slug}_show_latest_past_events_view", $show, $this );

		return $show;
	}

	/**
	 * Setup of Additional Views into another View.
	 *
	 * @since 5.1.0
	 *
	 * @param array $events        Array that will be counted to verify if we have events.
	 * @param array $template_vars An associative array of variables that will be set, and exported, in the template.
	 */
	protected function setup_additional_views( array $events = [], array $template_vars = [] ) {

		$manager      = tribe( Manager::class );
		$default_slug = $manager->get_default_view_option();

		// If the slug is `default`, get the slug another way.
		if ( 'default' === $default_slug ) {
			$default_class = $manager->get_default_view();
			$default_slug  = $manager->get_view_slug_by_class( $default_class );
		}

		// Show Latest Past Events only on the default view.
		if ( static::$view_slug !== $default_slug ) {
			return;
		}

		// If doing a search, do not show.
		if ( $this->context->get( 'keyword', '' ) ) {
			return;
		}

		$now    = $this->context->get( 'now', time() );
		$latest = tribe_events_latest_date();

		// If now is less then the latest event published, do not show.
		if ( $now < $latest ) {
			return;
		}

		// Checks to verify on the initial load of a view or if using today's date for the view.
		$today     = $this->context->get( 'today' );
		$view_date = $this->context->get( 'event_date', '' );

		switch ( static::$view_slug ) {
			case Month_View::get_view_slug():
				$today_formatted     = Dates::build_date_object( $today )->format( Dates::DBYEARMONTHTIMEFORMAT );
				$view_date_formatted = Dates::build_date_object( $view_date )->format( Dates::DBYEARMONTHTIMEFORMAT );
				break;

			case 'week':
				[ $today_week_start, $today_week_end ] = Dates::get_week_start_end( $today, (int) $this->context->get( 'start_of_week', 0 ) );
				[ $view_week_start, $view_week_end ] = Dates::get_week_start_end( $view_date, (int) $this->context->get( 'start_of_week', 0 ) );

				$today_formatted     = $today_week_start->format( Dates::DBDATEFORMAT );
				$view_date_formatted = $view_week_start->format( Dates::DBDATEFORMAT );
				break;

			default:
				$today_formatted     = Dates::build_date_object( $today )->format( Dates::DBDATEFORMAT );
				$view_date_formatted = Dates::build_date_object( $view_date )->format( Dates::DBDATEFORMAT );
		}

		// If view date is not empty and today does not equal the view date, then do not show.
		if ( ! empty( $view_date ) && $today_formatted !== $view_date_formatted ) {
			return;
		}

		// Flatten Views such as Month and Week that have an array values.
		$first_value = reset( $events );
		if ( is_array( $first_value ) ) {
			$events = array_unique( array_merge( ...array_values( $events ) ), SORT_REGULAR );
		}

		/**
		 * Filters The Threshold to Show The Latest Past Events.
		 * Defaults to show when there are Zero Events.
		 *
		 * @since 5.1.0
		 *
		 * @param int   The threshold to show The Latest Past Events.
		 * @param array $events        Array that will be counted to verify if we have events.
		 * @param array $template_vars An associative array of variables that will be set, and exported, in the template.
		 * @param View  $instance      The current View object.
		 */
		$latest_past_threshold = apply_filters( 'tribe_events_views_v2_threshold_to_show_latest_past_events', absint( 0 ), $events, $template_vars, $this );

		// If threshold is less than upcoming events, do not show Recent Past Events.
		if ( $latest_past_threshold < count( $events ) ) {
			return;
		}

		// If no events found, do not show.
		if ( 0 === tribe_events()->found() ) {
			return;
		}

		if ( ! empty( $template_vars['show_latest_past'] ) ) {
			$template_vars['show_latest_past'] = true;
			$latest_past_view                  = static::make( Latest_Past_View::get_view_slug() );
			$latest_past_view->set_context( $this->context );
			$latest_past_view->add_view_filters();
		}
	}

	/**
	 * Returns the number of upcoming events in relation to the "now" time.
	 *
	 * @since 5.2.0
	 *
	 * @return int The number of upcoming events from "now".
	 */
	protected function upcoming_events_count() {
		$now       = $this->context->get( 'now', Dates::build_date_object()->format( 'Y-m-d H:i:s' ) );
		$from_date = tribe_beginning_of_day( $now );

		return (int) tribe_events()->where( 'starts_after', $from_date )->found();
	}

	/**
	 * Returns the View current URL query arguments, parsed from the View `get_url()` method.
	 *
	 * Since there are a number of parties filtering each View URL arguments, this method will
	 * parse a View URL query arguments from its filtered URL. This will include all the modifications
	 * done to a View URL by other plugins and add-ons.
	 *
	 * @since 5.3.0
	 *
	 * @return array<string,mixed> The current View URL args or an empty array if the View URL is empty
	 *                             or not valid..
	 */
	public function get_url_args() {
		$view_url       = $this->get_url( false );
		$view_query_str = wp_parse_url( $view_url, PHP_URL_QUERY );
		if ( empty( $view_query_str ) ) {
			// This might happen if the URL is too mangled to be parsed.
			return [];
		}
		parse_str( $view_query_str, $view_query_args );

		return (array) $view_query_args;
	}

	/**
	 * Initializes the View repository args, if required, and
	 * applies them to the View repository instance.
	 *
	 * @since 4.6.0
	 */
	protected function get_repository_args() {
		if ( ! empty( $this->repository_args ) ) {
			return $this->repository_args;
		}

		return $this->filter_repository_args( $this->setup_repository_args() );
	}

	/**
	 * Compiled the global repository args that should be applied to all events queried for this view.
	 *
	 * @since 6.0.5
	 *
	 * @return array The global filtered repository args.
	 */
	protected function get_global_repository_args() {
		if ( ! is_array( $this->global_repository_args ) ) {
			/**
			 * Will filter any repository args to be applied globally on the various repository queries on
			 * this view.
			 *
			 * @since 6.0.5
			 *
			 * @param array<string,mixed> Events Repository args that will be applied globally to all event
			 *                            repository queries.
			 * @param View $this          The View object being rendered.
			 *
			 * @return array<string,mixed> The repository args to be applied.
			 */
			$this->global_repository_args = apply_filters( 'tec_events_views_v2_view_global_repository_args', [], $this );

			$view_slug = static::$view_slug;

			/**
			 * A view specific filter for repository args to be applied globally on the various repository
			 * queries on this view.
			 *
			 * @since 6.0.5
			 *
			 * @param array<string,mixed> Events Repository args that will be applied globally to all
			 *                            event repository queries.
			 * @param View $this          The View object being rendered.
			 *
			 * @return array<string,mixed> The repository args to be applied.
			 */
			$this->global_repository_args = apply_filters( "tec_events_views_v2_{$view_slug}_view_global_repository_args", $this->global_repository_args, $this );
		}

		return $this->global_repository_args;
	}

	/**
	 * Sets up the View repository args to produce the correct list of Events
	 * in the context of an iCalendar export.
	 *
	 * @since 4.6.0
	 *
	 * @param int $per_page The number of events per page to show in the iCalendar
	 *                      export. The value will override whatever events per page
	 *                      setting the View might have.
	 */
	protected function setup_ical_repository_args( $per_page ) {
		if ( empty( $this->repository_args ) ) {
			$this->repository->by_args( $this->filter_ical_repository_args( $this->get_repository_args() ) );
		}

		// Overwrites the amount of posts manually for ical.
		$this->repository->per_page( $per_page );
	}

	/**
	 * Filters the repository arguments that will be used to set up the View repository instance for iCal requests.
	 *
	 * @since 4.6.0
	 *
	 * @param array $repository_args The repository arguments that will be used to set up the View repository instance.
	 *
	 * @return array The filtered repository arguments for ical requests.
	 */
	protected function filter_ical_repository_args( $repository_args ) {
		/**
		 * Filters the repository args for a View on iCal requests.
		 *
		 * @since 4.6.0
		 *
		 * @param array          $repository_args An array of repository arguments that will be set for all Views.
		 * @param View_Interface $this            The View that will use the repository arguments.
		 */
		$repository_args = apply_filters( 'tribe_events_views_v2_view_ical_repository_args', $repository_args, $this );

		$view_slug = static::$view_slug;
		/**
		 * Filters the repository args for a specific View on iCal requests.
		 *
		 * @since 4.6.0
		 *
		 * @param array          $repository_args An array of repository arguments that will be set for a specific View.
		 * @param View_Interface $this            The View that will use the repository arguments.
		 */
		$repository_args = apply_filters(
			"tribe_events_views_v2_view_{$view_slug}_ical_repository_args",
			$repository_args,
			$this
		);

		return $repository_args;
	}

	/**
	 * Filters the current template current view which allows you to pull globally which view is currently being rendered.
	 *
	 * @since 6.0.0
	 *
	 * @param View_Interface $view Which is the previous view.
	 *
	 * @return self
	 */
	public function filter_set_current_view( $view ) {
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_ical_ids( $per_page ) {
		$this->setup_ical_repository_args( $per_page );

		$ids = $this->repository->get_ids();

		// Reset the repository args to force a re-initialization of the repository on next run.
		$this->repository_args = null;

		return $ids;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_url_object( Url $url_object ) {
		$this->url = $url_object;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disable_url_management() {
		$this->should_manage_url = false;

		return $this;
	}

	/**
	 * Gets the base object for asset registration.
	 *
	 * @since 5.7.0
	 *
	 * @return \stdClass $object Object to tie registered assets to.
	 */
	public static function get_asset_origin( $slug ) {
		$asset_registration_object = tribe( 'tec.main' );

		/**
		 * Filters the object used for registering assets.
		 *
		 * @since 5.7.0
		 *
		 * @param \stdClass $origin_object Object used for asset registration.
		 * @param string    $slug          View slug.
		 */
		$asset_registration_object = apply_filters( "tribe_events_views_v2_view_{$slug}_asset_origin_object", $asset_registration_object, $slug );

		return $asset_registration_object;
	}

	/**
	 * Registers assets for the view.
	 *
	 * Should be overridden if there are assets for the view.
	 *
	 * @since 5.7.0
	 *
	 * @param \stdClass $object Object to tie registered assets to.
	 */
	public static function register_assets( $object ) {
		// Default to a no-op.
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_rewrite_slugs(): array {
		// This translation method relies on the slug being translated elsewhere.
		return [ static::$view_slug, translate( static::$view_slug, 'the-events-calendar' ) ];
	}
}
