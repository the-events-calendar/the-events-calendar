<?php
/**
 * Class RSVP.
 *
 * @since 6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Email;

use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Emails as TEC_Email_Handler;
use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Template;
use TEC\Tickets\Emails\Dispatcher;
use TEC\Tickets\Emails\Email\RSVP as RSVP_Email;
use TEC\Tickets\Emails\Email_Abstract;
use Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;
use \Tribe__Utils__Array as Arr;

/**
 * Class RSVP.
 *
 * @since 6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */
class RSVP {
	/**
	 * The option key for the Event calendar links.
	 *
	 * @see Email_Abstract::get_option_key() for option key format.
	 *
	 * @since 6.1.1
	 *
	 * @var string
	 */
	public static string $option_add_event_links = 'tec-tickets-emails-rsvp-add-event-links';

	/**
	 * The option key for the Event calendar invite.
	 *
	 * @see Email_Abstract::get_option_key() for option key format.
	 *
	 * @since 6.1.1
	 *
	 * @var string
	 */
	public static string $option_add_event_ics = 'tec-tickets-emails-rsvp-add-event-ics';

	/**
	 * Filter the email settings and add TEC specific settings.
	 *
	 * @since 6.1.1
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
			'fieldset_attributes' => [
				'data-depends'                  => '#' . tribe( RSVP_Email::class )->get_option_key( 'use-ticket-email' ),
				'data-condition-is-not-checked' => true,
			],
		];

		$settings[ static::$option_add_event_ics ] = [
			'type'            => 'checkbox_bool',
			'label'           => esc_html__( 'Calendar invites', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Attach calendar invites (.ics) to the RSVP email.', 'the-events-calendar' ),
			'default'         => true,
			'validation_type' => 'boolean',
			'fieldset_attributes' => [
				'data-depends'                  => '#' . tribe( RSVP_Email::class )->get_option_key( 'use-ticket-email' ),
				'data-condition-is-not-checked' => true,
			],
		];

		return $settings;
	}

	/**
	 * Filters the attachments for the RSVP Emails and maybe add the calendar ics file.
	 *
	 * @since 6.1.1
	 *
	 * @param array<string,string> $attachments  The attachments for the Tickets Emails.
	 * @param Dispatcher           $dispatcher   The Email dispatcher object.
	 *
	 * @return array<string,string> The filtered attachments for the RSVP Emails.
	 */
	public function include_attachments( $attachments, $dispatcher ) {
		$email_class = $dispatcher->get_email();

		if ( ! $email_class instanceof RSVP_Email ) {
			return $attachments;
		}

		// If the ticket email settings are being used, don't add these attachments.
		if ( tribe( RSVP_Email::class )->is_using_ticket_email_settings() ) {
			return tribe( Ticket::class )->get_ics_attachments( $attachments, $email_class->get( 'post_id' ) );
		}

		if ( ! tribe_is_truthy( tribe_get_option( self::$option_add_event_ics, true ) ) ) {
			return $attachments;
		}

		$post_id = $email_class->get( 'post_id' );

		if ( ! tribe_is_event( $post_id ) ) {
			return $attachments;
		}

		return tribe( TEC_Email_Handler::class )->add_event_ics_to_attachments( $attachments, $post_id );
	}

	/**
	 * Maybe include event links.
	 *
	 * @since 6.1.1
	 *
	 * @param \Tribe__Template $parent_template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_calendar_links( $parent_template ) {
		if ( ! $this->should_show_calendar_links( $parent_template ) ) {
			return;
		}

		$args = $parent_template->get_local_values();

		if ( ! isset( $args['event'] ) ) {
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
	 * @since 6.1.1
	 *
	 * @param \Tribe__Template $parent_template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_link_styles( $parent_template ): void {
		$args = $parent_template->get_local_values();

		if ( ! $args['email'] instanceof RSVP_Email ) {
			return;
		}

		tribe( Template::class )->template( 'template-parts/header/head/tec-styles', $parent_template->get_local_values(), true );
	}

	/**
	 * Check if the calendar links should be shown.
	 *
	 * @since 6.1.1
	 *
	 * @param \Tribe__Template $et_template Event Tickets template object.
	 *
	 * @return bool
	 */
	public function should_show_calendar_links( $parent_template ): bool {
		$args  = $parent_template->get_local_values();

		if ( ! $args['email'] instanceof RSVP_Email ) {
			return false;
		}

		$is_preview = Arr::get( $args, 'preview', false );
		if ( $is_preview && isset( $args['add_event_links'] ) ) {
			return tribe_is_truthy( $args['add_event_links'] );
		}

		$option_key = self::$option_add_event_links;
		if ( tribe( RSVP_Email::class )->is_using_ticket_email_settings() ) {
			$option_key = Ticket::$option_add_event_links;
		}

		return tribe_is_truthy( tribe_get_option( $option_key, true ) );
	}
}
