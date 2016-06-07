<?php
_deprecated_file( __FILE__, '4.2', 'Tribe__JSON_LD__Abstract' );

/**
 * Handles output of Google structured data markup
 */
abstract class Tribe__Events__Google_Data_Markup {

	/**
	 * Compile the schema.org event data into an array
	 */
	protected function build_data() {
		return Tribe__Events__JSON_LD__Event::instance()->get_data();
	}

	/**
	 * This method is kept for backwards compatiblity, does nothing!
	 */
	protected function filter_data( $data ) {
		return $data;
	}

	/**
	 * puts together the actual html/json javascript block for output
	 * @return string
	 */
	public function script_block() {
		return Tribe__Events__JSON_LD__Event::instance()->get_markup();
	}
}
