<?php
/**
 * Handles the compatibility with the Filter Bar plugin.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

use Tribe\Events\Event_Status\Status_Labels;
use Tribe\Events\Filterbar\Views\V2\Filters\Concatenated_Value_Handling;
use Tribe\Events\Filterbar\Views\V2\Filters\Context_Filter;
use Tribe\Events\Event_Status\Event_Meta as Event_Status_Meta;

/**
 * Class Events_Status_Filter.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */
class Events_Status_Filter extends \Tribe__Events__Filterbar__Filter {
	use Context_Filter;
	use Concatenated_Value_Handling;

	/**
	 * Value checked for "all" filter.
	 *
	 * @since TBD
	 */
	const EXPLICITLY_ALL = 'all';

	/**
	 * @var string The table alias that will be used for the postmeta table.
	 */
	protected $alias = 'tribe_event_status_filterbar_alias';

	/**
	 * The control type.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $type = 'select';

	/**
	 * The filter slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $slug = 'filterbar_event_status';

	/**
	 * Name for the Filter.
	 *
	 * @var string
	 */
	public $name = 'event_status';

	/**
	 * Status Labels.
	 *
	 * @since TBD
	 *
	 * @var Status_Labels
	 */
	protected $status_labels;

	/**
	 * Constructor.
	 *
	 * @param Status_Labels $status_labels An instance of the statuses handler.
	 */
	public function __construct( Status_Labels $status_labels) {
		$this->status_labels = $status_labels;
		$name = $this->status_labels->get_event_status_label();

		parent::__construct( $name, $this->slug );
	}

	/**
	 * Returns the admin form HTML.
	 *
	 * @return string
	 */
	public function get_admin_form() {
		return $this->get_title_field();
	}

	/**
	 * Get the name for the admin field.
	 *
	 * @since TBD
	 *
	 * @param string $name The individual name for the individual control (ie radio button).
	 * @return string
	 */
	protected function get_admin_field_name( $name ) {
		return "tribe_filter_options[{$this->slug}][{$name}]";
	}

	/**
	 * Returns the value supported by this filter.
	 *
	 * One actually.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_values() {
		/**
		 * Allow filtering of the event statuses.
		 *
		 * @since TBD
		 *
		 * @param array<string|string> An array of video sources.
		 * @param \WP_Post $event The current event post object, as decorated by the `tribe_get_event` function.
		 */
		$statuses = (array) apply_filters( 'tec_event_statuses', [], [] );

		$filter_statuses = [];
		foreach ( $statuses as $status ) {
			$filter_statuses[ $status['value'] ] = [
				'name'  => $status['text'],
				'value' => $status['value'],
			];
		}

		return $filter_statuses;
	}

	/**
	 * Sets up our join clause for the query.
	 *
	 * @since TBD
	 */
	protected function setup_join_clause() {
		/** @var \wpdb $wpdb */
		global $wpdb;

		$clause = "LEFT JOIN {$wpdb->postmeta} AS {$this->alias}
			ON ( {$wpdb->posts}.ID = {$this->alias}.post_id
			AND {$this->alias}.meta_key = %s )";

		$this->joinClause = $wpdb->prepare( $clause, Event_Status_Meta::$key_status );
	}

	/**
	 * Sets up our where clause for the query.
	 *
	 * @since TBD
	 */
	protected function setup_where_clause() {
		/** @var \wpdb $wpdb */
		global $wpdb;

		if ( is_array( $this->currentValue ) ) {
			$event_status_ids = implode( ',', array_map( 'esc_sql', $this->currentValue ) );
		} else {
			$event_status_ids = "'" . esc_sql( (string) $this->currentValue ) . "'";
		}
		$clauses = [];

		$clauses[] = $wpdb->prepare(
			" {$this->alias}.meta_value IN (%s) ",
			$event_status_ids,
		);

		if ( 'scheduled' === $this->currentValue || in_array( 'scheduled', $this->currentValue ) ) {
			$clauses[] = " {$this->alias}.meta_value = '' ";
			$clauses[] = " {$this->alias}.meta_value IS NULL ";
		}

		$this->whereClause = ' AND (' . implode( ' OR ', $clauses ) . ') ';
	}
}
