<?php

namespace Tribe\Tests\Modules\Pro\Acceptance;


class Options extends \Codeception\Module {

	/**
	 * Returns an array of default options overriding the specified ones.
	 *
	 * @param array $overrides
	 *
	 * @return array
	 */
	public function getDefaultProOptions( array $overrides = [ ] ) {
		$defaults = array(
			'schema-version'                   => '4.0beta2',
			'recurring_events_are_hidden'      => 'exposed',
			'previous_ecp_versions'            => array(
				0 => '0',
			),
			'latest_ecp_version'               => '4.0beta2',
			'disable_metabox_custom_fields'    => 'show',
			'pro-schema-version'               => '4.0beta2',
			'donate-link'                      => false,
			'postsPerPage'                     => '10',
			'liveFiltersUpdate'                => true,
			'hideSubsequentRecurrencesDefault' => false,
			'userToggleSubsequentRecurrences'  => false,
			'recurrenceMaxMonthsBefore'        => '24',
			'recurrenceMaxMonthsAfter'         => '24',
			'showComments'                     => false,
			'showEventsInMainLoop'             => false,
			'eventsSlug'                       => 'events',
			'singleEventSlug'                  => 'event',
			'multiDayCutoff'                   => '00:00',
			'defaultCurrencySymbol'            => '$',
			'reverseCurrencyPosition'          => false,
			'embedGoogleMaps'                  => true,
			'geoloc_default_geofence'          => '25',
			'geoloc_default_unit'              => 'miles',
			'embedGoogleMapsZoom'              => '10',
			'debugEvents'                      => false,
			'tribe_events_timezone_mode'       => 'event',
			'tribe_events_timezones_show_zone' => false,
			'stylesheetOption'                 => 'tribe',
			'tribeEventsTemplate'              => 'default',
			'tribeEnableViews'                 => array(
				0 => 'list',
				1 => 'month',
				2 => 'week',
				3 => 'day',
				4 => 'map',
				5 => 'photo',
			),
			'viewOption'                       => 'month',
			'tribeDisableTribeBar'             => false,
			'hideLocationSearch'               => false,
			'hideRelatedEvents'                => false,
			'week_view_hide_weekends'          => false,
			'monthEventAmount'                 => '3',
			'enable_month_view_cache'          => false,
			'dateWithYearFormat'               => 'm/d/Y',
			'dateWithoutYearFormat'            => 'F j',
			'monthAndYearFormat'               => 'F Y',
			'weekDayFormat'                    => 'D jS',
			'dateTimeSeparator'                => ' @ ',
			'timeRangeSeparator'               => ' - ',
			'datepickerFormat'                 => '0',
			'tribeEventsBeforeHTML'            => '',
			'tribeEventsAfterHTML'             => '',
			'eventsDefaultOrganizerID'         => null,
			'eventsDefaultVenueID'             => null,
			'eventsDefaultAddress'             => '',
			'eventsDefaultCity'                => '',
			'eventsDefaultState'               => '',
			'eventsDefaultProvince'            => '',
			'eventsDefaultZip'                 => '',
			'defaultCountry'                   => '',
			'eventsDefaultPhone'               => '',
			'tribeEventsCountries'             => '',
			'custom-fields'                    => array(),
			'custom-fields-max-index'          => 2,
		);

		return array_merge($defaults,$overrides);
	}
}