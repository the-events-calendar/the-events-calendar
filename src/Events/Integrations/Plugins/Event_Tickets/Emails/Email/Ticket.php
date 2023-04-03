<?php
/**
 * Class Ticket.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Email;

use TEC\Tickets\Emails\Email\Ticket as Ticket_Email;
use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Emails as TEC_Email_Handler;

/**
 * Class Ticket.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */
class Ticket {
	/**
	 * The option key for the Event calendar links.
	 *
	 * @see TEC\Tickets\Emails\Email_Abstract::get_option_key() for option key format.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $option_add_event_links = 'tec-tickets-emails-ticket-add-event-links';

	/**
	 * The option key for the Event calendar invite.
	 *
	 * @see TEC\Tickets\Emails\Email_Abstract::get_option_key() for option key format.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $option_add_event_ics = 'tec-tickets-emails-ticket-add-event-ics';

	/**
	 * Filter the email settings and add TEC specific settings.
	 *
	 * @since TBD
	 *
	 * @param array $settings The email settings.
	 *
	 * @return array $settings The modified email settings.
	 */
	public function filter_tec_tickets_emails_ticket_email_settings( $settings ) {

		$settings[ self::$option_add_event_links ] = [
			'type'            => 'toggle',
			'label'           => esc_html__( 'Include "Add to calendar" links', 'the-events-calendar' ),
			'tooltip'         => esc_html__( "Include links to add the event to the user's calendar.", 'the-events-calendar' ),
			'default'         => true,
			'validation_type' => 'boolean',
		];

		$settings[ self::$option_add_event_ics ] = [
			'type'            => 'toggle',
			'label'           => esc_html__( 'Attach Calendar Invites', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Attach calendar invites (.ics) to the ticket email.', 'the-events-calendar' ),
			'default'         => true,
			'validation_type' => 'boolean',
		];

		return $settings;
	}

	/**
	 * Filters the attachments for the Tickets Emails and maybe add the calendar ics file.
	 *
	 * @since TBD
	 *
	 * @param array          $attachments The placeholders for the Tickets Emails.
	 * @param string         $email_id     The email ID.
	 * @param Email_Abstract $email_class  The email class.
	 *
	 * @return array The filtered attachments for the Tickets Emails.
	 */
	public function filter_tec_tickets_emails_ticket_email_attachments( $attachments, $email_id, $email_class ) {
		if ( ! $email_class->is_enabled() ) {
			return $attachments;
		}

		if ( ! tribe_is_truthy( tribe_get_option( self::$option_add_event_ics, true ) ) ) {
			return $attachments;
		}

		$post_id = $email_class->__get( 'post_id' );

		if ( ! tribe_is_event( $post_id ) ) {
			return $attachments;
		}

		$event = tribe_get_event( $post_id );

		if ( empty( $event ) ) {
			return $attachments;
		}

		$attachments = tribe( TEC_Email_Handler::class )->tec_tickets_emails_add_event_ics_to_attachments( $attachments, $post_id );

		return $attachments;

	}
}
