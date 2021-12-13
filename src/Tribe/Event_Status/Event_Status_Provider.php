<?php
/**
 * The Event Status service provider.
 *
 * @package Tribe\Events\Event_Status
 * @since   5.11.0
 */

namespace Tribe\Events\Event_Status;

use Tribe\Extensions\EventsControl\Main as Events_Control_Main;
use Tribe\Events\Event_Status\Compatibility\Filter_Bar\Detect;
use Tribe__Events__Main as Events_Plugin;
use Tribe__Context as Context;
use WP_Post;

/**
 * Class Event_Status_Provider
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status
 */
class Event_Status_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * The constant to disable the event status coding.
	 *
	 * @since 5.11.0
	 */
	const DISABLED = 'TEC_EVENT_STATUS_DISABLED';

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.11.0
	 */
	public function register() {
		if ( ! self::is_active() ) {
			return;
		}

		// Register the SP on the container
		$this->container->singleton( 'events.status.provider', $this );

		$this->add_actions();
		$this->add_filters();
		$this->add_templates();
		$this->handle_compatibility();
	}

	/**
	 * Returns whether the event status should register, thus activate, or not.
	 *
	 * @since 5.11.0
	 *
	 * @return bool Whether the event status should register or not.
	 */
	public static function is_active() {
		if ( defined( self::DISABLED ) && constant( self::DISABLED ) ) {
			// The disable constant is defined and it's truthy.
			return false;
		}

		if ( getenv( self::DISABLED ) ) {
			// The disable env var is defined and it's truthy.
			return false;
		}

		/**
		 * Allows filtering whether the event status should be activated or not.
		 *
		 * Note: this filter will only apply if the disable constant or env var
		 * are not set or are set to falsy values.
		 *
		 * @since 5.11.0
		 *
		 * @param bool $activate Defaults to `true`.
		 */
		return (bool) apply_filters( 'tec_event_status_enabled', true );
	}

	/**
	 * Adds the actions required for event status.
	 *
	 * @since 5.11.0
	 */
	protected function add_actions() {
		add_action( 'init', [ $this, 'on_init' ] );
		add_action( 'add_meta_boxes', [ $this, 'register_metabox' ], 15 );
		add_action( 'save_post_' . Events_Plugin::POSTTYPE, [ $this, 'on_save_post' ], 15, 3 );
	}

	/**
	 * Adds the filters required by the plugin.
	 *
	 * @since 5.11.0
	 */
	protected function add_filters() {
		// Filter event object properties to add the ones related to event status.
		add_filter( 'tribe_get_event', [ $this, 'filter_tribe_get_event' ] );

		// Add the event status locations to the Context.
		add_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ] );

		// Add Event statuses.
		add_filter( 'tec_event_statuses', [ $this, 'filter_event_statuses' ], 10, 2 );

		add_filter( 'post_class', [ $this, 'filter_add_post_class' ], 15, 3 );
		add_filter( 'tribe_json_ld_event_object', [ $this, 'filter_json_ld_modifiers' ], 10, 3 );
	}

	/**
	 * Handle compatibility with external plugins and extensions for event status.
	 *
	 * @since 5.12.1
	 *
	 */
	protected function handle_compatibility() {
		add_action( 'tribe_common_loaded', [ $this, 'handle_events_control_extension' ], 99 );
		add_action( 'tribe_common_loaded', [ $this, 'handle_filter_bar' ], 99 );
	}

	/**
	 * Register the metabox fields in the correct action.
	 *
	 * @since 5.11.0
	 */
	public function on_init() {
		$this->container->make( Classic_Editor::class )->register_fields();
	}

	/**
	 * Renders the metabox template.
	 *
	 * @since 5.11.0
	 *
	 * @param int $post_id  The post ID of the event we are interested in.
	 */
	public function register_metabox( $post_id ) {
		echo $this->container->make( Classic_Editor::class )->register_metabox( $post_id ); /* phpcs:ignore */
	}

	/**
	 * Register the metabox fields in the correct action.
	 *
	 * @since 5.11.0
	 *
	 * @param int     $post_id Which post ID we are dealing with when saving.
	 * @param WP_Post $post    WP Post instance we are saving.
	 * @param boolean $update  If we are updating the post or not.
	 */
	public function on_save_post( $post_id, $post, $update ) {
		$this->container->make( Classic_Editor::class )->save( $post_id, $post, $update );
	}

	/**
	 * Filters the object returned by the `tribe_get_event` function to add to it properties related to event status.
	 *
	 * @since 5.11.0
	 *
	 * @param WP_Post $post The events post object to be modified.
	 *
	 * @return \WP_Post The original event object decorated with properties related to event status.
	 */
	public function filter_tribe_get_event( $post ) {
		if ( ! $post instanceof WP_Post ) {
			// We should only act on event posts, else bail.
			return $post;
		}


		return $this->container->make( Models\Event::class )->add_properties( $post );
	}

	/**
	 * Add, to the Context, the locations used by the plugin.
	 *
	 * @since 5.11.0
	 *
	 * @param array<string,array> $context_locations The current Context locations.
	 *
	 * @return array<string,array> The updated Context locations.
	 */
	public function filter_context_locations( array $context_locations ) {
		$context_locations['events_status_data'] = [
			'read' => [
				Context::REQUEST_VAR => [ Classic_Editor::$id ],
			],
		];

		return $context_locations;
	}

	/**
	 * Add the event statuses to select for an event.
	 *
	 * @since 5.11.0
	 *
	 * @param array<string|mixed> $statuses       The event status options for an event.
	 * @param string              $current_status The current event status for the event or empty string if none.
	 *
	 * @return array<string|mixed> The event status options for an event.
	 */
	public function filter_event_statuses( $statuses, $current_status ) {
		return $this->container->make( Status_Labels::class )->filter_event_statuses( $statuses, $current_status );
	}

	/**
	 * Add the status classes for the views v2 elements
	 *
	 * @since 5.11.0
	 *
	 * @param array<string|string> $classes Space-separated string or array of class names to add to the class list.
	 * @param int|WP_Post          $post    Post ID or post object.
	 *
	 * @return array<string|string> An array of post classes with the status added.
	 */
	public function filter_add_post_class( $classes, $class, $post ) {
		$new_classes = $this->container->make( Template_Modifications::class )->get_post_classes( $post );

		return array_merge( $classes, $new_classes );
	}

	/**
	 * Modifiers to the JSON LD object we use.
	 *
	 * @since 5.11.0
	 *
	 * @param object  $data The JSON-LD object.
	 * @param array   $args The arguments used to get data.
	 * @param WP_Post $post The post object.
	 *
	 * @return object JSON LD object after modifications.
	 */
	public function filter_json_ld_modifiers( $data, $args, $post ) {
		return $this->container->make( JSON_LD::class )->modify_event( $data, $args, $post );
	}

	/**
	 * Adds the templates for event status.
	 *
	 * @since 5.11.0
	 */
	protected function add_templates() {

		// "Classic" Event Single.
		add_filter(
			'tribe_the_notices',
			[ $this, 'filter_include_single_status_reason' ],
			15,
			2
		);

		$label_templates = [
			// List View.
			'events/v2/list/event/title:after_container_open',
			// Month View.
			'events/v2/month/calendar-body/day/calendar-events/calendar-event/title:after_container_open',
			'events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/title:after_container_open',
			'events/v2/month/mobile-events/mobile-day/mobile-event/title:after_container_open',
			'events/v2/month/calendar-body/day/multiday-events/multiday-event/bar/title:after_container_open',
			'events/v2/month/calendar-body/day/multiday-events/multiday-event/hidden/link/title:after_container_open',
			// Day View.
			'events/v2/day/event/title:after_container_open',
			// Latest Past Events View.
			'events/v2/latest-past/event/title:after_container_open',
			// List Widget.
			'events/v2/widgets/widget-events-list/event/title:after_container_open',
		];

		/**
		 * Filters the list of template where the event status label is added.
		 *
		 * @since 5.11.0
		 *
		 * @param array<string> $label_templates The array of template names for each view to add the status label.
		 */
		$label_templates = apply_filters( 'tec_event_status_templates', $label_templates );

		foreach ( $label_templates as $template ) {
		    if ( ! is_string( $template ) ) {
	            continue;
	        }

			add_filter(
				'tribe_template_entry_point:' . $template,
				[ $this, 'filter_insert_status_label' ],
				15,
				3
			);
		}
	}

	/**
	 * Include the status reason for the single pages.
	 *
	 * @since 5.11.0
	 *
	 * @param string $notices_html Previously set HTML.
	 * @param array  $notices      Array of notices added previously.
	 *
	 * @return string  Before event html with the status reason.
	 */
	public function filter_include_single_status_reason( $notices_html, $notices ) {
		return $this->container->make( Template_Modifications::class )->add_single_status_reason( $notices_html, $notices );
	}

	/**
	 * Inserts Status Label.
	 *
	 * @since 5.11.0
	 *
	 * @param string   $hook_name        For which template include this entry point belongs.
	 * @param string   $entry_point_name Which entry point specifically we are triggering.
	 * @param Template $template         Current instance of the Template.
	 */
	public function filter_insert_status_label( $hook_name, $entry_point_name, $template ) {
		return $this->container->make( Template_Modifications::class )->insert_status_label( $hook_name, $entry_point_name, $template );
	}

	/**
	 * Handles the compatibility with the "The Events Calendar Extension: Events Control" plugin.
	 *
	 * @since 5.12.1
	 */
	public function handle_events_control_extension() {
		if ( ! class_exists( Events_Control_Main::class ) ) {
			return;
		}

		$this->container->register( Compatibility\Events_Control_Extension\Service_Provider::class );
	}

	/**
	 * Handles the compatibility with the Filter Bar plugin.
	 *
	 * @since 5.12.1
	 */
	public function handle_filter_bar() {
		if ( ! tribe( Detect::class )::is_active() ) {
			return;
		}

		$this->container->register( Compatibility\Filter_Bar\Service_Provider::class );
	}
}
