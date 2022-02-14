<?php
/**
 * Provides and API to interact with the migration reports in a per-event and
 * per-site basis.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

/**
 * Class Reports.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Reports {

	/**
	 * Builds and returns the site migration report in array format.
	 *
	 * @since TBD
	 *
	 * @return Site_Report A reference the site migration report instance.
	 */
	public function build() {
		// @todo pull site-wide stats
		// @todo pull per-event stats
		$data = [];
		return new Site_Report( $data );
	}
}