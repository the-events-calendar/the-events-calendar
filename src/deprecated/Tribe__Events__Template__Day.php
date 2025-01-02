<?php

_deprecated_file( __FILE__, '6.0.0' );

/**
 * This file contains hooks and functions required to set up the day view.
 * @deprecated 6.0.0
 */
class Tribe__Events__Template__Day extends Tribe__Events__Template_Factory {
	/**
	 * @deprecated 6.0.0
	 */
	const AJAX_HOOK = 'tribe_event_day';

	/**
	 * @deprecated 6.0.0
	 */
	public $view_path = 'day/content';


	/**
	 * @deprecated 6.0.0
	 */
	public function hooks() {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function header_attributes( $attrs ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function ical_link( $link ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function setup_view() {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function ajax_response() {
		_deprecated_function( __METHOD__, '6.0.0' );
	}
}
