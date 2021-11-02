<?php
/**
 * Handles the compatibility with the Events Control extension.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status\Compatibility\Events_Control_Extension
 */

namespace Tribe\Events\Event_Status\Compatibility\Events_Control_Extension;

use Tribe\Extensions\EventsControl\Event_Meta as Event_Control_Meta;
use Tribe\Extensions\EventsControl\Main as Events_Control_Main;
use Tribe\Extensions\EventsControl\Hooks as Events_Control_Extension_Hooks;
use Tribe\Events\Virtual\Plugin as Events_Virtual_Plugin;
use Tribe\Extensions\EventsControl\Metabox;
use WP_Post;

/**
 * Class Service_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status\Compatibility\Events_Control_Extension
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Registers the bindings and filters used to ensure compatibility with the Events Control extension.
	 *
	 * @since TBD
	 */
	public function register() {

		$this->container->singleton( self::class, $this );
		$this->container->singleton( 'events.compatibility.tribe-ext-events-control', $this );

		add_action( 'tribe_plugins_loaded', [ $this, 'handle_actions' ], 20 );
		add_action( 'tribe_plugins_loaded', [ $this, 'handle_filters' ], 20 );
	}

	/**
	 * Un-hooks the extension actions that deal with events with canceled or postponed status.
	 *
	 * @since TBD
	 */
	public function handle_actions() {
		if ( ! class_exists( Events_Control_Main::class ) ) {
			return;
		}

		$extension_hooks = tribe( Events_Control_Extension_Hooks::class );

		// Metabox.
		remove_action(
			'add_meta_boxes',
			[ $extension_hooks, 'action_add_metabox' ],
			10
		);

		// List View.
		remove_action(
			'tribe_template_after_include:events/v2/list/event/venue',
			[ $extension_hooks, 'action_add_online_event' ],
			15,
			3
		);

		// Day View.
		remove_action(
			'tribe_template_after_include:events/v2/day/event/description',
			[ $extension_hooks, 'action_add_online_event' ],
			15,
			3
		);

		remove_action(
			'tribe_template_after_include:events/v2/day/event/venue',
			[ $extension_hooks, 'action_add_online_event' ],
			15,
			3
		);

		// Photo View.
		remove_action(
			'tribe_template_before_include:events-pro/v2/photo/event/date-time',
			[ $extension_hooks, 'action_add_online_event' ],
			15,
			3
		);

		// Map View.
		remove_action(
			'tribe_template_before_include:events-pro/v2/photo/event/date-time',
			[ $extension_hooks, 'action_add_online_event' ],
			15,
			3
		);

		remove_action(
			'tribe_template_after_include:events-pro/v2/map/event-cards/event-card/tooltip/venue',
			[ $extension_hooks, 'action_add_online_event' ],
			15,
			3
		);

		// Week View.
		remove_action(
			'tribe_template_after_include:events-pro/v2/week/mobile-events/day/event/venue',
			[ $extension_hooks, 'action_add_online_event' ],
			15,
			3
		);

		remove_action(
			'tribe_template_after_include:events-pro/v2/week/grid-body/events-day/event/tooltip/description',
			[ $extension_hooks, 'action_add_online_event' ],
			15,
			3
		);
	}

	/**
	 * Handles the filters hooked by the extension by short-circuiting or removing them.
	 *
	 * @since TBD
	 */
	public function handle_filters() {
		if ( ! class_exists( Events_Control_Main::class ) ) {
			return;
		}

		$extension_hooks = tribe( Events_Control_Extension_Hooks::class );

		// Add Marked online option to the new event status metabox.
		add_filter(
			'tribe_template_entry_point:events/admin-views/metabox/event-status:before_container_close',
			[ $this, 'replace_metabox_template' ],
			20,
			3
		);

		// Remove JSON LD modification from extension.
		remove_filter(
			'tribe_json_ld_event_object',
			[ $extension_hooks, 'filter_json_ld_modifiers' ],
			15,
			3
		);

		// Add JSON LD modification if extension is active.
		add_filter(
			'tribe_json_ld_event_object',
			[ $this, 'filter_json_ld_modifiers' ],
			14,
			3
		);

		// List View.
		remove_filter(
			'tribe_template_html:events/v2/list/event/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		// Month View.
		remove_filter(
			'tribe_template_html:events/v2/month/calendar-body/day/calendar-events/calendar-event/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		remove_filter(
			'tribe_template_html:events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		remove_filter(
			'tribe_template_html:events/v2/month/calendar-body/day/multiday-events/multiday-event',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		remove_filter(
			'tribe_template_html:events/v2/month/mobile-events/mobile-day/mobile-event/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		// Day View.
		remove_filter(
			'tribe_template_html:events/v2/day/event/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		// Photo View.
		remove_filter(
			'tribe_template_html:events-pro/v2/photo/event/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		// Map View.
		remove_filter(
			'tribe_template_html:events-pro/v2/map/event-cards/event-card/event/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		remove_filter(
			'tribe_template_html:events-pro/v2/map/event-cards/event-card/tooltip/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		// Week View.
		remove_filter(
			'tribe_template_html:events-pro/v2/week/grid-body/events-day/event/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		remove_filter(
			'tribe_template_html:events-pro/v2/week/grid-body/events-day/event/tooltip/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		remove_filter(
			'tribe_template_html:events-pro/v2/week/grid-body/multiday-events-day/multiday-event',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);

		remove_filter(
			'tribe_template_html:events-pro/v2/week/mobile-events/day/event/title',
			[ $extension_hooks, 'filter_insert_status_label' ],
			15,
			4
		);
	}

	/**
	 * Modifiers to the JSON LD object we use.
	 *
	 * @since TBD
	 *
	 * @param object  $data The JSON-LD object.
	 * @param array   $args The arguments used to get data.
	 * @param WP_Post $post The post object.
	 *
	 * @return object JSON LD object after modifications.
	 */
	public function filter_json_ld_modifiers( $data, $args, $post ) {
		return $this->container->make( JSON_LD::class )->modify_online_event( $data, $args, $post );
	}

	/**
	 * This method adds the marked online option to the event status metabox,
	 * only when the events control extension is active and virtual event is not.
	 * The extension continues to handle the saving and display of the field.
	 *
	 * @since TBD
	 *
	 * @param string               $found_file The template file found for the template name.
	 * @param array<string>|string $name       The name, or name fragments, of the requested template.
	 * @param \Tribe__Template     $template   The template instance that is currently handling the template location
	 */
	public function replace_metabox_template( $found_file, $name, \Tribe__Template $template ) {
		if ( ! class_exists( Events_Control_Main::class ) ) {
			return;
		}

		// Only add mark as an online event if Virtual Events is not found.
		if ( class_exists( Events_Virtual_Plugin::class ) ) {
			return;
		}

		// Setup vars for the mark online compatibility template.
		$event_id = get_the_ID();
		$fields = [
			'online' => tribe_is_truthy( get_post_meta( $event_id, Event_Control_Meta::$key_online, true ) ),
			'online-url' => get_post_meta( $event_id, Event_Control_Meta::$key_online_url, true ),
		];
		$metabox = tribe( Metabox::class );

		$template->template(
			'/metabox/compatibility/events-control-extension/mark-online',
			[
				'fields' => $fields,
				'metabox' => $metabox
			]
		);
	}
}
