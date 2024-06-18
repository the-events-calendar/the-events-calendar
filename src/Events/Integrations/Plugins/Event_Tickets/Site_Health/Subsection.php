<?php
/**
 * Class that handles interfacing with core Site Health.
 *
 * @since 6.3.2
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Site_Health
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Site_Health;

use TEC\Tickets\Site_Health\Abstract_Info_Subsection;

/**
 * Class The_Events_Calendar_Fields
 *
 * @since 6.3.2
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Site_Health
 */
class Subsection extends Abstract_Info_Subsection {

	/**
	 * @inheritDoc
	 */
	protected function is_subsection_enabled(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function generate_subsection(): array {
		return [
			[
				'id'       => 'number_of_ticketed_events',
				'title'    => esc_html__(
					'Number of Ticketed Events',
					'event-tickets'
				),
				'value'    => $this->get_number_of_ticketed_events(),
				'priority' => 90,
			],
			[
				'id'       => 'number_of_ticketed_events_happening_now',
				'title'    => esc_html__(
					'Number of Ticketed Events Happening Now',
					'event-tickets'
				),
				'value'    => $this->get_number_of_ticketed_events_happening_now(),
				'priority' => 100,
			],
			[
				'id'       => 'average_number_of_attendees_per_event',
				'title'    => esc_html__(
					'Average Number of Attendees per Event',
					'event-tickets'
				),
				'value'    => $this->get_average_attendees_per_event(),
				'priority' => 140,
			],
		];
	}

	/**
	 * Counts the number of ticketed events.
	 *
	 * @return int Count of ticketed events.
	 */
	private function get_number_of_ticketed_events(): int {
		return tribe( 'tickets.event-repository' )->per_page( -1 )->where( 'has_tickets' )->count();
	}

	/**
	 * Counts the number of ticketed events happening now.
	 *
	 * @return int Count of ticketed events currently happening.
	 */
	private function get_number_of_ticketed_events_happening_now(): int {
		return tribe( 'tickets.event-repository' )->where( 'has_tickets' )->where(
			'ends_after',
			'now'
		)->count();
	}

	/**
	 * Calculates the average number of attendees per event.
	 *
	 * @return int Average number of attendees per event.
	 */
	private function get_average_attendees_per_event(): int {
		$attendee_count       = (int) tribe( 'tickets.attendee-repository' )->count();
		$ticketed_event_count = (int) tribe( 'tickets.event-repository' )->per_page( -1 )->where(
			'has_tickets'
		)->count();

		if ( 0 === $ticketed_event_count ) {
			return 0;
		}

		$average_attendees_per_event = floor( $attendee_count / $ticketed_event_count );

		return $average_attendees_per_event;
	}
}
