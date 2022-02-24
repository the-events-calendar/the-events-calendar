<?php
/**
 * The API implemented by any implemetation of a Custom Tables v1 Event migration strategy.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;

/**
 * Interface StrategyInterface.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
interface Strategy_Interface {

	/**
	 * Applies the migration strategy and returns a report representing
	 * its effects.
	 *
	 * @since TBD
	 *
	 * @param Event_Report
	 *
	 * @return Event_Report A reference to the report for the Event migration.
	 */
	public function apply( Event_Report $event_report );

	/**
	 * Undoes an Event migration.
	 *
	 * @since TBD
	 *
	 * @param Event_Report
	 *
	 * @return Event_Report A reference to the report for the Event migration undoing.
	 */
	public function undo( Event_Report $event_report );
}