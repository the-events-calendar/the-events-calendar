<?php
/**
 * The Event Status service provider.
 *
 * @package Tribe\Events\Event_Status
 * @since   TBD
 */

namespace Tribe\Events\Event_Status;

use Tribe__Events__Main as Events_Plugin;
use Tribe__Context as Context;
use WP_Post;

/**
 * Class Event_Status_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status
 */
class Event_Status_Provider extends \tad_DI52_ServiceProvider {
	const DISABLED = 'TEC_EVENT_STATUS_DISABLED';

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		if ( ! self::is_active() ) {
			return false;
		}

		// Register the SP on the container
		$this->container->singleton( 'events.status.provider', $this );

		$this->add_actions();
		$this->add_filters();
		$this->add_templates();
	}

	/**
	 * Returns whether the event status should register, thus activate, or not.
	 *
	 * @since TBD
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
		 * @since TBD
		 *
		 * @param bool $activate Defaults to `true`.
		 */
		return (bool) apply_filters( 'tec_event_status_enabled', true );
	}

	/**
	 * Adds the actions required for event status.
	 *
	 * @since TBD
	 */
	protected function add_actions() {
		add_action( 'init', [ $this, 'on_init' ] );
		add_action( 'add_meta_boxes', [ $this, 'register_metabox' ], 15 );
		add_action( 'save_post_' . Events_Plugin::POSTTYPE, [ $this, 'on_save_post' ], 15, 3 );
	}

	/**
	 * Adds the filters required by the plugin.
	 *
	 * @since TBD
	 */
	protected function add_filters() {
		// Filter event object properties to add the ones related to event status.
		add_filter( 'tribe_get_event', [ $this, 'filter_tribe_get_event' ] );

		// Add the event status locations to the Context.
		add_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ] );

		// Add Event statuses.
		add_filter( 'tribe_events_event_statuses', [ $this, 'filter_event_statuses' ], 10, 2 );

		add_filter( 'post_class', [ $this, 'filter_add_post_class' ], 15, 3 );
	}

	/**
	 * Register the metabox fields in the correct action.
	 *
	 * @since TBD
	 */
	public function on_init() {
		$this->container->make( Classic_Editor::class )->register_fields();
	}

	/**
	 * Renders the metabox template.
	 *
	 * @since TBD
	 *
	 * @param int $post_id  The post ID of the event we are interested in.
	 */
	public function register_metabox( $post_id ) {
		echo $this->container->make( Classic_Editor::class )->register_metabox( $post_id ); /* phpcs:ignore */
	}

	/**
	 * Register the metabox fields in the correct action.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since 1.0.0
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

	public function filter_event_statuses( $statuses, $event ) {
		$statuses = [
			[
				'text'     => _x( 'Scheduled', 'Event status default option', 'the-events-calendar' ),
				'id'       => 'scheduled',
				'value'    => 'scheduled',
				'selected' => 'scheduled' === $event->event_status ? true : false,
			],
			[
				'text'     => _x( 'Canceled', 'Event status of being canceled in the select field', 'the-events-calendar' ),
				'id'       => 'canceled',
				'value'    => 'canceled',
				'selected' => 'canceled' === $event->event_status ? true : false,
			],
			[
				'text'     => _x( 'Postponed', 'Event status of being postponed in the select field', 'the-events-calendar' ),
				'id'       => 'postponed',
				'value'    => 'postponed',
				'selected' => 'postponed' === $event->event_status ? true : false,
			]
		];

		return $statuses;
	}

	/**
	 * Add the control classes for the views v2 elements
	 *
	 * @since TBD
	 *
	 * @param string|string[]  $classes   Space-separated string or array of class names to add to the class list.
	 * @param int|WP_Post      $post      Post ID or post object.
	 *
	 * @return string[]
	 */
	public function filter_add_post_class( $classes, $class, $post ) {
		$new_classes = $this->container->make( Template_Modifications::class )->get_post_classes( $post );
		return array_merge( $classes, $new_classes );
	}

	/**
	 * Adds the templates for event status.
	 *
	 * @since TBD
	 */
	protected function add_templates() {

		// "Classic" Event Single.
/*		add_action(
			'tribe_events_single_event_after_the_content',
			[ $this, 'action_add_event_single_video_embed' ],
			15,
			0
		);*/
		add_filter(
			'tribe_the_notices',
			[ $this, 'filter_include_single_control_markers' ],
			15,
			2
		);

		// List View
		add_filter(
			'tribe_template_html:events/v2/list/event/title',
			[ $this, 'filter_insert_status_label' ],
			15,
			4
		);

	}

	/**
	 * Include the control markers for the single pages.
	 *
	 * @since TBD
	 *
	 * @param  string  $notices_html  Previously set HTML.
	 * @param  array   $notices       Array of notices added previously.
	 *
	 * @return string  Before event html with the new markers.
	 */
	public function filter_include_single_control_markers( $notices_html, $notices ) {
		return $this->container->make( Template_Modifications::class )->add_single_control_markers( $notices_html, $notices );
	}

	/**
	 * Insert the status label for the archive pages.
	 *
	 * @param string   $html      HTML of the template.
	 * @param string   $file      Complete path to include the PHP File.
	 * @param array    $name      Template name.
	 * @param Template $template  Current instance of the Template.
	 *
	 * @return string
	 */
	public function filter_insert_status_label( $html, $file, $name, $template ) {
		return $html;
		return $this->container->make( Template_Modifications::class )->regex_insert_template( 'status-label', $html, $file, $name, $template );
	}
}
