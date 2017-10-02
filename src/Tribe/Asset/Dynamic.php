<?php


class Tribe__Events__Asset__Dynamic extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$deps = array_merge(
			$this->deps,
			array(
				'jquery',
				'tribe-events-php-date-formatter',
				'tribe-moment',
			)
		);

		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'events-dynamic.js' ), true );

		wp_enqueue_script( $this->prefix . '-dynamic', $path, $deps, $this->filter_js_version(), true );

		$data = array(
			'date_with_year'    => tribe_get_date_option( 'dateWithYearFormat', Tribe__Date_Utils::DBDATEFORMAT ),
			'date_no_year'      => tribe_get_date_option( 'dateWithoutYearFormat', Tribe__Date_Utils::DBDATEFORMAT ),
			'datepicker_format' => Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) ),
			'days'              => array(
				__( 'Sunday' ),
				__( 'Monday' ),
				__( 'Tuesday' ),
				__( 'Wednesday' ),
				__( 'Thursday' ),
				__( 'Friday' ),
				__( 'Saturday' ),
			),
			'daysShort'         => array(
				__( 'Sun' ),
				__( 'Mon' ),
				__( 'Tue' ),
				__( 'Wed' ),
				__( 'Thu' ),
				__( 'Fri' ),
				__( 'Sat' ),
			),
			'months'            => array(
				__( 'January' ),
				__( 'February' ),
				__( 'March' ),
				__( 'April' ),
				__( 'May' ),
				__( 'June' ),
				__( 'July' ),
				__( 'August' ),
				__( 'September' ),
				__( 'October' ),
				__( 'November' ),
				__( 'December' ),
			),
			'monthsShort'       => array(
				__( 'Jan' ),
				__( 'Feb' ),
				__( 'Mar' ),
				__( 'Apr' ),
				__( 'May' ),
				__( 'Jun' ),
				__( 'Jul' ),
				__( 'Aug' ),
				__( 'Sep' ),
				__( 'Oct' ),
				__( 'Nov' ),
				__( 'Dec' ),
			),
			'msgs'              => json_encode( array(
				__( 'This event is from %%starttime%% to %%endtime%% on %%startdatewithyear%%.', 'the-events-calendar' ),
				__( 'This event is at %%starttime%% on %%startdatewithyear%%.', 'the-events-calendar' ),
				__( 'This event is all day on %%startdatewithyear%%.', 'the-events-calendar' ),
				__( 'This event starts at %%starttime%% on %%startdatenoyear%% and ends at %%endtime%% on %%enddatewithyear%%', 'the-events-calendar' ),
				__( 'This event starts at %%starttime%% on %%startdatenoyear%% and ends on %%enddatewithyear%%', 'the-events-calendar' ),
				__( 'This event is all day starting on %%startdatenoyear%% and ending on %%enddatewithyear%%.', 'the-events-calendar' ),
			) ),
		);

		wp_localize_script( $this->prefix . '-dynamic', 'tribe_dynamic_help_text', $data );

	}
}
