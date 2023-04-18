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
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails;

/**
 * Class Hooks.
 *
 * @since TBD
 *
 * @package TEC\Tickets_Plus
 */
class Hooks extends \tad_DI52_ServiceProvider {

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
		add_action( 'tribe_template_before_include:tickets/v2/emails/template-parts/body/tickets', [ $this, 'include_event_date_ticket_rsvp_emails' ], 10, 3 );
		add_action( 'tribe_template_before_include:tickets/v2/emails/template-parts/body/tickets', [ $this, 'include_event_title_ticket_rsvp_emails' ], 10, 3 );
		add_action( 'tribe_template_before_include:tickets/v2/emails/template-parts/body/tickets', [ $this, 'include_event_image_ticket_rsvp_emails' ], 10, 3 );
		add_action( 'tribe_template_after_include:tickets/v2/emails/template-parts/body/tickets', [ $this, 'include_event_venue_ticket_rsvp_emails' ], 10, 3 );
		add_action( 'tribe_template_after_include:tickets/v2/emails/template-parts/body/tickets', [ $this, 'maybe_include_event_links_ticket_rsvp_emails' ], 10, 3 );
	}

	/**
	 * Adds the filters required by each Tickets Emails component.
	 *
	 * @since TBD
	 */
	protected function add_filters() {
		// General emails filters.
		add_filter( 'tec_tickets_emails_placeholders', tribe_callback( Emails::class, 'filter_tec_tickets_emails_placeholders' ), 10, 3 );
		add_filter( 'tec_tickets_emails_preview_args', tribe_callback( Emails::class, 'filter_tec_tickets_emails_preview_args' ), 10, 4 );
		add_filter( 'tec_tickets_emails_template_args', tribe_callback( Emails::class, 'filter_tec_tickets_emails_template_args' ), 10, 4 );

		// Ticket Email.
		add_filter( 'tec_tickets_emails_ticket_settings', tribe_callback( Email\Ticket::class, 'filter_tec_tickets_emails_ticket_email_settings' ), 10 );
		add_filter( 'tec_tickets_emails_ticket_attachments', tribe_callback( Email\Ticket::class, 'filter_tec_tickets_emails_ticket_email_attachments' ), 10, 3 );

		// RSVP Email.
		add_filter( 'tec_tickets_emails_rsvp_settings', tribe_callback( Email\RSVP::class, 'filter_tec_tickets_emails_rsvp_email_settings' ), 10 );
		add_filter( 'tec_tickets_emails_rsvp_attachments', tribe_callback( Email\RSVP::class, 'filter_tec_tickets_emails_rsvp_email_attachments' ), 10, 3 );
	}

	/**
	 * Include the Event date in the ticket and RSVP emails.
	 *
	 * @since TBD
	 *
	 * @param string           $file        Template file.
	 * @param string           $name        Template name.
	 * @param \Tribe__Template $et_template Event Tickets template object.
	 * @return void
	 */
	public function include_event_date_ticket_rsvp_emails( $file, $name, $et_template ) {
		if ( ! $et_template instanceof \Tribe__Template ) {
			return;
		}

		tribe( Template::class )->template( 'template-parts/body/event/date', $et_template->get_local_values(), true );
	}

	/**
	 * Include the Event title and description in the ticket and RSVP emails.
	 *
	 * @since TBD
	 *
	 * @param string           $file        Template file.
	 * @param string           $name        Template name.
	 * @param \Tribe__Template $et_template Event Tickets template object.
	 * @return void
	 */
	public function include_event_title_ticket_rsvp_emails( $file, $name, $et_template ) {
		if ( ! $et_template instanceof \Tribe__Template ) {
			return;
		}

		$args = $et_template->get_local_values();

		$template = tribe( Template::class );

		$template->template( 'template-parts/body/event/title', $args, true );

		$template->template( 'template-parts/body/event/description', $args, true );
	}

	/**
	 * Include the Event image in the ticket and RSVP emails.
	 *
	 * @since TBD
	 *
	 * @param string           $file        Template file.
	 * @param string           $name        Template name.
	 * @param \Tribe__Template $et_template Event Tickets template object.
	 * @return void
	 */
	public function include_event_image_ticket_rsvp_emails( $file, $name, $et_template ) {
		if ( ! $et_template instanceof \Tribe__Template ) {
			return;
		}

		tribe( Template::class )->template( 'template-parts/body/event/image', $et_template->get_local_values(), true );
	}

	/**
	 * Include the Event venue in the ticket and RSVP emails.
	 *
	 * @since TBD
	 *
	 * @param string           $file        Template file.
	 * @param string           $name        Template name.
	 * @param \Tribe__Template $et_template Event Tickets template object.
	 * @return void
	 */
	public function include_event_venue_ticket_rsvp_emails( $file, $name, $et_template ) {
		if ( ! $et_template instanceof \Tribe__Template ) {
			return;
		}

		tribe( Template::class )->template( 'template-parts/body/event/venue', $et_template->get_local_values(), true );
	}

	/**
	 * Maybe include the Event links in the ticket and RSVP emails.
	 *
	 * @since TBD
	 *
	 * @param string           $file        Template file.
	 * @param string           $name        Template name.
	 * @param \Tribe__Template $et_template Event Tickets template object.
	 * @return void
	 */
	public function maybe_include_event_links_ticket_rsvp_emails( $file, $name, $et_template ) {
		if ( ! $et_template instanceof \Tribe__Template ) {
			return;
		}

		$this->container->make( Email\RSVP::class )->maybe_include_event_links( $et_template );
		$this->container->make( Email\Ticket::class )->maybe_include_event_links( $et_template );
	}
}
