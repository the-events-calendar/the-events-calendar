<?php
/**
 * Class Emails.
 *
 * @since   6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails;

use TEC\Tickets\Emails\Email_Abstract;
use Tribe\Utils\Lazy_String;
use Tribe__Events__Main;

/**
 * Class Emails.
 *
 * @since   6.1.1
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
	 * @since 6.1.1
	 * @since 6.2.2 Refactored method to move placeholder, venue, and organization logic out.
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

		$tec_placeholders = array_merge(
			$placeholders,
			$this->get_event_placeholders( $event ),
			$this->get_venue_placeholders( $event ),
			$this->get_organizer_placeholders( $event )
		);

		return array_merge( $placeholders, $tec_placeholders );
	}

	/**
	 * Includes preview arguments for email templates.
	 *
	 * This function adds preview data to the provided arguments array if the 'is_preview'
	 * flag is set. The preview data simulates an event with its related information.
	 *
	 * @since 6.1.1
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
			'ID'               => 213123123,
			'permalink'        => '#',
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
					'url' => esc_url( tribe_resource_url( 'images/event-example-image.jpg', false, null, Tribe__Events__Main::instance() ) ),
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
	 * @since 6.1.1
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
	 * @since 6.1.1
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

	/**
	 * Retrieves event-related placeholders.
	 *
	 * @since 6.2.2
	 *
	 * @param object $event The event object.
	 *
	 * @return array<string, mixed> An associative array of event-related placeholders.
	 */
	public function get_event_placeholders( $event ): array {
		$datetime_with_year_format = tribe_get_datetime_format( true );

		// if the context says that there's a post_id and it's a tribe_event, then add the event placeholders.
		$placeholders = [
			'{event_id}'         => $event->ID,
			'{event_date}'       => wp_kses( $event->schedule_details->value(), [] ),
			'{event_start_date}' => wp_kses( $event->dates->start->format( $datetime_with_year_format ), [] ),
			'{event_end_date}'   => wp_kses( $event->dates->end->format( $datetime_with_year_format ), [] ),
			'{event_name}'       => wp_kses( $event->post_title, [] ),
			'{event_timezone}'   => $event->timezone,
			'{event_url}'        => $event->permalink,
			'{event_image_url}'  => ! empty( $event->thumbnail->exists ) ? $event->thumbnail->full->url : '',
		];
		return $placeholders;
	}

	/**
	 * Retrieves venue-related placeholders if the event has a venue.
	 *
	 * @since 6.2.2
	 *
	 * @param object $event The event object.
	 *
	 * @return array<string, mixed> An associative array of venue-related placeholders.
	 */
	public function get_venue_placeholders( $event ): array {
		$placeholders = [];
		// If the event has a venue, add the venue placeholders.
		if ( ! empty( $event->venues->count() ) ) {
			$venue = $event->venues[0];

			$state_or_province = $venue->state;
			if ( $venue->country !== 'US' ) {
				$state_or_province = $venue->province;
			}
			if ( empty( $state_or_province ) ) {
				$state_or_province = $venue->state_province;
			}

			$placeholders = [
				'{event_venue_id}'                => $venue->ID,
				'{event_venue_name}'              => wp_kses( $venue->post_title, [] ),
				'{event_venue_street_address}'    => $venue->address,
				'{event_venue_city}'              => $venue->city,
				'{event_venue_state_or_province}' => $state_or_province,
				'{event_venue_province}'          => $venue->province,
				'{event_venue_state}'             => $venue->state,
				'{event_venue_zip}'               => $venue->zip,
				'{event_venue_url}'               => $venue->permalink,
			];
		}
		return $placeholders;
	}

	/**
	 * Retrieves organizer-related placeholders if the event has an organizer.
	 *
	 * @since 6.2.2
	 *
	 * @param object $event The event object.
	 *
	 * @return array<string, mixed> An associative array of organizer-related placeholders.
	 */
	public function get_organizer_placeholders( $event ): array {
		$placeholders = [];

		$placeholders['{event_organizers_count}'] = $event->organizers->count();
		$placeholders['{event_organizers_names}'] = !empty($event->organizer_names) ? implode(', ', $event->organizer_names->all()) : '';

		// If the event has an organizer, add the organizer placeholders.
		if ( ! empty( $event->organizers->count() ) ) {
			$organizer_placeholders = [];

			foreach ( $event->organizers as $index => $organizer ) {
				$organizer_id         = $organizer->ID;
				$organizer_post_title = wp_kses( $organizer->post_title, [] );
				$organizer_permalink  = $organizer->permalink;
				$organizer_url        = tribe_get_organizer_website_url( $organizer->ID );
				$organizer_email      = tribe_get_organizer_email( $organizer->ID );
				$organizer_phone      = $organizer->phone;

				$organizer_placeholders[] = [
					"{event_organizer:{$index}:id}"      => $organizer_id,
					"{event_organizer:{$index}:name}"    => $organizer_post_title,
					"{event_organizer:{$index}:url}"     => $organizer_permalink,
					"{event_organizer:{$index}:email}"   => $organizer_email,
					"{event_organizer:{$index}:website}" => $organizer_url,
					"{event_organizer:{$index}:phone}"   => $organizer_phone,
				];

				if ( $index === 0 ) {
					$organizer_placeholders[] = [
						'{event_organizer_id}'      => $organizer_id,
						'{event_organizer_name}'    => $organizer_post_title,
						'{event_organizer_url}'     => $organizer_permalink,
						'{event_organizer_email}'   => $organizer_email,
						'{event_organizer_website}' => $organizer_url,
						'{event_organizer_phone}'   => $organizer_phone,
					];
				}
			}
			$placeholders = array_merge( $placeholders, ...$organizer_placeholders );

		}
		return $placeholders;
	}

}
