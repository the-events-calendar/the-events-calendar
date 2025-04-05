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
 * @since   6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails;

use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Email\RSVP;
use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Email\Ticket;
use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\JSON_LD\Event_Data;
use TEC\Tickets\Emails\Dispatcher;
use TEC\Tickets\Emails\Email_Abstract;
use \Tribe__Template as Common_Template;

/**
 * Class Hooks.
 *
 * @since   6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */
class Hooks extends \TEC\Common\Contracts\Service_Provider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.1.1
	 */
	public function register(): void {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by each Tickets Emails component.
	 *
	 * @since 6.1.1
	 */
	protected function add_actions(): void {
		add_action( 'tribe_template_before_include:tickets/emails/template-parts/body/post-title', [ $this, 'include_event_date_ticket_rsvp_emails' ], 10, 3 );
		add_action( 'tribe_template_before_include:tickets/emails/template-parts/body/tickets', [ $this, 'include_event_image_ticket_rsvp_emails' ], 15, 3 );
		add_action( 'tribe_template_before_include:tickets/emails/template-parts/header/head/styles', [ $this, 'include_event_ticket_rsvp_styles' ], 10, 3 );
		add_action( 'tribe_template_after_include:tickets/emails/template-parts/body/post-description', [ $this, 'include_event_venue_ticket_rsvp_emails' ], 15, 3 );
		add_action( 'tribe_template_after_include:tickets/emails/template-parts/body/post-description', [ $this, 'include_event_calendar_links' ], 20, 3 );
	}

	/**
	 * Adds the filters required by each Tickets Emails component.
	 *
	 * @since 6.1.1
	 */
	protected function add_filters(): void {
		// General emails filters.
		add_filter( 'tec_tickets_emails_placeholders', [ $this, 'filter_include_emails_placeholders' ], 10, 3 );
		add_filter( 'tec_tickets_emails_preview_args', [ $this, 'filter_include_emails_preview_args' ], 10, 4 );
		add_filter( 'tec_tickets_emails_template_args', [ $this, 'filter_include_emails_template_args' ], 10, 4 );

		// Ticket Email.
		add_filter( 'tec_tickets_emails_ticket_settings', [ $this, 'filter_include_ticket_email_settings' ], 10 );
		add_filter( 'tec_tickets_emails_dispatcher_ticket_attachments', [ $this, 'filter_include_ticket_email_attachments' ], 10, 2 );

		// RSVP Email.
		add_filter( 'tec_tickets_emails_rsvp_settings', [ $this, 'filter_include_rsvp_email_settings' ], 10 );
		add_filter( 'tec_tickets_emails_dispatcher_rsvp_attachments', [ $this, 'filter_include_rsvp_email_attachments' ], 10, 2 );
		add_filter( 'tec_tickets_email_json_ld_event_schema_data', [ $this, 'filter_include_json_ld_event_data' ], 10, 2 );
	}

	/**
	 * Filters the placeholders for the email templates.
	 *
	 * @since 6.1.1
	 *
	 * @param array          $placeholders The existing placeholders.
	 * @param string         $email_id     The email identifier.
	 * @param Email_Abstract $email_class  The email class instance.
	 *
	 * @return array The modified placeholders.
	 */
	public function filter_include_emails_placeholders( $placeholders, $email_id, $email_class ): array {
		return $this->container->make( Emails::class )->include_placeholders( $placeholders, $email_id, $email_class );
	}

	/**
	 * Filters the template arguments for the email templates.
	 *
	 * @since 6.1.1
	 *
	 * @param array          $args     The existing template arguments.
	 * @param string         $id       The email identifier.
	 * @param string         $template The email template.
	 * @param Email_Abstract $email    The email class instance.
	 *
	 * @return array The modified template arguments.
	 */
	public function filter_include_emails_template_args( $args, $id, $template, $email ): array {
		return $this->container->make( Emails::class )->include_template_args( $args, $id, $template, $email );
	}

	/**
	 * Filters the preview arguments for the email templates.
	 *
	 * @since 6.1.1
	 *
	 * @param array          $args     The existing preview arguments.
	 * @param string         $id       The email identifier.
	 * @param string         $template The email template.
	 * @param Email_Abstract $email    The email class instance.
	 *
	 * @return array The modified preview arguments.
	 */
	public function filter_include_emails_preview_args( $args, $id, $template, $email ): array {
		return $this->container->make( Emails::class )->include_preview_args( $args, $id, $template, $email );
	}

	/**
	 * Filters the RSVP email settings.
	 *
	 * @since 6.1.1
	 *
	 * @param array $settings The existing RSVP email settings.
	 *
	 * @return array The modified RSVP email settings.
	 */
	public function filter_include_rsvp_email_settings( $settings ): array {
		return $this->container->make( RSVP::class )->include_settings( $settings );
	}

	/**
	 * Filters the ticket email settings.
	 *
	 * @since 6.1.1
	 *
	 * @param array $settings The existing ticket email settings.
	 *
	 * @return array The modified ticket email settings.
	 */
	public function filter_include_ticket_email_settings( $settings ): array {
		return $this->container->make( Ticket::class )->include_settings( $settings );
	}

	/**
	 * Filters the RSVP email attachments.
	 *
	 * @since 6.1.1
	 *
	 * @param array      $attachments The existing RSVP email attachments.
	 * @param Dispatcher $dispatcher  The email class instance.
	 *
	 * @return array The modified RSVP email attachments.
	 */
	public function filter_include_rsvp_email_attachments( $attachments, $dispatcher ): array {
		return $this->container->make( RSVP::class )->include_attachments( $attachments, $dispatcher );
	}

	/**
	 * Filters the ticket email attachments.
	 *
	 * @since 6.1.1
	 *
	 * @param array      $attachments The existing ticket email attachments.
	 * @param Dispatcher $dispatcher  The email class instance.
	 *
	 * @return array The modified ticket email attachments.
	 */
	public function filter_include_ticket_email_attachments( $attachments, $dispatcher ): array {
		return $this->container->make( Ticket::class )->include_attachments( $attachments, $dispatcher );
	}

	/**
	 * Include the Event date in the ticket and RSVP emails.
	 *
	 * @since 6.1.1
	 *
	 * @param string          $file     Template file.
	 * @param string          $name     Template name.
	 * @param Common_Template $template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_date_ticket_rsvp_emails( $file, $name, $template ) {
		if ( ! $template instanceof Common_Template ) {
			return;
		}

		$this->container->make( Template::class )->template( 'template-parts/body/event/date', $template->get_local_values(), true );
	}

	/**
	 * Include the Event image in the ticket and RSVP emails.
	 *
	 * @since 6.1.1
	 *
	 * @param string          $file     Template file.
	 * @param string          $name     Template name.
	 * @param Common_Template $template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_image_ticket_rsvp_emails( $file, $name, $template ) {
		if ( ! $template instanceof Common_Template ) {
			return;
		}

		$this->container->make( Template::class )->template( 'template-parts/body/event/image', $template->get_local_values(), true );
	}

	/**
	 * Include the Event venue in the ticket and RSVP emails.
	 *
	 * @since 6.1.1
	 *
	 * @param string          $file     Template file.
	 * @param string          $name     Template name.
	 * @param Common_Template $template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_venue_ticket_rsvp_emails( $file, $name, $template ) {
		if ( ! $template instanceof Common_Template ) {
			return;
		}

		$this->container->make( Template::class )->template( 'template-parts/body/event/venue', $template->get_local_values(), true );
	}

	/**
	 * Include the Event links in the ticket and RSVP emails.
	 *
	 * @since 6.1.1
	 *
	 * @param string          $file     Template file.
	 * @param string          $name     Template name.
	 * @param Common_Template $template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_calendar_links( $file, $name, $template ) {
		if ( ! $template instanceof Common_Template ) {
			return;
		}

		$this->container->make( RSVP::class )->include_calendar_links( $template );
		$this->container->make( Ticket::class )->include_calendar_links( $template );
	}

	/**
	 * Include the Event link styles in the ticket and RSVP emails.
	 *
	 * @since 6.1.1
	 *
	 * @param string          $file     Template file.
	 * @param string          $name     Template name.
	 * @param Common_Template $template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_ticket_rsvp_styles( $file, $name, $template ) {
		if ( ! $template instanceof Common_Template ) {
			return;
		}

		$this->container->make( RSVP::class )->include_event_link_styles( $template );
		$this->container->make( Ticket::class )->include_event_link_styles( $template );
	}

	/**
	 * Include the Event data in the ticket and RSVP emails JSON LD data.
	 *
	 * @since 6.1.1
	 *
	 * @param array $data The existing JSON LD data.
	 * @param \TEC\Tickets\Emails\JSON_LD\Event_Schema $schema The JSON LD schema.
	 *
	 * @return array
	 */
	public function filter_include_json_ld_event_data( $data, $schema ): array {
		return $this->container->make( Event_Data::class )->filter_event_data( $data, $schema );
	}
}
