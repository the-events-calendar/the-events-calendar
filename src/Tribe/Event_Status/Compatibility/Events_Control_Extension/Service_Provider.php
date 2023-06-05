<?php
/**
 * Handles the compatibility with the Events Control extension.
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status\Compatibility\Events_Control_Extension
 */

namespace Tribe\Events\Event_Status\Compatibility\Events_Control_Extension;

use Tribe\Extensions\EventsControl\Event_Meta as Event_Control_Meta;
use Tribe\Extensions\EventsControl\Main as Events_Control_Main;
use Tribe\Extensions\EventsControl\Hooks as Events_Control_Extension_Hooks;
use Tribe\Events\Virtual\Plugin as Events_Virtual_Plugin;
use Tribe\Extensions\EventsControl\Metabox;
use TEC\Common\Contracts\Service_Provider as Provider_Contract;

use WP_Post;

/**
 * Class Service_Provider
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status\Compatibility\Events_Control_Extension
 */
class Service_Provider extends Provider_Contract {


	/**
	 * Registers the bindings and filters used to ensure compatibility with the Events Control extension.
	 *
	 * @since 5.11.0
	 */
	public function register() {

		$this->container->singleton( self::class, $this );
		$this->container->singleton( 'events.compatibility.tribe-ext-events-control', $this );

		add_action( 'tribe_plugins_loaded', [ $this, 'handle_actions' ], 20 );
		add_action( 'tribe_plugins_loaded', [ $this, 'handle_filters' ], 20 );
		add_filter( 'tribe_template_done', [ $this, 'short_circuit_templates' ], 10, 2 );
	}

	/**
	 * Un-hooks the extension actions that deal with events with canceled or postponed status.
	 *
	 * @since 5.11.0
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
	}

	/**
	 * Handles the filters hooked by the extension by short-circuiting or removing them.
	 *
	 * @since 5.11.0
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

		$templates = [
			// List View.
			'events/v2/list/event/title',
			// Month View.
			'events/v2/month/calendar-body/day/calendar-events/calendar-event/title',
			'events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/title',
			'events/v2/month/calendar-body/day/multiday-events/multiday-event',
			'events/v2/month/mobile-events/mobile-day/mobile-event/title',
			// Day View.
			'events/v2/day/event/title',
			// Photo View.
			'events-pro/v2/photo/event/title',
			// Map View.
			'events-pro/v2/map/event-cards/event-card/event/title',
			'events-pro/v2/map/event-cards/event-card/tooltip/title',
			// Week View.
			'events-pro/v2/week/grid-body/events-day/event/title',
			'events-pro/v2/week/grid-body/events-day/event/tooltip/title',
			'events-pro/v2/week/grid-body/multiday-events-day/multiday-event',
			'events-pro/v2/week/mobile-events/day/event/title',
		];

		/**
		 * Filters the list of templates to remove from event status control extension by filter.
		 *
		 * @since 5.11.0
		 *
		 * @param array<string> $label_templates The array of template names for each view to add the status label.
		 */
		$templates = apply_filters( 'tec_event_status_compatibility_remove_extension_templates_by_filter', $templates );

		foreach ( $templates as $template ) {
		    if ( ! is_string( $template ) ) {
	            continue;
	        }

			remove_filter(
				'tribe_template_html:' . $template,
				[ $extension_hooks, 'filter_insert_status_label' ],
				15,
				4
			);
		}
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
		return $this->container->make( JSON_LD::class )->modify_online_event( $data, $args, $post );
	}

	/**
	 * Short-circuits the templates the extension would load for event status.
	 *
	 * @since 5.11.0
	 *
	 * @param bool|null    $done A flag to indicate whether the template request has been handled or not.
	 * @param string|array $name The name, or name fragments, of the requested template.
	 *
	 * @return bool|null Either the original `$done` value if the template is not one of the target ones, or `true` if
	 *                   the template is one of the target ones and should not be printed.
	 */
	public function short_circuit_templates( $done, $name ) {
		$targets = [
			'single/canceled-status',
			'single/postponed-status',
		];

		return in_array( $name, $targets, true ) ? true : $done;
	}

	/**
	 * This method adds the marked online option to the event status metabox,
	 * only when the events control extension is active and virtual event is not.
	 * The extension continues to handle the saving and display of the field.
	 *
	 * @since 5.11.0
	 *
	 * @param string               $found_file The template file found for the template name.
	 * @param array<string>|string $name       The name, or name fragments, of the requested template.
	 * @param \Tribe__Template     $template   The template instance that is currently handling the template location
	 *
	 * @return string An empty string or the HTML of the mark online template.
	 */
	public function replace_metabox_template( $found_file, $name, \Tribe__Template $template ) {
		if ( ! class_exists( Events_Control_Main::class ) ) {
			return '';
		}

		// Only add mark as an online event if Virtual Events is not found.
		if ( class_exists( Events_Virtual_Plugin::class ) ) {
			return '';
		}

		// Setup vars for the mark online compatibility template.
		$event_id = get_the_ID();
		$fields = [
			'online' => tribe_is_truthy( get_post_meta( $event_id, Event_Control_Meta::$key_online, true ) ),
			'online-url' => get_post_meta( $event_id, Event_Control_Meta::$key_online_url, true ),
		];
		$metabox = tribe( Metabox::class );

		return $template->template(
			'/metabox/compatibility/events-control-extension/mark-online',
			[
				'fields' => $fields,
				'metabox' => $metabox
			]
		);
	}
}
