<?php
/**
 * Class RSVP.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Email;

use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Emails as TEC_Email_Handler;
use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Template;
use TEC\Tickets\Emails\Dispatcher;
use TEC\Tickets\Emails\Email\RSVP as RSVP_Email;
use TEC\Tickets\Emails\Email_Abstract;
use TEC\Tickets\Emails\Email\Ticket as Tickets_Email_Ticket;
use \Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;

/**
 * Class RSVP.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */
class RSVP {
	/**
	 * The option key for the Event calendar links.
	 *
	 * @see Email_Abstract::get_option_key() for option key format.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static string $option_add_event_links = 'tec-tickets-emails-rsvp-add-event-links';

	/**
	 * The option key for the Event calendar invite.
	 *
	 * @see Email_Abstract::get_option_key() for option key format.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static string $option_add_event_ics = 'tec-tickets-emails-rsvp-add-event-ics';

	/**
	 * Filter the email settings and add TEC specific settings.
	 *
	 * @since TBD
	 *
	 * @param array<array<string,mixed>> $settings The email settings.
	 *
	 * @return array<array<string,mixed>> $settings The modified email settings.
	 */
	public function include_settings( $settings ): array {

		$settings[ static::$option_add_event_links ] = [
			'type'            => 'checkbox_bool',
			'label'           => esc_html__( 'Calendar links', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Include iCal and Google event links in this email.', 'the-events-calendar' ),
			'default'         => true,
			'validation_type' => 'boolean',
		];

		$settings[ static::$option_add_event_ics ] = [
			'type'            => 'checkbox_bool',
			'label'           => esc_html__( 'Calendar invites', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Attach calendar invites (.ics) to the RSVP email.', 'the-events-calendar' ),
			'default'         => true,
			'validation_type' => 'boolean',
		];

		return $settings;
	}

	/**
	 * Filters the attachments for the RSVP Emails and maybe add the calendar ics file.
	 *
	 * @since TBD
	 *
	 * @param array<string,string> $attachments  The attachments for the Tickets Emails.
	 * @param Dispatcher           $dispatcher   The Email dispatcher object.
	 *
	 * @return array<string,string> The filtered attachments for the RSVP Emails.
	 */
	public function include_attachments( $attachments, $dispatcher ) {
		$email_class = $dispatcher->get_email();
		if ( ! $email_class->is_enabled() ) {
			return $attachments;
		}

		$use_ticket_email = tribe_get_option( $email_class->get_option_key( 'use-ticket-email' ), false );

		if ( ! empty( $use_ticket_email ) ) {
			$email_class = tribe( Ticket::class );

			if ( ! $email_class->is_enabled() ) {
				return $attachments;
			}
		}

		if ( ! tribe_is_truthy( tribe_get_option( self::$option_add_event_ics, true ) ) ) {
			return $attachments;
		}

		$post_id = $email_class->get( 'post_id' );

		if ( ! tribe_is_event( $post_id ) ) {
			return $attachments;
		}

		$event = tribe_get_event( $post_id );

		if ( empty( $event ) ) {
			return $attachments;
		}

		$attachments = tribe( TEC_Email_Handler::class )->add_event_ics_to_attachments( $attachments, $post_id );

		return $attachments;
	}

	/**
	 * Maybe include event links.
	 *
	 * @since TBD
	 *
	 * @param \Tribe__Template $parent_template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_links( $parent_template ) {
		$args = $parent_template->get_local_values();
		if ( ! $this->should_show_links( $args ) ) {
			return;
		}

		$args['event_gcal_link'] = tribe( Google_Calendar::class )->generate_single_url( $args['event']->ID );
		$args['event_ical_link'] = tribe_get_single_ical_link( $args['event']->ID );

		if ( ! empty( $args['preview'] ) ) {
			$args['event_gcal_link'] = '#';
			$args['event_ical_link'] = '#';
		}

		tribe( Template::class )->template( 'template-parts/body/event/links', $args, true );
	}

	/**
	 * Maybe include event link styles.
	 *
	 * @since TBD
	 *
	 * @param \Tribe__Template $parent_template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_link_styles( $parent_template ): void {
		tribe( Template::class )->template( 'template-parts/header/head/tec-styles', $parent_template->get_local_values(), true );
	}

	/**
	 * Determines whether RSVP should show calendar links.
	 *
	 * @since TBD
	 *
	 * @param array $args References template context arguments.
	 *
	 * @return bool
	 */
	public function should_show_links( $args ): bool {
		// Double assigned due to needing to reference the original RSVP class later on.
		$rsvp_class = $email_class = tribe( RSVP_Email::class );
		if ( ! $email_class->is_enabled() ) {
			return false;
		}

		$use_ticket_email = tribe_get_option( $email_class->get_option_key( 'use-ticket-email' ), false );
		if ( ! empty( $use_ticket_email ) ) {
			$email_class = tribe( Tickets_Email_Ticket::class );

			if ( ! $email_class->is_enabled() ) {
				return false;
			}
		}
		if (
			! empty( $args['email'] )
			&& $args['email']->get_id() !== $rsvp_class->get_id()
		) {
			return false;
		}

		if ( ! empty( $args['preview'] ) && ! empty( $args['add_event_links'] ) ) {
			return tribe_is_truthy( $args['add_event_links'] );
		}

		if ( ! tribe_is_truthy( tribe_get_option( self::$option_add_event_links, true ) ) ) {
			return false;
		}

		if ( empty( $args['event'] ) ) {
			return false;
		}

		if ( empty( $args['preview'] ) && empty( $args['event'] ) && empty( $args['event']->ID ) ) {
			return false;
		}

		return true;
	}
}
