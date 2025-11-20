<?php
/**
 * Bootstrap Events Templating system, which by default will hook into
 * the WordPress normal template workflow to allow the injection the Events
 * archive.
 *
 * @since   4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe\Utils\Body_Classes;
use Tribe__Events__Main as TEC;
use Tribe__Events__Templates as V1_Event_Templates;
use Tribe__Notices;
use Tribe__Utils__Array as Arr;
use WP_Query;


/**
 * Class Template_Bootstrap
 *
 * @since   4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
class Template_Bootstrap {
	/**
	 * A cache array shared among instances.
	 *
	 * @since 5.0.1
	 *
	 * @var array<string,array>
	 */
	protected static $cache = [ 'should_load' => [] ];

	/**
	 * An instance of the Template Manager object.
	 *
	 * @since 4.9.11
	 *
	 * @var Manager
	 */
	protected $manager;

	/**
	 * Template_Bootstrap constructor.
	 *
	 * @param Manager $manager An instance of the manager object.
	 */
	public function __construct( Manager $manager  ) {
		$this->manager = $manager;
	}

	/**
	 * Disables the Views V1 implementation of a Template Hijack
	 *
	 * @since  4.9.2
	 *
	 * @return void
	 */
	public function disable_v1() {
		remove_filter( 'tribe_events_before_html', [ TEC::instance(), 'before_html_data_wrapper' ] );
		remove_filter( 'tribe_events_after_html', [ TEC::instance(), 'after_html_data_wrapper' ] );
	}

	/**
	 * Determines, with backwards compatibility in mind, which template user has selected
	 * on the Events > Settings page as their base Default template.
	 *
	 * @since  4.9.2
	 * @since 5.0.3 specifically pass 'event' to be clearer down the line where we check for custom templates.
	 *
	 * @return string Either 'event', 'page' or custom based templates
	 */
	public function get_template_setting() {
		$default_value = 'default';
		$template      = tribe_get_option( 'tribeEventsTemplate', $default_value );

		if ( empty( $template ) ) {
			$template = 'event';
		} elseif ( $default_value === $template ) {
			$template = 'page';
		}

		return $template;
	}

	/**
	 * Based on the admin template setting we fetch the respective object
	 * to handle the inclusion of the main file.
	 *
	 * @since  4.9.2
	 * @since 5.0.3 inverted logic, as all the custom templates are page templates
	 *
	 * @return object
	 */
	public function get_template_object() {
		$setting = $this->get_template_setting();

		return 'event' === $setting
			? tribe( Template\Event::class )
			: tribe( Template\Page::class );
	}

	/**
	 * Determines whether we are in a Single event page or not, base only on global context.
	 *
	 * @since  4.9.11
	 *
	 * @return bool Whether the current request is for the single event template or not.
	 */
	public function is_single_event() {
		if( ! did_action( 'parse_query' ) ) {
			return false;
		}

		$conditions = [
			tribe_context()->get( 'tec_post_type' ),
			is_singular( TEC::POSTTYPE ),
			'single-event' === tribe_context()->get( 'view' ),
		];

		return in_array( true, $conditions, true );
	}

	/**
	 * Determines whether we are in a Single organizer page or not, based only on global context.
	 *
	 * @since  6.11.0
	 *
	 * @return bool Whether the current request is for the single organizer template or not.
	 */
	public function is_single_organizer() {
		if ( ! did_action( 'parse_query' ) ) {
			return false;
		}

		$conditions = [
			is_singular( TEC::ORGANIZER_POST_TYPE ),
		];

		return in_array( true, $conditions, true );
	}

	/**
	 * Determines whether we are in a Single Venue page or not, based only on global context.
	 *
	 * @since  6.11.0
	 *
	 * @return bool Whether the current request is for the single venue template or not.
	 */
	public function is_single_venue() {
		if ( ! did_action( 'parse_query' ) ) {
			return false;
		}

		$conditions = [
			is_singular( TEC::VENUE_POST_TYPE ),
		];

		return in_array( true, $conditions, true );
	}

	/**
	 * Sets the current view context to `single-event` for the legacy view system.
	 *
	 * @since 6.4.1
	 *
	 * @return string
	 */
	public function context_view_as_single_event() {
		return 'single-event';
	}

	/**
	 * Fetches the HTML for the Single Event page using the legacy view system
	 *
	 * @since  4.9.4
	 *
	 * @return string
	 */
	protected function get_v1_single_event_html() {
		if ( ! tribe_is_showing_all() && tribe_is_past_event() ) {
			Tribe__Notices::set_notice( 'event-past', sprintf( esc_html__( 'This %s has passed.', 'the-events-calendar' ), tribe_get_event_label_singular_lowercase() ) );
		}

		// Set our context to read as a single-event view.
		if ( ! has_filter( "tribe_context_view", [ $this, 'context_view_as_single_event' ] ) ) {
			add_filter( "tribe_context_view", [ $this, 'context_view_as_single_event' ] );
		}

		$setting = $this->get_template_setting();

		// A number of TEC, and third-party, functions, depend on this. Let's fire it.
		global $post, $wp_query;
		do_action( 'the_post', $post, $wp_query );

		ob_start();
		if ( 'page' === $setting ) {
			echo '<section id="tribe-events">';
		} else {
			echo '<section id="tribe-events-pg-template" class="tribe-events-pg-template">';
		}
		tribe_events_before_html();
		tribe_get_view( 'single-event' );
		tribe_events_after_html();
		echo '</section>';

		$html = ob_get_clean();

		if ( function_exists( 'do_blocks' ) ) {
			$html = do_blocks( $html );
		}

		return $html;
	}

	/**
	 * Fetches the template for the Single Embed Event page using the legacy view system.
	 *
	 * @since  4.9.13
	 *
	 * @return string
	 */
	protected function get_v1_embed_template_path() {
		if ( ! tribe_is_showing_all() && tribe_is_past_event() ) {
			Tribe__Notices::set_notice( 'event-past', sprintf( esc_html__( 'This %s has passed.', 'the-events-calendar' ), tribe_get_event_label_singular_lowercase() ) );
		}

		$template_path = V1_Event_Templates::getTemplateHierarchy( 'embed' );
		return $template_path;
	}

	/**
	 * Gets the View HTML
	 *
	 * @todo Stop handling kitchen sink template here.
	 *
	 * @since  4.9.2
	 *
	 * @return string
	 */
	public function get_view_html() {
		$query     = tribe_get_global_query_object();
		$context   = tribe_context();
		$view_slug = $context->get( 'event_display' );

		/**
		 * Filters the HTML for the view before we do any other logic around that.
		 *
		 * @since 5.0.0
		 *
		 * @param string          $pre_html  Allow pre-filtering the HTML that we will bootstrap.
		 * @param string          $view_slug The slug of the View that will be built, based on the context.
		 * @param \Tribe__Context $context   Tribe context used to setup the view.
		 * @param \WP_Query       $query     The current WP Query object.
		 */
		$pre_html = apply_filters( 'tribe_events_views_v2_bootstrap_pre_get_view_html', null, $view_slug, $query, $context );

		if ( null !== $pre_html ) {
			return $pre_html;
		}

		$should_display_single = (
			$this->is_single_event()
			&& ! tribe_is_showing_all()
			&& ! is_embed()
		);

		/**
		 * Filters when we display the single for events.
		 *
		 * @since 5.0.0
		 *
		 * @param boolean         $should_display_single  If we are currently going to display single.
		 * @param string          $view_slug              The slug of the View that will be built, based on the context.
		 * @param \Tribe__Context $context                Tribe context used to setup the view.
		 * @param \WP_Query       $query                  The current WP Query object.
		 */
		$should_display_single = apply_filters( 'tribe_events_views_v2_bootstrap_should_display_single', $should_display_single, $view_slug, $query, $context );

		if ( $should_display_single ) {
			$html = $this->get_v1_single_event_html();
		} elseif ( isset( $query->query_vars['tribe_events_views_kitchen_sink'] ) ) {
			$context = [
				'query' => $query,
			];

			/**
			 * @todo  Replace with actual code for view and move this to correct kitchen sink
			 */
			$template = Arr::get( $context['query']->query_vars, 'tribe_events_views_kitchen_sink', 'page' );
			if ( ! in_array( $template, tribe( Kitchen_Sink::class )->get_available_pages() ) ) {
				$template = 'page';
			}

			$html = tribe( Kitchen_Sink::class )->template( $template, $context, false );
		} else {
			/**
			 * Filters the slug of the view that will be loaded, to allow changing view  based on the context of a given
			 * request.
			 *
			 * @since  4.9.11
			 *
			 * @param string          $view_slug The slug of the View that will be built, based on the context.
			 * @param \Tribe__Context $context   Tribe context used to setup the view.
			 * @param \WP_Query       $query     The current WP Query object.
			 */
			$view_slug = apply_filters( 'tribe_events_views_v2_bootstrap_view_slug', $view_slug, $context, $query );

			$html = View::make( $view_slug, $context )->get_html();
		}

		/**
		 * Filters the HTML for the view before we do any other logic around that.
		 *
		 * @since 5.0.0
		 *
		 * @param string          $html      The html to be displayed.
		 * @param \Tribe__Context $context   Tribe context used to setup the view.
		 * @param string          $view_slug The slug of the View that we've built,
		 *                                   based on the context but possibly altered in the build process.
		 * @param \WP_Query       $query     The current WP Query object.
		 */
		return apply_filters( 'tribe_events_views_v2_bootstrap_html', $html, $context, $view_slug, $query );
	}

	/**
	 * Determines when we should bootstrap the template for The Events Calendar
	 *
	 * @since  4.9.2
	 *
	 * @param  WP_Query $query Which WP_Query object we are going to load on
	 *
	 * @return boolean Whether any template managed by this class should load at all or not.
	 */
	public function should_load( $query = null ) {
		if ( ! $query instanceof \WP_Query ) {
			$query = tribe_get_global_query_object();
		}

		if ( ! $query instanceof WP_Query ) {
			// Cannot discriminate, bail.
			return false;
		}

		$should_load = null;
		if ( ! empty( $query->query_vars_hash ) && isset( static::$cache['should_load'][ $query->query_vars_hash ] ) ) {
			$should_load = static::$cache['should_load'][ $query->query_vars_hash ];
		}

		/**
		 * Allows filtering if bootstrap should load.
		 *
		 * @since 5.0.0
		 *
		 * @param null|boolean    $should_load  Anything other then null will be returned after casting as bool.
		 * @param \WP_Query       $query        The current WP Query object.
		 */
		$should_load = apply_filters( 'tribe_events_views_v2_bootstrap_pre_should_load', $should_load, $query );
		if ( null !== $should_load ) {
			static::$cache['should_load'][ $query->query_vars_hash ] = (bool) $should_load;

			return (bool) $should_load;
		}

		if ( ! $query instanceof \WP_Query ) {
			static::$cache['should_load'][ $query->query_vars_hash ] = false;

			return false;
		}

		if ( $query->is_404() ) {
			static::$cache['should_load'][ $query->query_vars_hash ] = false;

			return false;
		}

		/**
		 * Bail if we are not dealing with an Event, Venue or Organizer main query.
		 *
		 * The `tribe_is_event_query` property is a logic `OR` of any post type and taxonomy we manage.
		 *
		 * @see \Tribe__Events__Query::parse_query() where this property is set.
		 */
		$should_load = $query->is_main_query() && ! empty( $query->tribe_is_event_query );

		static::$cache['should_load'][ $query->query_vars_hash ] = $should_load;

		return $should_load;
	}

	/**
	 * Filters the `template_include` filter to return the Views router template if required..
	 *
	 * @since 4.9.2
	 *
	 * @param string $template The template located by WordPress.
	 *
	 * @return string Path to the File that initializes the template
	 */
	public function filter_template_include( $template ) {
		if ( tec_is_full_site_editor() ) {
			return $template;
		}

		$query   = tribe_get_global_query_object();
		$context = tribe_context();

		/**
		 * Allows filtering the loading of our proprietary templates.
		 *
		 * @since 5.2.1
		 *
		 * @param boolean        $load     Whether we should load the theme templates instead of the Tribe templates. Default false.
		 * @param string         $template The template located by WordPress.
		 * @param Tribe__Context $context  The singleton, immutable, global object instance.
		 * @param WP_Query       $query    The global $wp_query, the $wp_the_query if $wp_query empty, null otherwise. From tribe_get_global_query_object() above.
		 */
		$load_template = apply_filters( 'tribe_events_views_v2_use_wp_template_hierarchy', false, $template, $context, $query );

		// Let others decide if they want to load our templates or not.
		if ( (bool) $load_template ) {
			return $template;
		}

		// Global 404 needs to be respected.
		if ( $query->is_404() ) {
			return $template;
		}

		// Determine if we should load bootstrap or bail.
		if ( ! $this->should_load() ) {
			return $template;
		}

		$is_embed  = is_embed() || 'embed' === $context->get( 'view' );

		if ( $is_embed ) {
			return $this->get_v1_embed_template_path();
		}

		return $this->get_template_object()->get_path();
	}

	/**
	 * Set the correct body classes for our plugin.
	 *
	 * @since  4.9.11
	 * @since 6.7.2 Cast  object to string to avoid deprecation notices.
	 *
	 * @return array The array containing the body classes
	 */
	public function filter_add_body_classes( $classes ) {
		$setting  = $this->get_template_setting();
		$active_theme = wp_get_theme();

		if ( 'page' !== $setting ) {
			return $classes;
		}

		$classes[] = 'page-template-' . sanitize_title( (string) $active_theme->display( 'Name' ) );

		if ( ! get_queried_object() instanceof \WP_Term ) {
			$key = array_search( 'archive', $classes, true );

			if ( false !== $key ) {
				unset( $classes[ $key ] );
			}
		}

		return $classes;
	}

	/**
	 * Contains the logic for if this object's classes should be added to the queue.
	 *
	 * @since 5.1.5
	 *
	 * @param boolean $add   Whether to add the class to the queue or not.
	 * @param array   $class The array of body class names to add.
	 * @param string  $queue The queue we want to get 'admin', 'display', 'all'.

	 * @return boolean Whether body classes should be added or not.
	 */
	public function should_add_body_class_to_queue( $add, $class, $queue ) {
		if ( 'admin' === $queue ) {
			return $add;
		}

		if ( 'tribe-events-page-template' === $class ) {
			$setting = $this->get_template_setting();

			if ( 'page' !== $setting ) {
				return true;
			}
		}

		return $add;
	}

	/**
	 * Add body classes.
	 *
	 * @since 5.1.5
	 *
	 * @return void
	 */
	public function add_body_classes() {
		tribe( Body_Classes::class )->add_class( 'tribe-events-page-template' );
	}

	/**
	 * Filter the template file in case we're in single event
	 * and we need to use the theme overrides.
	 *
	 * @since  5.0.0
	 *
	 * @param string $file      Complete path to include the PHP File
	 * @param array  $name      Template name
	 * @param object $template  Instance of the Tribe__Template
	 *
	 * @return string
	 */
	public function filter_template_file( $file, $name, $template ) {
		if ( is_404() ) {
			return $file;
		}

		$template_name = end( $name );

		// Bail when we don't are not loading 'default-template'.
		if ( 'default-template' !== $template_name ) {
			return $file;
		}

		if (
			! is_singular( TEC::POSTTYPE )
			&& 'single-event' !== tribe_context()->get( 'view' )
		) {
			return $file;
		}

		$theme_file = locate_template( 'tribe-events/default-template.php', false, false );

		if ( ! $theme_file ) {
			return $file;
		}

		if ( ! tribe_is_showing_all() && tribe_is_past_event() ) {
			Tribe__Notices::set_notice( 'event-past', sprintf( esc_html__( 'This %s has passed.', 'the-events-calendar' ), tribe_get_event_label_singular_lowercase() ) );
		}

		return $theme_file;
	}

	/**
	 * Wraps the view HTML in a main landmark for accessibility if not already wrapped.
	 *
	 * This ensures that even when themes override the default-template.php,
	 * the content is still wrapped in a proper main landmark element.
	 *
	 * @since 6.15.12
	 * @since 6.15.12.2 Changed the approach to inject the `role` attribute to the first element in the HTML instead of wrapping the entire HTML in a main element.
	 *
	 * @param string $html The HTML output from the view.
	 *
	 * @return string The HTML, wrapped in a main landmark if needed.
	 */
	public function maybe_add_main_landmark( $html ) {
		// Don't add a landmark if we're doing an AJAX request or if this is embed content.
		if ( is_embed() || tribe_context()->doing_ajax() ) {
			return $html;
		}

		$cache = tribe_cache();

		if ( ! empty( $cache['tec_events_views_v2_main_landmark_added'] ) ) {
			return $html;
		}

		$cache['tec_events_views_v2_main_landmark_added'] = true;

		if ( strstr( $html, 'role="main"' ) ) {
			// A main role is already present.
			return $html;
		}

		return preg_replace( '/<(\w+)([^>]*)>/', '<$1$2 role="main">', $html, 1 );
	}
}
