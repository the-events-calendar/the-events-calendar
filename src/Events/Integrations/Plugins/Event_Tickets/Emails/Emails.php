<?php
/**
 * Class Emails.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails;

use TEC\Tickets\Emails\Email_Abstract;
use Tribe\Utils\Lazy_String;

/**
 * Class Emails.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */
class Emails {
	/**
	 * Includes event-related placeholders for emails.
	 *
	 * Given a post ID associated with an event, this function adds
	 * event, venue, and organizer placeholders to the provided placeholders array.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $placeholders The placeholders for the Tickets Emails.
	 * @param string              $email_id     The email ID.
	 * @param Email_Abstract      $email_class  The email class.
	 *
	 * @return array<string,mixed> The filtered placeholders for the Tickets Emails.
	 */
	public function include_placeholders( $placeholders, $email_id, $email_class ) {
		$post_id = $email_class->get( 'post_id' );

		if ( ! tribe_is_event( $post_id ) ) {
			return $placeholders;
		}

		$event = tribe_get_event( $post_id );

		if ( empty( $event ) ) {
			return $placeholders;
		}

		// if the context says that there's a post_id and it's a tribe_event, then add the event placeholders.
		$tec_placeholders = [
			'{event_id}'         => $post_id,
			'{event_date}'       => $event->schedule_details->value(),
			'{event_start_date}' => $event->start_date,
			'{event_end_date}'   => $event->end_date,
			'{event_name}'       => $event->post_title,
			'{event_timezone}'   => $event->timezone,
			'{event_url}'        => $event->permalink,
			'{event_image_url}'  => ! empty( $event->thumbnail->exists ) ? $event->thumbnail->full->url : '',
		];

		// If the event has a venue, add the venue placeholders.
		if ( ! empty( $event->venues->count() ) ) {
			$venue                = $event->venues[0];
			$append_after_address = array_filter( array_map( 'trim', [ $venue->state_province, $venue->state, $venue->province ] ) );
			$event_venue_address  = $venue->address . ( $venue->address && ( $append_after_address || $venue->city ) ? ', ' : '' );
			$event_venue_address .= $append_after_address;

			$tec_placeholders = array_merge(
				$tec_placeholders,
				[
					'{event_venue_id}'       => $venue->ID,
					'{event_venue_name}'     => $venue->post_title,
					'{event_venue_address}'  => $event_venue_address,
					'{event_venue_city}'     => $venue->city,
					'{event_venue_state}'    => $venue->state,
					'{event_venue_province}' => $venue->province,
					'{event_venue_url}'      => $venue->permalink,
				]
			);
		}

		// If the event has an organizer, add the organizer placeholders.
		if ( ! empty( $event->organizers->count() ) ) {
			$organizer       = $event->organizers[0];
			$organizer_url   = tribe_get_organizer_website_url( $organizer->ID );
			$organizer_email = tribe_get_organizer_email( $organizer->ID );

			$tec_placeholders = array_merge(
				$tec_placeholders,
				[
					'{event_organizer_id}'      => $organizer->ID,
					'{event_organizer_name}'    => $organizer->post_title,
					'{event_organizer_url}'     => $organizer->permalink,
					'{event_organizer_email}'   => ! empty( $organizer_email ) ? $organizer_email : '',
					'{event_organizer_website}' => ! empty( $organizer_url ) ? $organizer_url : '',
					'{event_organizer_phone}'   => ! empty( $organizer->phone ) ? $organizer->phone : '',
				]
			);
		}

		return array_merge( $placeholders, $tec_placeholders );
	}

	/**
	 * Includes preview arguments for email templates.
	 *
	 * This function adds preview data to the provided arguments array if the 'is_preview'
	 * flag is set. The preview data simulates an event with its related information.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $args     The email preview arguments.
	 * @param string              $id       The email id.
	 * @param string              $template Template name.
	 * @param Email_Abstract      $email    The email object.
	 *
	 * @return array<string,mixed> The filtered arguments for the Tickets Emails preview.
	 */
	public function include_preview_args( $args, $id, $template, $email ): array {
		if ( empty( $args['is_preview'] ) ) {
			return $args;
		}

		$preview_event = [
			'title'            => esc_html__( 'Arts in the Park', 'the-events-calendar' ),
			'schedule_details' => new Lazy_String(
				static function () {
					return esc_html__( 'September 22 @ 7:00 pm - 11:00 pm', 'the-events-calendar' );
				}
			),
			'dates'            => (object) [],
			'venues'           => [
				(object) [
					'post_title'      => esc_html__( 'Central Park', 'the-events-calendar' ),
					'address'         => esc_html__( '41st Street', 'the-events-calendar' ),
					'city'            => esc_html__( 'New York', 'the-events-calendar' ),
					'state'           => esc_html__( 'NY 10001', 'the-events-calendar' ),
					'country'         => esc_html__( 'United States', 'the-events-calendar' ),
					'phone'           => esc_html__( '(555) 555-5555', 'the-events-calendar' ),
					'website_url'     => esc_url( get_site_url() ),
					'directions_link' => '#',
				],
			],
			'thumbnail'        => (object) [
				'exists'    => true,
				'full'      => (object) [
					'url' => esc_url( plugins_url( '/the-events-calendar/src/resources/images/event-example-image.jpg' ) ),
				],
				'thumbnail' => (object) [
					'alt'   => esc_html__( 'Arts in the Park', 'the-events-calendar' ),
					'title' => esc_html__( 'Arts in the Park', 'the-events-calendar' ),
				],
			],
		];

		$args['event'] = (object) $preview_event;

		return $args;
	}

	/**
	 * Includes event-related template arguments for emails.
	 *
	 * Given a post ID associated with an event, this function adds
	 * the event object to the provided arguments array.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $args     The email preview arguments.
	 * @param string              $id       The email id.
	 * @param string              $template Template name.
	 * @param Email_Abstract      $email    The email object.
	 *
	 * @return array<string,mixed> The filtered arguments for the Tickets Emails .
	 */
	public function include_template_args( $args, $id, $template, $email ): array {
		$post_id = $email->get( 'post_id' );

		if ( ! tribe_is_event( $post_id ) ) {
			return $args;
		}

		$event = tribe_get_event( $post_id );

		if ( empty( $event ) ) {
			return $args;
		}

		$args['event'] = $event;

		return $args;
	}

	/**
	 * Adds event ICS file to email attachments for The Events Calendar Tickets.
	 *
	 * This function generates an ICS file for the event specified by the provided
	 * event ID and adds it to the provided attachments array.
	 *
	 *
	 * @since TBD
	 *
	 * @param array<string,string> $attachments The placeholders for the Tickets Emails.
	 * @param string               $event_id    The event ID.
	 *
	 * @return array<string,string> The filtered attachments.
	 */
	public function add_event_ics_to_attachments( $attachments, $event_id ) {
		$ical        = tribe( 'tec.iCal' );
		$ics_content = $ical->generate_ical_feed( get_post( $event_id ), false );
		$file        = tempnam( sys_get_temp_dir(), 'invite' );

		if ( false === $file ) {
			/** @var \Tribe__Log $logger */
			$logger = tribe( 'logger' );
			$logger->log_error(
				sprintf( "Couldn't generate calendar invite file for Tickets/RSVP email. Event ID: %s", $event_id ),
				'Event Tickets Emails Integration - ICS'
			);
			return $attachments;
		}

		$ics_filname = $file . '.ics';
		file_put_contents( $ics_filname, $ics_content );
		$attachments[] = $ics_filname;
		unlink( $file );

		return $attachments;
	}
}
