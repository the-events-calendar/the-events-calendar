<?php


class Tribe__Events__Pro__Recurrence__Series_Rules_Factory {

	/**
	 * @var Tribe__Events__Pro__Recurrence__Series_Rules_Factory
	 */
	protected static $instance;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Pro__Recurrence__Series_Rules_Factory
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Builds and returns the date series rule needed to find the next occurences of the event.
	 *
	 * @param array $recurrence An event recurrence meta entry.
	 * @param string $rule_type The rule type, defaults to `rules`.
	 *
	 * @return Tribe__Events__Pro__Date_Series_Rules__Rules_Interface A date series rule instance.
	 */
	public function build_from( array $recurrence, $rule_type = 'rules' ) {
		if ( 'exclusions' === $rule_type ) {
			$recurrence['type'] = Tribe__Events__Pro__Recurrence__Custom_Types::CUSTOM_TYPE;
		}

		$rule = null;

		if ( Tribe__Events__Pro__Recurrence__Custom_Types::CUSTOM_TYPE === $recurrence['type'] && ! isset( $recurrence['custom']['interval'] ) ) {
			$recurrence['custom']['interval'] = 1;
		}

		$type = $this->get_recurrence_type( $recurrence );

		return $this->build_rule_for_type( $type, $recurrence );
	}

	/**
	 * Convert an ordinal from an ECP recurrence series into an integer
	 *
	 * @param string $ordinal The ordinal number
	 *
	 * @return An integer representation of the ordinal
	 */
	private static function ordinalToInt( $ordinal ) {
		switch ( $ordinal ) {
			case 'First':
				return 1;
			case 'Second':
				return 2;
			case 'Third':
				return 3;
			case 'Fourth':
				return 4;
			case 'Fifth':
				return 5;
			case 'Last':
				return - 1;
			default:
				return null;
		}
	}

	private function get_recurrence_type( array $recurrence ) {
		$invalid_type = empty( $recurrence['type'] );

		if ( $invalid_type ) {
			return 'invalid';
		}

		$no_custom_type = empty( $recurrence['custom']['type'] );
		$valid_type     = in_array(
			$recurrence['type'], Tribe__Events__Pro__Recurrence__Custom_Types::get_legit_recurrence_types()
		);
		if ( $no_custom_type && $valid_type ) {
			return $recurrence['type'];
		}
		$is_valid_custom_type = $recurrence['type'] == Tribe__Events__Pro__Recurrence__Custom_Types::CUSTOM_TYPE && in_array(
				$recurrence['custom']['type'], Tribe__Events__Pro__Recurrence__Custom_Types::get_legit_custom_types()
			);
		if ( $is_valid_custom_type ) {
			return $recurrence['custom']['type'];
		}

		return 'invalid';
	}

	private function build_rule_for_type( $type = Tribe__Events__Pro__Recurrence__Custom_Types::DATE_CUSTOM_TYPE, array $recurrence ) {
		switch ( $type ) {
			case Tribe__Events__Pro__Recurrence__Custom_Types::DATE_CUSTOM_TYPE:
				$rule = new Tribe__Events__Pro__Date_Series_Rules__Date(
					strtotime( $recurrence['custom']['date']['date'] )
				);
				break;
			case Tribe__Events__Pro__Recurrence__Custom_Types::EVERY_DAY_TYPE:
				$rule = new Tribe__Events__Pro__Date_Series_Rules__Day( 1 );
				break;
			case Tribe__Events__Pro__Recurrence__Custom_Types::DAILY_CUSTOM_TYPE:
				$rule = new Tribe__Events__Pro__Date_Series_Rules__Day( $recurrence['custom']['interval'] );
				break;
			case Tribe__Events__Pro__Recurrence__Custom_Types::EVERY_WEEK_TYPE:
				$rule = new Tribe__Events__Pro__Date_Series_Rules__Week( 1 );
				break;
			case Tribe__Events__Pro__Recurrence__Custom_Types::WEEKLY_CUSTOM_TYPE:
				$days = empty( $recurrence['custom']['week']['day'] ) ? array() : $recurrence['custom']['week']['day'];
				$rule = new Tribe__Events__Pro__Date_Series_Rules__Week(
					$recurrence['custom']['interval'], $days
				);
				break;
			case Tribe__Events__Pro__Recurrence__Custom_Types::EVERY_MONTH_TYPE:
				$rule = new Tribe__Events__Pro__Date_Series_Rules__Month( 1 );
				break;
			case Tribe__Events__Pro__Recurrence__Custom_Types::MONTHLY_CUSTOM_TYPE:
				$day_of_month = isset( $recurrence['custom']['month']['number'] ) && is_numeric(
					$recurrence['custom']['month']['number']
				) ? array( $recurrence['custom']['month']['number'] ) : null;
				$month_number = self::ordinalToInt(
					$recurrence['custom']['month']['number']
				);
				$rule         = new Tribe__Events__Pro__Date_Series_Rules__Month(
					$recurrence['custom']['interval'], $day_of_month, $month_number,
					$recurrence['custom']['month']['day']
				);
				break;
			case Tribe__Events__Pro__Recurrence__Custom_Types::EVERY_YEAR_TYPE:
				$rule = new Tribe__Events__Pro__Date_Series_Rules__Year( 1 );
				break;
			case Tribe__Events__Pro__Recurrence__Custom_Types::YEARLY_CUSTOM_TYPE:
				$rule = new Tribe__Events__Pro__Date_Series_Rules__Year(
					$recurrence['custom']['interval'], $recurrence['custom']['year']['month'],
					empty( $recurrence['custom']['year']['filter'] ) ? null : $recurrence['custom']['year']['month-number'],
					empty( $recurrence['custom']['year']['filter'] ) ? null : $recurrence['custom']['year']['month-day']
				);
				break;
			default:
				$data = json_encode( $recurrence );
				$rule = new WP_Error(
					'invalid-recurrence-data', "A recurrence series rule could not be built using the data '{$data}'"
				);
				break;
		}

		return $rule;
	}

}