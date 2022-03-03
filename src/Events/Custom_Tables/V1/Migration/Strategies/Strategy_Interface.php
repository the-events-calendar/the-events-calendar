<?php
/**
 * The API implemented by any implemetation of a Custom Tables v1 Event migration strategy.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Strategies
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Strategies;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;

/**
 * Interface StrategyInterface.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Stategies
 */
interface Strategy_Interface {
	/**
	 * Returns the strategy slug that should uniquely identify it among the possible
	 * migration strategies.
	 *
	 * @since TBD
	 *
	 * @return string The migration strategy slug.
	 */
	public static function get_slug();

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