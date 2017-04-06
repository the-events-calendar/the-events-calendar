<?php

namespace Tribe\Tests\Modules\Options;


class Options extends \Codeception\Module {

	/**
	 * Returns an array of default options overriding the specified ones.
	 *
	 * @param array $overrides
	 *
	 * @return array
	 */
	public function getDefaultCoreOptions( array $overrides = [ ] ) {
		$defaults = [
			'schema-version'                   => '4.0beta2',
			'recurring_events_are_hidden'      => 'hidden',
			'previous_ecp_versions'            => [
				0 => '0',
			],
			'latest_ecp_version'               => '4.0beta2',
			'donate-link'                      => false,
			'postsPerPage'                     => '10',
			'liveFiltersUpdate'                => true,
			'showComments'                     => false,
			'showEventsInMainLoop'             => false,
			'eventsSlug'                       => 'events',
			'singleEventSlug'                  => 'event',
			'multiDayCutoff'                   => '00:00',
			'defaultCurrencySymbol'            => '$',
			'reverseCurrencyPosition'          => false,
			'embedGoogleMaps'                  => true,
			'embedGoogleMapsZoom'              => '10',
			'debugEvents'                      => false,
			'tribe_events_timezone_mode'       => 'event',
			'tribe_events_timezones_show_zone' => false,
			'stylesheetOption'                 => 'tribe',
			'tribeEventsTemplate'              => 'default',
			'tribeEnableViews'                 => [
				0 => 'list',
				1 => 'month',
				2 => 'day',
			],
			'viewOption'                       => 'month',
			'tribeDisableTribeBar'             => false,
			'monthEventAmount'                 => '3',
			'enable_month_view_cache'          => false,
			'dateWithYearFormat'               => 'm/d/Y',
			'dateWithoutYearFormat'            => 'F j',
			'monthAndYearFormat'               => 'F Y',
			'dateTimeSeparator'                => ' @ ',
			'timeRangeSeparator'               => ' - ',
			'datepickerFormat'                 => '0',
			'tribeEventsBeforeHTML'            => '',
			'tribeEventsAfterHTML'             => '',
		];

		return array_merge( $defaults, $overrides );
	}
}