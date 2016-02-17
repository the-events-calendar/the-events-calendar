<?php

namespace Tribe\Tests\Modules\Pro\Acceptance;

class Widgets extends \Codeception\Module {

	/**
	 * @var int[]
	 */
	protected $counters = [ ];

	public function getWidgetSlug( $prettyName ) {
		$map = [
			'mini calendar' => 'tribe-mini-calendar',
			'countdown'     => 'tribe-events-countdown-widget',
			'venue'         => 'tribe-events-venue-widget',
			'list'          => 'tribe-events-adv-list-widget',
		];

		return array_key_exists( $prettyName, $map ) ? $map[ $prettyName ] . '-' . $this->count( $map[ $prettyName ] ) : '';
	}

	public function getAdvancedListWidgetSettings( array $overrides = [ ] ) {
		$defaults = array(
			'title'              => 'Events List',
			'limit'              => '5',
			'no_upcoming_events' => null,
			'venue'              => '1',
			'country'            => '1',
			'address'            => '1',
			'city'               => '1',
			'region'             => '1',
			'zip'                => '1',
			'phone'              => '1',
			'cost'               => '1',
			'organizer'          => '1',
			'operand'            => 'OR',
			'filters'            => '',
		);

		return array_merge( $defaults, $overrides );
	}

	public function getCountdownWidgetSettings( array $overrides = [ ] ) {
		return array_merge( array(
			'title'        => 'Events Countdown',
			'show_seconds' => 1,
			'type'         => 'single-event',
			'complete'     => 'Hooray!',
			'event'        => null,
			'event_ID'     => null,
			'event_date'   => null,
		), $overrides );
	}

	public function getVenueWidgetSettings( array$overrides = [ ] ) {
		$defaults = [
			'title'         => 'Featured Venue',
			'count'         => '3',
			'hide_if_empty' => '1',
			'venue_ID'      => null,
		];

		return array_merge( $defaults, $overrides );
	}

	public function getMiniCalendarWidgetSettings( array $overrides = [ ] ) {
		$defaults = array(
			'title'   => 'Events Calendar',
			'count'   => 5,
			'operand' => 'OR',
			'filters' => '',
		);

		return array_merge( $defaults, $overrides );
	}

	private function count( $slug ) {
		if ( empty( $this->counters[ $slug ] ) ) {
			$this->counters[ $slug ] = 0;
		}
		$this->counters[ $slug ] += 1;

		return $this->counters[ $slug ];
	}
}