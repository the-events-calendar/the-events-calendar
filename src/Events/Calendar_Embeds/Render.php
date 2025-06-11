<?php
/**
 * Render External Embed Calendars.
 *
 * @since 6.11.0
 * @package TEC/Events/Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use Tribe\Utils\Taxonomy;
use Tribe\Events\Views\V2\Manager as Views_Manager;
use Tribe\Events\Views\V2\Theme_Compatibility;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Utils\Element_Classes;
use Tribe__Context as Context;
use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe__Repository__Interface as Repository_Interface;

/**
 * Class for rendering the External Calendar Embeds.
 *
 * @since 6.11.0
 *
 * @package TEC/Events/Calendar_Embeds
 */
class Render {
	/**
	 * Prefix for the transient where we will save the base values for the
	 * setup of the context of the embed.
	 *
	 * @since 6.11.0
	 *
	 * @var string
	 */
	const TRANSIENT_PREFIX = 'tec_events_calendar_embeds_view_params_';

	/**
	 * Arguments of the current view.
	 *
	 * @since 6.11.0
	 *
	 * @var array
	 */
	protected $arguments;

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.11.0
	 *
	 * @var array
	 */
	protected $default_arguments = [
		'author'               => null,
		'category'             => null,
		'container-classes'    => [],
		'date'                 => null,
		'events_per_page'      => null,
		'exclude-category'     => null,
		'exclude-tag'          => null,
		'featured'             => null,
		'filter-bar'           => false,
		'hide_weekends'        => false,
		'hide-datepicker'      => false,
		'hide-export'          => false,
		'id'                   => null,
		'is-widget'            => false,
		'jsonld'               => true,
		'keyword'              => null,
		'layout'               => 'vertical', // @todo Change to auto when we enable that option.
		'main-calendar'        => false,
		'month_events_per_day' => null,
		'organizer'            => null,
		'past'                 => false,
		'should_manage_url'    => false, // @todo @bordoni @lucatume @be Update this when URL management is fixed.
		'skip-empty'           => false,
		'tag'                  => null,
		'tax-operand'          => 'AND',
		'tribe-bar'            => false,
		'venue'                => null,
		'view'                 => null,
		'week_events_per_day'  => null,
		'week_offset'          => null,
	];

	/**
	 * Setup the arguments for the view.
	 *
	 * @since 6.11.0
	 *
	 * @param array $arguments Arguments to be used to setup the view.
	 */
	public function setup( array $arguments ): void {
		$this->arguments = wp_parse_args( $arguments, $this->default_arguments );
	}

	/**
	 * Toggles the filtering of URLs to match the place where this is called.
	 *
	 * @since 6.11.0
	 *
	 * @param bool $toggle Whether to turn the hooks on or off.
	 *
	 * @return void
	 */
	protected function toggle_view_hooks( $toggle ): void {
		if ( $toggle ) {
			$this->add_view_hooks();
		} else {
			$this->remove_view_hooks();
		}

		/**
		 * Fires after View hooks have been toggled while rendering.
		 *
		 * @since 6.11.0
		 *
		 * @param bool   $toggle   Whether the hooks should be turned on or off.
		 * @param static $instance The instance that is toggling the View hooks.
		 */
		do_action( 'tec_events_calendar_embeds_render_toggle_view_hooks', $toggle, $this );
	}

	/**
	 * Toggles on portions of the template based on the params.
	 *
	 * @since 6.11.0
	 */
	protected function add_view_hooks(): void {
		add_filter( 'tribe_events_views_v2_url_query_args', [ $this, 'filter_view_query_args' ], 15 );
		add_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_view_repository_args' ], 10, 2 );
		add_filter( 'tribe_events_views_v2_view_html_classes', [ $this, 'filter_view_html_classes' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_container_data', [ $this, 'filter_view_data' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_url_query_args', [ $this, 'filter_view_url_query_args' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_context', [ $this, 'filter_view_context' ], 10, 2 );
		add_filter( 'tribe_events_views_v2_manager_default_view', [ $this, 'filter_default_url' ] );
		add_filter( 'tribe_events_views_v2_view_url', [ $this, 'filter_view_url' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_next_url', [ $this, 'filter_view_url' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_prev_url', [ $this, 'filter_view_url' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_week_breakpoints', [ $this, 'filter_week_view_breakpoints' ], 10, 2 );
		add_filter( 'tribe_events_views_v2_ff_link_next_event', [ $this, 'filter_ff_link_next_event' ], 10, 2 );

		// Removing tribe-bar when that argument is `false`.
		if (
			! tribe_is_truthy( $this->get_argument( 'tribe-bar' ) )
			|| tribe_is_truthy( $this->get_argument( 'is-widget' ) )
		) {
			add_filter( 'tribe_template_html:events/v2/components/events-bar', '__return_false' );
		}

		// Removing export button when that argument is `true`.
		if (
			tribe_is_truthy( $this->get_argument( 'hide-export' ) )
			|| tribe_is_truthy( $this->get_argument( 'is-widget' ) )
		) {
			add_filter( 'tribe_template_html:events/v2/components/ical-link', '__return_false' );
		}

		/* Filter Bar */
		if (
			! tribe_is_truthy( $this->get_argument( 'filter-bar' ) )
			|| ! tribe_is_truthy( $this->get_argument( 'tribe-bar' ) )
			|| tribe_is_truthy( $this->get_argument( 'is-widget' ) )
		) {
			add_filter( 'tribe_events_filter_bar_views_v2_should_display_filters', '__return_false' );
			add_filter( 'tribe_events_filter_bar_views_v2_1_should_display_filters', '__return_false' );
			add_filter( 'tribe_events_filter_bar_views_v2_assets_should_enqueue_frontend', '__return_false' );
			add_filter( 'tribe_events_views_v2_filter_bar_view_html_classes', '__return_false' );

			if ( tribe()->isBound( 'filterbar.views.v2_1.hooks' ) ) {
				remove_filter(
					'tribe_events_pro_shortcode_tribe_events_before_assets',
					[ tribe( 'filterbar.views.v2_1.hooks' ), 'action_include_assets' ]
				);
			} elseif ( tribe()->isBound( 'filterbar.views.v2.hooks' ) ) {
				remove_filter(
					'tribe_events_pro_shortcode_tribe_events_before_assets',
					[ tribe( 'filterbar.views.v2.hooks' ), 'action_include_assets' ]
				);
			}
		}

		/* Month widget only. */
		if (
			Month_View::get_view_slug() === $this->get_argument( 'view' )
			&& tribe_is_truthy( $this->get_argument( 'is-widget' ) )
		) {
			/* Mobile "footer" nav */
			add_filter( 'tribe_template_html:events/v2/month/mobile-events/nav', '__return_false' );
		}

		// Removing datepicker when that argument is `true`.
		if (
			tribe_is_truthy( $this->get_argument( 'hide-datepicker' ) )
			|| tribe_is_truthy( $this->get_argument( 'is-widget' ) )
		) {
			add_filter( 'tribe_template_html:events/v2/month/top-bar/datepicker', '__return_false' );
			add_filter( 'tribe_template_html:events-pro/v2/week/top-bar/datepicker', '__return_false' );
		}

		if ( ! tribe_is_truthy( $this->get_argument( 'jsonld' ) ) ) {
			add_filter( 'tribe_template_html:events/v2/components/json-ld-data', '__return_false' );
		}

		// Past Events - don't navigate to empty months.
		if ( tribe_is_truthy( $this->get_argument( 'past', false ) ) ) {
			add_filter( 'tribe_events_views_v2_month_nav_skip_empty', [ $this, 'filter_skip_empty' ] );
		}
	}

	/**
	 * Hide weekends.
	 *
	 * @since 6.11.0
	 *
	 * @param mixed  $value       The value for the option.
	 * @param string $option_name The name of the option.
	 *
	 * @return mixed The value for the option.
	 */
	public function week_view_hide_weekends( $value, $option_name ) {
		if ( 'week_view_hide_weekends' !== $option_name ) {
			return $value;
		}

		return true;
	}

	/**
	 * Toggles off portions of the template that were toggled on above.
	 *
	 * @since 6.11.0
	 */
	protected function remove_view_hooks(): void {
		remove_filter( 'tribe_events_views_v2_url_query_args', [ $this, 'filter_view_query_args' ], 15 );
		remove_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_view_repository_args' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_html_classes', [ $this, 'filter_view_html_classes' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_container_data', [ $this, 'filter_view_data' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_url_query_args', [ $this, 'filter_view_url_query_args' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_context', [ $this, 'filter_view_context' ], 10 );
		remove_filter( 'tribe_events_views_v2_manager_default_view', [ $this, 'filter_default_url' ] );
		remove_filter( 'tribe_events_views_v2_view_url', [ $this, 'filter_view_url' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_next_url', [ $this, 'filter_view_url' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_prev_url', [ $this, 'filter_view_url' ], 10 );

		remove_filter( 'tribe_template_html:events/v2/components/events-bar', '__return_false' ); // tribe-bar.
		remove_filter( 'tribe_template_html:events/v2/components/ical-link', '__return_false' ); // hide-export.
		remove_filter( 'tribe_template_html:events/v2/month/top-bar/datepicker', '__return_false' ); // hide-datepicker.
		remove_filter( 'tribe_template_html:events-pro/v2/week/top-bar/datepicker', '__return_false' ); // hide-datepicker.

		// Filter Bar.
		remove_filter( 'tribe_events_filter_bar_views_v2_should_display_filters', '__return_false' );
		remove_filter( 'tribe_events_filter_bar_views_v2_1_should_display_filters', '__return_false' );
		remove_filter( 'tribe_events_filter_bar_views_v2_assets_should_enqueue_frontend', '__return_false' );
		remove_filter( 'tribe_events_views_v2_filter_bar_view_html_classes', '__return_false' );
		// Yes, add - we're adding it back.
		if ( tribe()->isBound( 'filterbar.views.v2_1.hooks' ) ) {
			add_filter( 'tribe_events_pro_shortcode_tribe_events_before_assets', [ tribe( 'filterbar.views.v2_1.hooks' ), 'action_include_assets' ] );
		} elseif ( tribe()->isBound( 'filterbar.views.v2.hooks' ) ) {
			add_filter( 'tribe_events_pro_shortcode_tribe_events_before_assets', [ tribe( 'filterbar.views.v2.hooks' ), 'action_include_assets' ] );
		}

		remove_filter( 'tribe_get_option', [ $this, 'week_view_hide_weekends' ] );
		remove_filter( 'tribe_events_views_v2_view_week_breakpoints', [ $this, 'filter_week_view_breakpoints' ], 10 );

		remove_filter( 'tribe_events_views_v2_week_events_per_day', [ $this, 'views_v2_week_events_per_day' ], 10 );
		remove_filter( 'tribe_events_views_v2_ff_link_next_event', [ $this, 'filter_ff_link_next_event' ], 10 );

		// Past Events - don't navigate to empty months.
		remove_filter( 'tribe_events_views_v2_month_nav_skip_empty', [ $this, 'filter_skip_empty' ] );
	}

	/**
	 * Maybe toggles the hooks on a rest request.
	 *
	 * @since 6.11.0
	 *
	 * @param string $slug   The current view Slug.
	 * @param array  $params Params so far that will be used to build this view.
	 */
	public static function maybe_toggle_hooks_for_rest( string $slug, array $params ): void {
		$embed = Arr::get( $params, 'embed', false );
		if ( ! $embed ) {
			return;
		}

		$view_instance = new self();
		$db_args       = $view_instance->get_database_arguments( $embed );

		if ( empty( $db_args ) ) {
			return;
		}

		$view_instance->setup( $db_args, '' );

		$view_instance->toggle_view_hooks( true );
	}

	/**
	 * Verifies if we should allow View URL management.
	 *
	 * @since 6.11.0
	 *
	 * @return bool
	 */
	public function should_manage_url(): bool {
		// Defaults to true due to old behaviors on Views V1.
		$should_manage_url = $this->get_argument( 'should_manage_url', $this->default_arguments['should_manage_url'] );

		$disallowed_locations = [
			'widget_text_content',
		];

		/**
		 * Allows filtering of the disallowed locations for URL management.
		 *
		 * @since 6.11.0
		 *
		 * @param mixed  $disallowed_locations Which filters we don't allow URL management.
		 * @param static $instance             Which instance we are dealing with.
		 */
		$disallowed_locations = apply_filters( 'tec_events_calendar_embeds_render_manage_url_disallowed_locations', $disallowed_locations, $this );

		// Block certain locations.
		foreach ( $disallowed_locations as $location ) {
			// If any we are in any of the disallowed locations.
			if ( doing_filter( $location ) ) {
				$should_manage_url = $this->default_arguments['should_manage_url'];
			}
		}

		/**
		 * Allows filtering if URL management is active.
		 *
		 * @since 6.11.0
		 *
		 * @param mixed  $should_manage_url Should we manage the URL for this views instance.
		 * @param static $instance          Which instance we are dealing with.
		 */
		$should_manage_url = apply_filters( 'tec_events_calendar_embeds_render_should_manage_url', $should_manage_url, $this );

		return (bool) $should_manage_url;
	}

	/**
	 * Changes the URL to match this view if needed.
	 *
	 * @since 6.11.0
	 *
	 * @param array $query_args Current URL for this view.
	 *
	 * @return array The filtered View query args, with the View ID added.
	 */
	public function filter_view_query_args( $query_args ): array {
		$query_args['embed'] = $this->get_id();
		unset( $query_args['tag'] );

		return $query_args;
	}

	/**
	 * Fetches from the database the params of a given view based on the ID created.
	 *
	 * @since 6.11.0
	 *
	 * @param string $embed_id The identifier, or `null` to use the current one.
	 *
	 * @return array Array of params configuring the View.
	 */
	public function get_database_arguments( ?string $embed_id = null ): array {
		$embed_id            = $embed_id ?: $this->get_id();
		$transient_key       = static::TRANSIENT_PREFIX . $embed_id;
		$transient_arguments = get_transient( $transient_key );

		return (array) $transient_arguments;
	}

	/**
	 * Configures the Relationship between view ID and their params in the database
	 * allowing us to pass the URL as the base for the Queries.
	 *
	 * @since 6.11.0
	 *
	 * @return  bool  Return if we have the arguments configured or not.
	 */
	public function set_database_params(): bool {
		$embed_id           = $this->get_id();
		$transient_key      = static::TRANSIENT_PREFIX . $embed_id;
		$db_arguments       = $this->get_database_arguments();
		$db_arguments['id'] = $embed_id;

		// If the value is the same it's already in the Database.
		if ( $db_arguments === $this->get_arguments() ) {
			return true;
		}

		return set_transient( $transient_key, $this->get_arguments() );
	}

	/**
	 * Alters the context with its arguments.
	 *
	 * @since 6.11.0
	 *
	 * @param Context $context Context we will use to build the view.
	 * @param array   $arguments Arguments to be used to alter the context.
	 *
	 * @return Context Context after view changes.
	 */
	public function alter_context( Context $context, array $arguments = [] ): Context {
		$embed_id = $context->get( 'id' );

		if ( empty( $arguments ) ) {
			$arguments = $this->get_arguments();
			$embed_id  = $this->get_id();
		}

		$alter_context = $this->args_to_context( $arguments, $context );

		// The View will consume this information on initial state.
		$alter_context['embed'] = $embed_id;
		$alter_context['id']    = $embed_id;

		$context = $context->alter( $alter_context );

		return $context;
	}

	/**
	 * Based on the either a argument "id" of the definition
	 * or the 8 first characters of the hashed version of a string serialization
	 * of the params sent to the view we will create/get an ID for this
	 * instance of the view
	 *
	 * @since 6.11.0
	 *
	 * @return string The view unique(ish) identifier.
	 */
	public function get_id(): string {
		$arguments = $this->get_arguments();

		// In case we have the ID argument we just return that.
		if ( ! empty( $arguments['id'] ) ) {
			return $arguments['id'];
		}

		// @todo: We hates it, my precious - find a better way.
		if ( is_array( $arguments ) ) {
			ksort( $arguments );
		}

		/*
		 * Generate a string id based on the arguments used to setup the view.
		 * Note that arguments are sorted to catch substantially same view w. diff. order argument.
		 */
		return substr( md5( maybe_serialize( $arguments ) ), 0, 8 );
	}

	/**
	 * Determines if we should display the view in a given page.
	 *
	 * @since 6.11.0
	 *
	 * @return bool
	 */
	public function should_display(): bool {
		/**
		 * On blocks editor views are being rendered in the screen which for some unknown reason makes the admin
		 * URL soft redirect (browser history only) to the front-end view URL of that view.
		 *
		 * @see TEC-3157
		 */
		$should_display = true;

		/**
		 * If we should display the view.
		 *
		 * @since 6.11.0
		 *
		 * @param bool   $should_display Whether we should display or not.
		 * @param static $view           Instance of the view we are dealing with.
		 */
		$should_display = apply_filters( 'tec_events_calendar_embeds_render_should_display', $should_display, $this );

		return tribe_is_truthy( $should_display );
	}

	/**
	 * Renders the HTML.
	 *
	 * @since 6.11.0
	 *
	 * @return string The HTML.
	 */
	public function get_html(): string {
		if ( ! $this->should_display() ) {
			return '';
		}

		/**
		 * Please if you don't understand what these are doing, don't touch this.
		 */
		$context = tribe_context();

		// Before anything happens we set a DB ID and value for this view entry.
		$this->set_database_params();

		// Modifies the Context for the view params.
		$context = $this->alter_context( $context );

		$context->disable_read_from( [ Context::REQUEST_VAR, Context::QUERY_VAR, Context::WP_MATCHED_QUERY, Context::WP_PARSED ] );

		// Fetches if we have a specific view are building.
		$view_slug = $this->get_argument( 'view', $context->get( 'view' ) );

		// Toggle the view required modifications.
		$this->toggle_view_hooks( true );

		// Setup the view instance.
		$view = View::make( $view_slug, $context );

		// Setup whether this view should manage url or not.
		$view->get_template()->set( 'should_manage_url', $this->should_manage_url() );

		$theme_compatibility = tribe( Theme_Compatibility::class );

		$html = '';

		/**
		 * Allows removing the compatibility container.
		 *
		 * @since 6.11.0
		 *
		 * @param bool   $compatibility_required Is compatibility required for this view.
		 * @param static $view                   View instance that is being rendered.
		 */
		$compatibility_required = apply_filters(
			'tec_events_calendar_embeds_render_compatibility_required',
			$theme_compatibility->is_compatibility_required(),
			$this
		);

		if ( $compatibility_required ) {
			$container       = [ 'tribe-compatibility-container' ];
			$classes         = array_merge( $container, $theme_compatibility::get_compatibility_classes() );
			$element_classes = new Element_Classes( $classes );
			$html           .= '<div ' . $element_classes->get_attribute() . '>';
		}

		$html .= $view->get_html();

		if ( $compatibility_required ) {
			$html .= '</div>';
		}

		// Toggle the view required modifications.
		$this->toggle_view_hooks( false );

		/**
		 * Please if you don't understand what these are doing, don't touch this.
		 */
		$context->refresh();

		return $html;
	}

	/**
	 * Filters the View repository args to add the ones required.
	 *
	 * @since 6.11.0
	 *
	 * @param array   $repository_args An array of repository arguments that will be set for all Views.
	 * @param Context $context         The current render context object.
	 *
	 * @return array Repository arguments after view args added.
	 */
	public function filter_view_repository_args( array $repository_args, Context $context ): array {
		if ( ! $context instanceof Context ) {
			return $repository_args;
		}

		$embed_id = $context->get( 'embed', false );

		if ( false === $embed_id ) {
			return $repository_args;
		}

		$view_args = $this->get_database_arguments( $embed_id );

		$repository_args = $this->args_to_repository( (array) $repository_args, (array) $view_args );

		return $repository_args;
	}

	/**
	 * Filters the context locations to add the ones used by Views.
	 *
	 * @since 6.11.0
	 *
	 * @param array $locations The array of context locations.
	 *
	 * @return array The modified context locations.
	 */
	public static function filter_context_locations( array $locations = [] ): array {
		$locations['embed'] = [
			'read' => [
				Context::REQUEST_VAR   => 'embed',
				Context::LOCATION_FUNC => [
					'view_prev_url',
					static function ( $url ) {
						return tribe_get_query_var( $url, 'embed', Context::NOT_FOUND );
					},
				],
			],
		];

		return $locations;
	}

	/**
	 * Translates view arguments to their Context argument counterpart.
	 *
	 * @since 6.11.0
	 *
	 * @param array   $arguments The view arguments to translate.
	 * @param Context $context   The request context.
	 *
	 * @return array The translated view arguments.
	 */
	protected function args_to_context( array $arguments, Context $context ): array {
		$context_args = [];

		if ( ! empty( $arguments['date'] ) ) {
			$context_args['event_date'] = $arguments['date'];
		}

		if ( ! empty( $arguments[ TEC::TAXONOMY ] ) ) {
			$context_args[ TEC::TAXONOMY ] = $arguments[ TEC::TAXONOMY ];
		}

		if ( ! empty( $arguments['tag'] ) ) {
			$context_args['post_tag'] = $arguments['tag'];
		}

		if ( isset( $arguments['featured'] ) ) {
			$context_args['featured'] = tribe_is_truthy( $arguments['featured'] );
		}

		if ( ! empty( $arguments['events_per_page'] ) ) {
			$context_args['events_per_page'] = (int) $arguments['events_per_page'];
		}

		if ( tribe_is_truthy( $arguments['is-widget'] ) ) {
			$context_args['is-widget'] = tribe_is_truthy( $arguments['is-widget'] );
		}

		if ( ! empty( $arguments['month_events_per_day'] ) ) {
			$context_args['month_posts_per_page'] = (int) $arguments['month_events_per_day'];
		}

		if ( ! empty( $arguments['week_events_per_day'] ) ) {
			$context_args['week_events_per_day'] = (int) $arguments['week_events_per_day'];
		}

		if ( ! empty( $arguments['keyword'] ) ) {
			$context_args['keyword'] = sanitize_text_field( $arguments['keyword'] );
		}

		if ( null === $context->get( 'eventDisplay' ) ) {
			if ( ! empty( $arguments['past'] ) && tribe_is_truthy( $arguments['past'] ) ) {
				$month_slug = Month_View::get_view_slug();
				$manager    = tribe( Views_Manager::class );
				$views      = $manager->get_publicly_visible_views();
				$view_slug  = ! empty( $views[ $month_slug ] ) ? $month_slug : $manager->get_default_view_slug();

				$context_args['view']               = $view_slug;
				$context_args['event_display_mode'] = $view_slug;

			} elseif ( empty( $arguments['view'] ) ) {
				$default_view_class                 = tribe( Views_Manager::class )->get_default_view();
				$context_args['event_display_mode'] = tribe( Views_Manager::class )->get_view_slug_by_class( $default_view_class );
				$context_args['view']               = $context_args['event_display_mode'];
			} else {
				$context_args['event_display_mode'] = $arguments['view'];
				$context_args['view']               = $context_args['event_display_mode'];
			}
		}

		if ( ! empty( $arguments['view'] ) ) {
			$context_args['view']               = $arguments['view'];
			$context_args['event_display_mode'] = $arguments['view'];
		}

		if ( ! empty( $arguments['past'] ) && tribe_is_truthy( $arguments['past'] ) ) {
			$context_args['past']              = tribe_is_truthy( $arguments['past'] );
			$context_args['ends_before']       = tribe_end_of_day( current_time( 'mysql' ) );
			$context_args['latest_event_date'] = tribe_end_of_day( current_time( 'mysql' ) );
		}

		return $context_args;
	}

	/**
	 * Translates view arguments to their Repository argument counterpart.
	 *
	 * @since 6.11.0
	 *
	 * @param array $repository_args The current repository arguments.
	 * @param array $arguments       The view arguments to translate.
	 *
	 * @return array The translated view arguments.
	 */
	public function args_to_repository( array $repository_args, array $arguments ): array {
		if (
			! empty( $arguments['tag'] )
			|| ! empty( $arguments['category'] )
		) {
			$operand = Arr::get( $arguments, 'tax-operand', 'OR' );

			// Makes sure tax query exists.
			if ( empty( $repository_args['tax_query'] ) ) {
				$repository_args['tax_query'] = []; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			}

			$items = [
				'tag'      => 'post_tag',
				'category' => TEC::TAXONOMY,
			];

			foreach ( $items as $key => $taxonomy ) {
				if ( empty( $arguments[ $key ] ) ) {
					continue;
				}

				$repository_args['tax_query'] = Arr::merge_recursive_query_vars( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					$repository_args['tax_query'],
					Taxonomy::translate_to_repository_args( $taxonomy, $arguments[ $key ], $operand )
				);

			}

			$repository_args['tax_query']['relation'] = $operand;
		}

		if (
			! empty( $arguments['exclude-tag'] )
			|| ! empty( $arguments['exclude-category'] )
		) {
			$operand = 'AND';

			// Makes sure tax query exists.
			if ( empty( $repository_args['tax_query'] ) ) {
				$repository_args['tax_query'] = []; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			}

			$items = [
				'exclude-tag'      => 'post_tag',
				'exclude-category' => TEC::TAXONOMY,
			];

			foreach ( $items as $key => $taxonomy ) {
				if ( empty( $arguments[ $key ] ) ) {
					continue;
				}

				$repo = tribe_events();
				$repo->by( 'term_not_in', $taxonomy, $arguments[ $key ] );
				$built_query = $repo->build_query();

				if ( ! empty( $built_query->query_vars['tax_query'] ) ) {
					$repository_args['tax_query'] = Arr::merge_recursive_query_vars( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						$repository_args['tax_query'],
						$built_query->query_vars['tax_query']
					);
				}
			}

			$repository_args['tax_query']['relation'] = $operand;
		}

		if ( isset( $arguments['date'] ) ) {
			// The date can be used in many ways, so we juggle a bit here.
			$date_filters = tribe_events()->get_date_filters();
			$date_keys    = array_filter(
				$repository_args,
				static function ( $key ) use ( $date_filters ) {
					return in_array( $key, $date_filters, true );
				},
				ARRAY_FILTER_USE_KEY
			);

			if ( count( $date_keys ) === 1 ) {
				$date_indices = array_keys( $date_keys );
				$date_index   = reset( $date_indices );
				$date_key     = $date_keys[ $date_index ];

				if ( $date_key === $arguments['date'] ) {
					// Let's only set it if we are sure.
					$repository_args[ $date_index ] = $arguments['date'];
				} else {
					$repository_args[ $date_index ] = $date_key;
				}
			}
		}

		if ( ! empty( $arguments['author'] ) ) {
			if ( ! is_numeric( $arguments['author'] ) ) {
				$author = get_user_by( 'login', $arguments['author'] );
			} else {
				$author = get_user_by( 'id', $arguments['author'] );
			}

			if ( empty( $author->ID ) ) {
				// -1, 0, and strings all prevent excluding posts by author. Using PHP_INT_MAX appropriately causes the filter to function.
				$repository_args['author'] = PHP_INT_MAX;
			} else {
				$repository_args['author'] = $author->ID;
			}
		}

		if ( ! empty( $arguments['organizer'] ) ) {
			if ( ! is_numeric( $arguments['organizer'] ) ) {
				$organizer_id = tribe_organizers()
					->where( 'title', $arguments['organizer'] )
					->per_page( 1 )
					->fields( 'ids' )
					->first();
				if ( empty( $organizer_id ) ) {
					$organizer_id = tribe_organizers()
						->where( 'name', $arguments['organizer'] )
						->per_page( 1 )
						->fields( 'ids' )
						->first();
				}
			} else {
				$organizer_id = $arguments['organizer'];
			}

			if ( empty( $organizer_id ) ) {
				$repository_args['organizer'] = -1;
			} else {
				$repository_args['organizer'] = $organizer_id;
			}
		}

		if ( ! empty( $arguments['venue'] ) ) {
			if ( ! is_numeric( $arguments['venue'] ) ) {
				$venue_id = tribe_venues()
					->where( 'title', $arguments['venue'] )
					->per_page( 1 )
					->fields( 'ids' )
					->first();

				if ( empty( $venue_id ) ) {
					$venue_id = tribe_venues()
						->where( 'name', $arguments['venue'] )
						->per_page( 1 )
						->fields( 'ids' )
						->first();
				}
			} else {
				$venue_id = $arguments['venue'];
			}

			if ( empty( $venue_id ) ) {
				$repository_args['venue'] = -1;
			} else {
				$repository_args['venue'] = $venue_id;
			}
		}

		if ( isset( $arguments['featured'] ) ) {
			$repository_args['featured'] = tribe_is_truthy( $arguments['featured'] );
		}

		if ( isset( $arguments['past'] ) && tribe_is_truthy( $arguments['past'] ) ) {
			$repository_args['past']        = tribe_is_truthy( $arguments['past'] );
			$repository_args['ends_before'] = tribe_end_of_day( current_time( 'mysql' ) );
			// Make sure this isn't set to avoid logic conflicts.
			unset( $repository_args['starts_after'] );
		}

		return $repository_args;
	}

	/**
	 * Alters the context of the view based on the view params stored in the database based on the ID.
	 *
	 * @since 6.11.0
	 *
	 * @param Context $view_context Context for this request.
	 * @param string  $view_slug    Slug of the view we are building.
	 *
	 * @return Context
	 */
	public function filter_view_context( Context $view_context, string $view_slug ): Context {
		$embed_id = $view_context->get( 'embed' );
		if ( ! $embed_id ) {
			return $view_context;
		}

		$arguments = $this->get_database_arguments( $embed_id );

		if ( empty( $arguments ) ) {
			return $view_context;
		}

		if ( false !== stripos( $view_slug, Day_View::get_view_slug() ) ) {
			/* Day view/widget only. */
			$event_date = $view_context->get( 'eventDate' );

			if ( ! empty( $event_date ) ) {
				$arguments['date'] = $event_date;
			}
		} else {
			// Works for month view.
			$arguments['date'] = $view_context->get( 'tribe-bar-date' );
		}

		return $this->alter_context( $view_context, $arguments );
	}

	/**
	 * Filters the default view in the views manager for views navigation.
	 *
	 * @since 6.11.0
	 *
	 * @param string $view_class Fully qualified class name for default view.
	 *
	 * @return string Fully qualified class name for default view of the view in question.
	 */
	public function filter_default_url( string $view_class ): string {
		if ( tribe_context()->doing_php_initial_state() ) {
			return $view_class;
		}

		// Use the global context here as we should be in the context of an AJAX view request.
		$embed_id = tribe_context()->get( 'embed', false );

		if ( false === $embed_id ) {
			// If we're not in the context of an AJAX view request, bail.
			return $view_class;
		}

		$view_args = $this->get_database_arguments( $embed_id );

		if ( ! $view_args['view'] ) {
			return $view_class;
		}

		return tribe( Views_Manager::class )->get_view_class_by_slug( $view_args['view'] );
	}

	/**
	 * Filters the View HTML classes to add some related to PRO features.
	 *
	 * @since 6.11.0
	 *
	 * @param array<string>  $html_classes The current View HTML classes.
	 * @param string         $slug         The View registered slug.
	 * @param View_Interface $view         The View currently rendering.
	 *
	 * @return array<string> The filtered HTML classes.
	 */
	public function filter_view_html_classes( array $html_classes, string $slug, View_Interface $view ): array {
		$context = $view->get_context();

		if ( ! $context instanceof Context ) {
			return $html_classes;
		}

		$embed = $context->get( 'embed', false );

		if ( ! $embed ) {
			return $html_classes;
		}
		$view_args = $this->get_database_arguments( $embed );

		$html_classes[] = 'tribe-events-view--embed';
		$html_classes[] = 'tribe-events-view--embed-' . $embed;

		$container_classes = Arr::get( $view_args, 'container-classes', '' );

		if ( ! empty( $container_classes ) ) {
			$html_classes = array_merge( $html_classes, $container_classes );
		}

		return $html_classes;
	}

	/**
	 * Cleans up an array of values as html classes.
	 *
	 * @since 6.11.0
	 *
	 * @param mixed $value Which classes we are cleaning up.
	 *
	 * @return array Resulting clean html classes.
	 */
	public static function validate_array_html_classes( $value ): array {
		if ( ! is_array( $value ) ) {
			$value = explode( ' ', $value );
		}

		return array_map( 'sanitize_html_class', (array) $value );
	}

	/**
	 * Filters the View data attributes to add some related to PRO features.
	 *
	 * @since 6.11.0
	 *
	 * @param array<string,string> $data The current View data attributes classes.
	 * @param string               $slug The View registered slug.
	 * @param View_Interface       $view The View currently rendering.
	 *
	 * @return array<string,string> The filtered data attributes.
	 */
	public function filter_view_data( array $data, string $slug, View_Interface $view ): array {
		if ( ! $view instanceof View_Interface ) {
			return $data;
		}

		$context = $view->get_context();

		if ( ! $context instanceof Context ) {
			return $data;
		}

		$embed = $context->get( 'embed', false );

		if ( $embed ) {
			$data['embed'] = $embed;
		}

		return $data;
	}

	/**
	 * Filters the View URL to add the embed query arg, if required.
	 *
	 * @since 6.11.0
	 *
	 * @param string         $url       The View current URL.
	 * @param bool           $canonical Whether to return the canonical version of the URL or the normal one.
	 * @param View_Interface $view      This view instance.
	 *
	 * @return string The URL for the view embed.
	 */
	public function filter_view_url( string $url, bool $canonical, View_Interface $view ): string {
		$context = $view->get_context();

		if ( empty( $url ) ) {
			return $url;
		}

		if ( ! $context instanceof Context ) {
			return $url;
		}

		$embed_id = $context->get( 'embed', false );

		if ( false === $embed_id ) {
			return $url;
		}

		return add_query_arg( [ 'embed' => $embed_id ], $url );
	}

	/**
	 * Filters the query arguments array and add the Embeds.
	 *
	 * @since 6.11.0
	 *
	 * @param array          $query     Arguments used to build the URL.
	 * @param string         $view_slug The current view slug.
	 * @param View_Interface $view      The current View object.
	 *
	 * @return  array  Filtered the query arguments for embeds.
	 */
	public function filter_view_url_query_args( array $query, string $view_slug, View_Interface $view ): array {
		$context = $view->get_context();

		if ( ! $context instanceof Context ) {
			return $query;
		}

		$embed = $context->get( 'embed', false );

		if ( false === $embed ) {
			return $query;
		}

		$query['embed'] = $embed;

		return $query;
	}

	/**
	 * Filter the breakpoints for the week view widget based on layout.
	 *
	 * @since 6.11.0
	 *
	 * @param array $breakpoints All breakpoints available.
	 * @param View  $view        The current View instance being rendered.
	 *
	 * @return array Modified array of available breakpoints.
	 */
	public function filter_week_view_breakpoints( array $breakpoints, View $view ): array {
		$context = $view->get_context();
		$widget  = $context->get( 'is-widget', false );
		$embed   = $context->get( 'embed', false );

		if ( false === $widget ) {
			return $breakpoints;
		}

		if ( false === $embed ) {
			return $breakpoints;
		}

		$view_args = $this->get_database_arguments( $embed );
		if ( ! $view_args ) {
			return $breakpoints;
		}

		if ( 'vertical' === $view_args['layout'] ) {
			// Remove all breakpoints to remain in "mobile view".
			return [];
		} elseif ( 'horizontal' === $view_args['layout'] ) {
			// Simplify breakpoints to remain in "desktop view".
			unset( $breakpoints['xsmall'] );
			$breakpoints['medium'] = 0;

			return $breakpoints;
		}

		// Fallback and space for "auto".
		return $breakpoints;
	}

	/**
	 * Modify the Week events per day of a given view based on arguments from View.
	 *
	 * @since 6.11.0
	 *
	 * @param int|string $events_per_day Number of events per day.
	 * @param View       $view           Current view being rendered.
	 *
	 * @return mixed
	 */
	public function filter_week_events_per_day( $events_per_day, View $view ) {
		$context = $view->get_context();
		$embed   = $context->get( 'embed', false );

		if ( false === $embed ) {
			return $events_per_day;
		}

		$view_args = $this->get_database_arguments( $embed );
		if ( ! $view_args || ! isset( $view_args['count'] ) ) {
			return $events_per_day;
		}

		return $view_args['count'];
	}

	/**
	 * Modify the events repository query for the fast-forward link.
	 *
	 * @since 6.11.0
	 *
	 * @param Repository_Interface $next_event Current instance of the events repository class.
	 * @param View_Interface       $view       The View currently rendering.
	 *
	 * @return Repository_Interface $next_event The modified repository instance.
	 */
	public function filter_ff_link_next_event( Repository_Interface $next_event, View_Interface $view ): Repository_Interface {
		$embed = $view->get_context()->get( 'embed' );
		if ( empty( $embed ) ) {
			return $next_event;
		}

		$args = $this->get_database_arguments( $embed );

		if ( ! empty( $args['category'] ) ) {
			$next_event = $next_event->where( 'category', (array) $args['category'] );
		}

		if ( ! empty( $args['tag'] ) ) {
			$next_event = $next_event->where( 'tag', (array) $args['tag'] );
		}

		if ( ! empty( $args['exclude-category'] ) ) {
			$next_event = $next_event->where( 'category_not_in', (array) $args['exclude-category'] );
		}

		if ( ! empty( $args['exclude-tag'] ) ) {
			$next_event = $next_event->where( 'tag__not_in', (array) $args['exclude-tag'] );
		}

		if ( ! empty( $args['author'] ) ) {
			$next_event = $next_event->where( 'author', $args['author'] );
		}

		if ( ! empty( $args['organizer'] ) ) {
			$next_event = $next_event->where( 'organizer', $args['organizer'] );
		}

		if ( ! empty( $args['venue'] ) ) {
			$next_event = $next_event->where( 'venue', $args['venue'] );
		}

		return $next_event;
	}

	/**
	 * Allows the user to specify that they want to skip empty views.
	 *
	 * @since 6.11.0
	 *
	 * @param bool $skip Whether to skip empty views.
	 *
	 * @return bool Whether to skip empty views.
	 */
	public function filter_skip_empty( bool $skip ): bool {
		$arguments = $this->get_arguments();
		if ( ! isset( $arguments['skip-empty'] ) ) {
			return $skip;
		}

		return tribe_is_truthy( $arguments['skip-empty'] );
	}

	/**
	 * Get the arguments for this view.
	 *
	 * @since 6.11.0
	 *
	 * @return array
	 */
	public function get_arguments(): array {
		return $this->arguments;
	}

	/**
	 * Get a specific argument for this view.
	 *
	 * @since 6.11.0
	 *
	 * @param string $index   The index of the argument to get.
	 * @param mixed  $default The default value to return if the argument is not set.
	 *
	 * @return mixed
	 */
	public function get_argument( string $index, $default = null ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound
		return Arr::get( $this->get_arguments(), $index, $default );
	}
}
