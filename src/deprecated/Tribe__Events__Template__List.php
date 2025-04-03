<?php

_deprecated_file( __FILE__, '6.0.0' );

/**
 * List view template class
 *
 * @deprecated 6.0.0
 */
class Tribe__Events__Template__List extends Tribe__Events__Template_Factory {

	/**
	 * @deprecated 6.0.0
	 */
	const AJAX_HOOK = 'tribe_list';

	/**
	 * @deprecated 6.0.0
	 */
	public $view_path = 'list/content';

	/**
	 * @deprecated 6.0.0
	 */
	public function ajax_response() {
		_deprecated_function( __METHOD__, '6.0.0' );
	}
}
