<?php


class Tribe__Events__Pro__Recurrence__Validator {

	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var int
	 */
	protected $event_id;

	/**
	 * @var array
	 */
	protected $recurrence_meta;

	/**
	 * Recurrence validation method.  This is checked after saving an event, but before splitting a series out into
	 * multiple occurrences
	 *
	 * @param int   $event_id        The event object that is being saved
	 * @param array $recurrence_meta Recurrence information for this event
	 *
	 * @return bool
	 */
	public function is_valid( $event_id, array $recurrence_meta ) {
		$response = (object) array(
			'valid' => true,
			'message' => '',
		);

		$this->event_id        = $event_id;
		$this->recurrence_meta = $recurrence_meta;

		if ( ! tribe_is_event( $event_id ) ) {
				$response->valid   = false;
			$response->message = __( 'Not an event post.', 'tribe-events-calendar-pro' );

			return $this->filtered_response( $response );
		}

		try {
			$this->ensure_not_empty();
			if ( $this->is_custom() ) {
				$this->ensure_custom_type();
				$this->ensure_all_data();
				if ( $this->is_monthly() ) {
					$this->ensure_monthly_day_and_number();
				} else if ( $this->is_yearly() ) {
					$this->ensure_yearly_day(); }
			}
		} catch ( RuntimeException $e ) {
				$response->valid   = false;
			$response->message = $e->getMessage();
		}

		return $this->filtered_response( $response );
	}

	/**
	 * Singleton constructor method for the class.
	 *
	 * @return Tribe__Events__Pro__Recurrence__Validator
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
			}

		return self::$instance;
		}

	/**
	 * Returns the validation response `valid` boolean after filtering.
	 *
	 * @param stdClass $response
	 *
	 * @return bool Wheter the response is considered valid or not.
	 */
	private function filtered_response( $response ) {
		$response = apply_filters( 'tribe_recurring_pre_event_error', $response, $this->event_id, $this->recurrence_meta );

		if ( ! $response->valid ) {
			do_action( 'tribe_recurring_event_error', $response, $this->event_id, $this->recurrence_meta );
		}

		return $response->valid;
	}

	protected function is_custom() {
		return isset( $this->recurrence_meta['type'] ) && Tribe__Events__Pro__Recurrence__Custom_Types::SLUG === $this->recurrence_meta['type'];
	}

	private function ensure_custom_type() {
		if ( ! isset( $this->recurrence_meta['custom']['type'] ) ) {
			throw new RuntimeException( __( 'Custom recurrences must have a type selected.', 'tribe-events-calendar-pro' ) );
		}
	}

	protected function ensure_all_data() {
		$empty_custom_key   = empty( $this->recurrence_meta['custom'] );
		$custom_keys = array_intersect( Tribe__Events__Pro__Recurrence__Custom_Types::data_keys(), array_keys( $this->recurrence_meta['custom'] ) );
		$not_a_valid_custom = empty( $custom_keys );
		if ( $empty_custom_key || $not_a_valid_custom ) {
			throw new RuntimeException( __( 'Custom recurrences must have all data present.', 'tribe-events-calendar-pro' ) );
		}
	}

	private function ensure_monthly_day_and_number() {
		$no_day          = empty( $this->recurrence_meta['custom']['month']['day'] );
		$day_is_dash     = '-' === $this->recurrence_meta['custom']['month']['day'];
		$no_number       = empty( $this->recurrence_meta['custom']['month']['number'] );
		$is_missing_data = $no_day || $no_number || $day_is_dash;

		if ( $is_missing_data ) {
			throw new RuntimeException            ( __( 'Monthly custom recurrences cannot have a dash set as the day to occur on.', 'tribe-events-calendar-pro' ) );
		}
	}

	private function ensure_yearly_day() {
		$empty_month_day   = empty( $this->recurrence_meta['custom']['year']['month-day'] );
		$month_day_is_dash = '-' === $this->recurrence_meta['custom']['year']['month-day'];
		$no_month_day      = $empty_month_day || $month_day_is_dash;

		if ( $no_month_day ) {
			throw new RuntimeException ( __( 'Yearly custom recurrences cannot have a dash set as the day to occur on.', 'tribe-events-calendar-pro' ) );
		}
	}

	private function ensure_not_empty() {
		if ( empty( $this->recurrence_meta ) ) {
			throw new RuntimeException ( __( 'Recurrence meta should not be empty.', 'tribe-events-calendar-pro' ) );
		}
	}

	/**
	 * @return bool
	 */
	private function is_monthly() {
		$is_monthly = Tribe__Events__Pro__Recurrence__Custom_Types::MONTHLY_CUSTOM_TYPE === $this->recurrence_meta['custom']['type'];

		return $is_monthly;
	}

	/**
	 * @return bool
	 */
	private function is_yearly() {
		$is_yearly = Tribe__Events__Pro__Recurrence__Custom_Types::YEARLY_CUSTOM_TYPE === $this->recurrence_meta['custom']['type'];

		return $is_yearly;
	}
}
