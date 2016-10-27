<?php


class Tribe__Events__Asset__Admin extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$deps = array_merge(
			$this->deps,
				array(
					'jquery',
					'jquery-ui-datepicker',
					'jquery-ui-sortable',
					'tribe-bumpdown',
					'underscore',
					'tribe-events-php-date-formatter',
					'wp-util',
				)
		);

		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'events-admin.js' ), true );

		wp_enqueue_script( $this->prefix . '-admin', $path, $deps, $this->filter_js_version(), true );

		$data = array(
			'date_with_year'    => tribe_get_date_option( 'dateWithYearFormat', Tribe__Date_Utils::DBDATEFORMAT ),
			'date_no_year'      => tribe_get_date_option( 'dateWithoutYearFormat', Tribe__Date_Utils::DBDATEFORMAT ),
			'datepicker_format' => Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) ),
			'days'              => array(
				__( 'Sunday', 'the-events-calendar' ),
				__( 'Monday', 'the-events-calendar' ),
				__( 'Tuesday', 'the-events-calendar' ),
				__( 'Wednesday', 'the-events-calendar' ),
				__( 'Thursday', 'the-events-calendar' ),
				__( 'Friday', 'the-events-calendar' ),
				__( 'Saturday', 'the-events-calendar' ),
			),
			'daysShort'         => array(
				__( 'Sun', 'the-events-calendar' ),
				__( 'Mon', 'the-events-calendar' ),
				__( 'Tue', 'the-events-calendar' ),
				__( 'Wed', 'the-events-calendar' ),
				__( 'Thu', 'the-events-calendar' ),
				__( 'Fri', 'the-events-calendar' ),
				__( 'Sat', 'the-events-calendar' ),
			),
			'months'            => array(
				__( 'January', 'the-events-calendar' ),
				__( 'February', 'the-events-calendar' ),
				__( 'March', 'the-events-calendar' ),
				__( 'April', 'the-events-calendar' ),
				__( 'May', 'the-events-calendar' ),
				__( 'June', 'the-events-calendar' ),
				__( 'July', 'the-events-calendar' ),
				__( 'August', 'the-events-calendar' ),
				__( 'September', 'the-events-calendar' ),
				__( 'October', 'the-events-calendar' ),
				__( 'November', 'the-events-calendar' ),
				__( 'December', 'the-events-calendar' ),
			),
			'monthsShort'       => array(
				__( 'Jan', 'the-events-calendar' ),
				__( 'Feb', 'the-events-calendar' ),
				__( 'Mar', 'the-events-calendar' ),
				__( 'Apr', 'the-events-calendar' ),
				__( 'May', 'the-events-calendar' ),
				__( 'Jun', 'the-events-calendar' ),
				__( 'Jul', 'the-events-calendar' ),
				__( 'Aug', 'the-events-calendar' ),
				__( 'Sep', 'the-events-calendar' ),
				__( 'Oct', 'the-events-calendar' ),
				__( 'Nov', 'the-events-calendar' ),
				__( 'Dec', 'the-events-calendar' ),
			),
			'msgs'              => json_encode( array(
				__( 'This event is from %%starttime%% to %%endtime%% on %%startdatewithyear%%.', 'the-events-calendar' ),
				__( 'This event is at %%starttime%% on %%startdatewithyear%%.', 'the-events-calendar' ),
				__( 'This event is all day on %%startdatewithyear%%.', 'the-events-calendar' ),
				__( 'This event starts at %%starttime%% on %%startdatenoyear%% and ends at %%endtime%% on %%enddatewithyear%%', 'the-events-calendar' ),
				__( 'This event is all day starting on %%startdatenoyear%% and ending on %%enddatewithyear%%.', 'the-events-calendar' ),
			) ),
		);

		wp_localize_script( $this->prefix . '-admin', 'tribe_dynamic_help_text', $data );

	}
}
