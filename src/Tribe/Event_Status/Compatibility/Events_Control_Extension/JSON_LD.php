<?php
/**
 * Handles the JSON LD compatibility with the Events Control extension.
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status\Compatibility\Events_Control_Extension
 */

namespace Tribe\Events\Event_Status\Compatibility\Events_Control_Extension;

use Tribe\Events\Virtual\Plugin as Events_Virtual_Plugin;
use Tribe\Extensions\EventsControl\Event_Meta as Event_Control_Meta;
use Tribe\Extensions\EventsControl\Main as Events_Control_Main;
use WP_Post;

/**
 * Class JSON_LD
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status\Compatibility\Events_Control_Extension
 */
class JSON_LD {

	const ONLINE_EVENT_ATTENDANCE_MODE = 'https://schema.org/OnlineEventAttendanceMode';

	/**
	 * Modifiers to the JSON LD event object for online attendance events if the extension is active.
	 *
	 * @since 5.11.0
	 *
	 * @param object  $data The JSON-LD object.
	 * @param array   $args The arguments used to get data.
	 * @param WP_Post $post The post object.
	 *
	 * @return object JSON LD object after modifications.
	 */
	public function modify_online_event( $data, $args, $post ) {
		if ( ! class_exists( Events_Control_Main::class ) ) {
			return $data;
		}

		// Only modify as an online event if Virtual Events is not found.
		if ( class_exists( Events_Virtual_Plugin::class ) ) {
			return $data;
		}

		// Skip any events without proper data.
		if ( empty( $data->startDate ) || empty( $data->endDate ) ) {
			return $data;
		}

		$online = tribe( Event_Control_Meta::class )->is_online( $post->ID );

		/**
		 * Filters if an Event is Considered Online.
		 *
		 * @since 5.11.0
		 *
		 * @param boolean $online If an event is considered online.
		 * @param object  $data   The JSON-LD object.
		 * @param array   $args   The arguments used to get data.
		 * @param WP_Post $post   The post object.
		 */
		$online = apply_filters( 'tec_single_event_online_status', $online, $data, $args, $post );

		// Bail on modifications for non-online events.
		if ( ! $online ) {
			return $data;
		}

		// if online, set the attendance mode
		$data->eventAttendanceMode = static::ONLINE_EVENT_ATTENDANCE_MODE;

		$data->location = (object) [
			'@type' => 'VirtualLocation',
			'url'   => esc_url( $this->get_online_url( $post ) ),
		];

		return $data;
	}

	/**
	 * Get the Online URL for an Event Trying the Online URL, the Website URL, and using the Permalink if nothing found.
	 * A URL is required when using VirtualLocation.
	 *
	 * @since 5.11.0
	 *
	 * @param WP_Post $post The post object to use to get the online url for an event.
	 *
	 * @return mixed The string of the online url for an event if available.
	 */
	protected function get_online_url( $post ) {

		$online_url = tribe( Event_Control_Meta::class )->get_online_url( $post->ID );

		// If Empty Get Website URL.
		if ( empty( $online_url ) ) {
			$online_url = get_post_meta( $post->ID, '_EventURL', true );
		}

		// If Both are Empty Then Get the Permalink.
		if ( empty( $online_url ) ) {
			$online_url = get_the_permalink( $post->ID );
		}

		return $online_url;
	}

}