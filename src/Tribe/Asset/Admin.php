<?php


class Tribe__Events__Asset__Admin extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$deps = array_merge( $this->deps, array(
			'jquery',
			'jquery-ui-datepicker',
			'jquery-ui-sortable',
			'tribe-bumpdown',
			'tribe-events-php-date-formatter',
			'wp-util',
		) );

		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'events-admin.js' ), true );

		wp_enqueue_script( $this->prefix . '-admin', $path, $deps, $this->filter_js_version(), true );

		$data = array(
			'date_with_year'    => tribe_get_date_option( 'dateWithYearFormat', Tribe__Date_Utils::DBDATEFORMAT ),
			'date_no_year'      => tribe_get_date_option( 'dateWithoutYearFormat', Tribe__Date_Utils::DBDATEFORMAT ),
			'datepicker_format' => Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) ),
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
