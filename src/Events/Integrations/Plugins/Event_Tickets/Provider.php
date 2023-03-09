<?php

namespace TEC\Events\Integrations\Plugins\Event_Tickets;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Events\Integrations\Plugins\Plugin_Integration;

/**
 * Class Provider
 *
 * @since   6.0.4
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */
class Provider extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * The option key to enable calendar links.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $option_add_calendar_links = 'tickets-enable-calendar-links';

	/**
	 * The option key to CC the event organizer in emails.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $option_cc_event_organizer = 'tickets-enable-cc-event-organizer';

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'event-tickets';
	}

	/**
	 * @inheritDoc
	 */
	public function load_conditionals(): bool {
		return class_exists( 'Tribe__Tickets__Main' );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		add_filter( 'tec_tickets_emails_ticket_settings', [ $this, 'filter_tec_ticket_settings' ], 11, 2 );
	}

	/**
	 * Filters Tickets Emails Ticket template setting to add TEC options.
	 *
	 * @param  array $fields  Settings fields array.
	 *
	 * @return array Filtered settings fields array.
	 */
	public function filter_tec_ticket_settings( $fields ) {

		$fields[ self::$option_add_calendar_links ] = [
			'type'            => 'checkbox_bool',
			'label'           => esc_html__( 'Add Calendar Links', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Include calendar links to events.', 'the-events-calendar' ),
			'default'         => true,
			'validation_type' => 'boolean',
		];

		$fields[ self::$option_cc_event_organizer ] = [
			'type'            => 'checkbox_bool',
			'label'           => esc_html__( 'CC Event Organizer', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'CC event organizers on all emails.', 'the-events-calendar' ),
			'default'         => true,
			'validation_type' => 'boolean',
		];

		return $fields;
	}
}
