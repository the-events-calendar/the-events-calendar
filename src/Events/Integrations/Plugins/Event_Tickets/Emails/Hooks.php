<?php
/**
 * Handles hooking all the actions and filters used by Tickets Emails.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Hooks::class ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Hooks::class ), 'some_method' ] );
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails;

use \tad_DI52_ServiceProvider;

/**
 * Class Hooks.
 *
 * @since   TBD
 *
 * @package TEC\Tickets_Plus
 */
class Hooks extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by each Tickets Emails component.
	 *
	 * @since TBD
	 */
	protected function add_actions() {
	}

	/**
	 * Adds the filters required by each Tickets Emails component.
	 *
	 * @since TBD
	 */
	protected function add_filters() {
		// General emails filters.
		add_filter( 'tec_tickets_emails_placeholders', tribe_callback( Emails::class, 'filter_tec_tickets_emails_placeholders' ), 10, 3 );

		// Ticket Email.
		add_filter( 'tec_tickets_emails_ticket_settings', tribe_callback( Email\Ticket::class, 'filter_tec_tickets_emails_ticket_email_settings' ), 10 );
		add_filter( 'tec_tickets_emails_ticket_attachments', tribe_callback( Email\Ticket::class, 'filter_tec_tickets_emails_ticket_email_attachments' ), 10, 3 );

		// RSVP Email.
		add_filter( 'tec_tickets_emails_rsvp_settings', tribe_callback( Email\RSVP::class, 'filter_tec_tickets_emails_rsvp_email_settings' ), 10 );
		add_filter( 'tec_tickets_emails_ticket_attachments', tribe_callback( Email\Ticket::class, 'filter_tec_tickets_emails_rsvp_email_attachments' ), 10, 3 );
	}


}
