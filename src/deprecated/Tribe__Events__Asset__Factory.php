<?php

class Tribe__Events__Asset__Factory extends Tribe__Asset__Factory {
	/**
	 * @return string
	 */
	protected function get_asset_class_name_prefix() {
		return 'Tribe__Events__Asset__';
	}

	/**
	 * @return Tribe__Events__Asset__Factory
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self;
		}

		return $instance;
	}
}
