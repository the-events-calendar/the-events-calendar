<?php


class Tribe__Events__Pro__Recurrence__Custom_Types {

	const SLUG                = 'Custom';
	const CUSTOM_TYPE         = 'Custom';
	const DATE_CUSTOM_TYPE    = 'Date';
	const EVERY_DAY_TYPE      = 'Every Day';
	const DAILY_CUSTOM_TYPE   = 'Daily';
	const EVERY_WEEK_TYPE     = 'Every Week';
	const WEEKLY_CUSTOM_TYPE  = 'Weekly';
	const EVERY_MONTH_TYPE    = 'Every Month';
	const MONTHLY_CUSTOM_TYPE = 'Monthly';
	const EVERY_YEAR_TYPE     = 'Every Year';
	const YEARLY_CUSTOM_TYPE  = 'Yearly';


	/**
	 * converts a custom type to a custom type array index slug
	 *
	 * @param string $custom_type Friendly Custom-Type value
	 *
	 * @return string
	 */
	public static function to_key( $custom_type ) {
		switch ( $custom_type ) {
			case self::DATE_CUSTOM_TYPE:
				return 'date';
			case self::YEARLY_CUSTOM_TYPE:
				return 'year';
			case self::MONTHLY_CUSTOM_TYPE:
				return 'month';
			case self::WEEKLY_CUSTOM_TYPE:
				return 'week';
			case self::DAILY_CUSTOM_TYPE:
			default:
				return 'day';
		}
	}

	public static function data_keys() {
		return array(
			'start-time',
			'day',
			'week',
			'month',
			'year'
		);
	}

	/**
	 * Gets a list of the legit and valid custom recurrence types.
	 *
	 * @return array
	 */
	public static function get_legit_custom_types() {
		return array(
			self::DATE_CUSTOM_TYPE,
			self::DAILY_CUSTOM_TYPE,
			self::WEEKLY_CUSTOM_TYPE,
			self::MONTHLY_CUSTOM_TYPE,
			self::YEARLY_CUSTOM_TYPE,
		);
	}

	public static function get_legit_recurrence_types() {
		return array(
			self::EVERY_DAY_TYPE,
			self::EVERY_WEEK_TYPE,
			self::EVERY_MONTH_TYPE,
			self::EVERY_YEAR_TYPE,
		);
	}
}