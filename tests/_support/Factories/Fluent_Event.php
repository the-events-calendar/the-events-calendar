<?php
/**
 * A utility class to allow building event meta information for testing purposes
 * with a readable syntax.
 *
 * @package Tribe\Events\Test\Factories
 */

namespace Tribe\Events\Test\Factories;

/**
 * Class Fluent_Event
 *
 * @package Tribe\Events\Test\Factories
 * @since   4.9.2
 */
class Fluent_Event {
	/**
	 * The start date string in a strtotime parse-able format.
	 *
	 * @var string
	 */
	protected $start_date = 'now';

	/**
	 * The timezone string, only PHP valid timezones are supported.
	 *
	 * @var string
	 */
	protected $timezone = 'UTC';

	/**
	 * The duration in seconds.
	 *
	 * @var int
	 */
	protected $duration = 7200;

	/**
	 * The factory this builder should use to create.
	 *
	 *
	 * @var mixed
	 */
	protected $factory;


	/**
	 * Fluent_Event constructor.
	 *
	 * @param string $start_date The event start date in a strtotime parse-able form.
	 */
	public function __construct( string $start_date ) {
		$this->start_date = $start_date;
	}

	/**
	 * Sets the event timezone string.
	 *
	 * @param string $timezone The event timezone string, must be a valid PHP one.
	 *
	 * @return $this This object to chain.
	 */
	public function with_timezone( string $timezone ): self {
		$this->timezone = $timezone;

		return $this;
	}

	/**
	 * Sets the event duration in seconds.
	 *
	 * @param int $duration The duration in seconds.
	 *
	 * @return $this This object to chain.
	 */
	public function lasting( int $duration ): self {
		$this->duration = $duration;

		return $this;
	}

	/**
	 * Builds and returns the data in a format suitable for use in a post factory creation method.
	 *
	 * @param array $overrides An array of generation definitions to override the defaults.
	 *
	 * @return int The ID of the post created using the parameters by the factory.
	 *
	 * @throws \Exception If the duration is not valid.
	 */
	public function create( array $overrides = [] ): int {
		$start = new \DateTime( $this->start_date, new \DateTimeZone( $this->timezone ) );
		$end   = clone $start;
		$end->add( new \DateInterval( "PT{$this->duration}S" ) );
		$utc = new \DateTimeZone( 'UTC' );

		$meta_input = array_merge( $overrides['meta_input'] ?? [], [
			'_EventStartDAte'    => $start->format( 'Y-m-d H:i:s' ),
			'_EventEndDate'      => $end->format( 'Y-m-d H:i:s' ),
			'_EventStartDateUTC' => $start->setTimezone( $utc )->format( 'Y-m-d H:i:s' ),
			'_EventEndDateUTC'   => $end->setTimezone( $utc )->format( 'Y-m-d H:i:s' ),
			'_EventDuration'     => $this->duration,
			'_EventTimezone'     => $this->timezone,
		] );

		unset( $overrides['meta_input'] );

		$overrides = array_merge( $overrides, [
			'meta_input' => $meta_input,
		] );

		return $this->factory->create( $overrides );
	}

	/**
	 * Sets the factory this builder should use to create the post.
	 *
	 * @param mixed $factory A factory object of any kind, not type-hinted
	 *                       by design.
	 */
	public function set_factory( $factory ) {
		$this->factory = $factory;
	}
}
