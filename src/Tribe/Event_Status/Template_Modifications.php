<?php
namespace Tribe\Events\Event_Status;

use Tribe__Events__Main as Events_Plugin;
use Tribe__Utils__Array as Arr;
use WP_Post;

/**
 * Class Template_Modifications
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status
 */
class Template_Modifications {
	/**
	 * Stores the template class used.
	 *
	 * @since 5.11.0
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * Status Labels.
	 *
	 * @since 5.11.0
	 *
	 * @var Status_Labels
	 */
	protected $status_labels;

	/**
	 * Template Modification constructor.
	 *
	 * @since 5.11.0
	 *
	 * @param Template $template      An instance of the plugin template handler.
	 * @param Status_Labels $status_labels An instance of the statuses handler.
	 */
	public function __construct( Template $template, Status_Labels $status_labels ) {
		$this->template      = $template;
		$this->status_labels = $status_labels;
	}

	/**
	 * Gets the instance of template class set for the metabox.
	 *
	 * @since 5.11.0
	 *
	 * @return Template Instance of the template we are using to render this metabox.
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * Add the control classes for the views v2 elements
	 *
	 * @since 5.11.0
	 *
	 * @param int|WP_Post $event Post ID or post object.
	 *
	 * @return array<string|string> An array of post classes.
	 */
	public function get_post_classes( $event ) {
		$classes = [];
		if ( ! tribe_is_event( $event ) ) {
			return $classes;
		}

		/**
		 * We're specifically forcing here (last param) as otherwise
		 * this runs into issues with the event list table in the admin.
		 */
		$event = tribe_get_event( $event, OBJECT, 'raw', true );

		if ( $event->event_status ) {
			$classes[] = 'tribe-events-status__list-event-' . sanitize_html_class( $event->event_status );
		}

		return $classes;
	}

	/**
	 * Include the event status label and reason to the single page notices.
	 *
	 * @since 5.11.0
	 *
	 * @param string               $notices_html Previously set HTML of notices.
	 * @param array<string|string> $notices      Array of notices added previously.
	 *
	 * @return string  HTML for existing notices if any plus the optional status and reason.
	 */
	public function add_single_status_reason( $notices_html, $notices ) {
		if ( ! is_singular( Events_Plugin::POSTTYPE ) ) {
			return $notices_html;
		}

		$args = [
			'event'         => tribe_get_event( get_the_ID() ),
			'status_labels' => $this->status_labels,
		];

		return $notices_html . $this->template->template( 'single/event-statuses-container', $args, false );
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
	public function insert_status_label( $hook_name, $entry_point_name, $template ) {
		$context = $template->get_values();
		$event   = Arr::get( $context, 'event', null );
		if ( ! $event instanceof WP_Post ) {
			return;
		}

		$args = [
			'event'         => $event,
			'status_labels' => $this->status_labels,
		];

		$this->template->template( 'event-status/status-label', $args );
	}
}
