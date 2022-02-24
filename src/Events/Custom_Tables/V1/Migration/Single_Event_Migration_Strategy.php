<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;

/**
 * Class Single_Event_Migration_Strategy.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Single_Event_Migration_Strategy implements Strategy_Interface {

	/**
	 * Single_Event_Migration_Strategy constructor.
	 *
	 * since TBD
	 *
	 * @param int  $post_id The post ID of the Event to migrate.
	 * @param bool $dry_run Whether the migration should actually commit information,
	 *                      or run in dry-run mode.
	 *
	 * @return Event_Report A reference to the report for the Event migration.
	 */
	public function __construct( $post_id, $dry_run ) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function apply(Event_Report $event_report) {
		// TODO: Implement apply() method.

		return $event_report;
	}

	/**
	 * {@inheritDoc}
	 */
	public function undo(Event_Report $event_report) {
		// TODO: Implement undo() method.
		return $event_report;
	}
}