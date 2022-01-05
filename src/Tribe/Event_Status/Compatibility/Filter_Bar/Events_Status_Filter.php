<?php
/**
 * Handles the compatibility with the Filter Bar plugin.
 *
 * @since   5.12.1
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

use Tribe\Events\Event_Status\Status_Labels;
use Tribe\Events\Filterbar\Views\V2\Filters\Context_Filter;
use Tribe\Events\Event_Status\Event_Meta as Event_Status_Meta;

/**
 * Class Events_Status_Filter.
 *
 * @since   5.12.1
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */
class Events_Status_Filter extends \Tribe__Events__Filterbar__Filter {
	use Context_Filter;

	/**
	 * Value checked for canceled events.
	 *
	 * @since   5.12.1
	 */
	const CANCELED = 'canceled';

	/**
	 * Value checked for postponed events.
	 *
	 * @since   5.12.1
	 */
	const POSTPONED = 'postponed';

	/**
	 * Event Status Table Alias.
	 *
	 * @since   5.12.1
	 *
	 * @var string The table alias that will be used for the postmeta table.
	 */
	protected $alias = 'tribe_event_status_filterbar_alias';

	/**
	 * The control type.
	 *
	 * @since 5.12.1
	 *
	 * @var string
	 */
	public $type = 'checkbox';

	/**
	 * The filter slug.
	 *
	 * @since 5.12.1
	 *
	 * @var string
	 */
	public $slug = 'filterbar_event_status';

	/**
	 * Name for the Filter.
	 *
	 * @since   5.12.1
	 *
	 * @var string
	 */
	public $name = 'event_status';

	/**
	 * Status Labels.
	 *
	 * @since 5.12.1
	 *
	 * @var Status_Labels
	 */
	protected $status_labels;

	/**
	 * Constructor.
	 *
	 * @since   5.12.1
	 *
	 * @param Status_Labels $status_labels An instance of the statuses handler.
	 */
	public function __construct( Status_Labels $status_labels ) {
		$this->status_labels = $status_labels;
		$name = $this->status_labels->get_event_status_label();

		parent::__construct( $name, $this->slug );
	}

	/**
	 * Returns the admin form HTML.
	 *
	 * @since   5.12.1
	 *
	 * @return string
	 */
	public function get_admin_form() {
		return $this->get_title_field();
	}

	/**
	 * Get the name for the admin field.
	 *
	 * @since 5.12.1
	 *
	 * @param string $name The individual name for the individual control (ie radio button).
	 *
	 * @return string The admin field input name.
	 */
	protected function get_admin_field_name( $name ) {
		return "tribe_filter_options[{$this->slug}][{$name}]";
	}

	/**
	 * Returns the value supported by this filter.
	 *
	 * @since 5.12.1
	 *
	 * @param array<string|mixed> An array of values.
	 */
	protected function get_values() {
		$default_values = [
			'canceled'  => [
				'name'  => _x( 'Hide canceled events', 'Canceled label for filter bar to hide canceled events.', 'the-events-calendar' ),
				'value' => self::CANCELED,
			],
			'postponed' => [
				'name'  => _x( 'Hide postponed events', 'Postponed label for filter bar to hide postponed events.', 'the-events-calendar' ),
				'value' => self::POSTPONED,
			],
		];

		/**
		 * Allow filtering of the event statuses values that show in Filter Bar.
		 *
		 * @since 5.12.1
		 *
		 * @param array<string|string> $default_values An array of filter values.
		 */
		return (array) apply_filters( 'tec_event_status_filterbar_values', $default_values );
	}

	/**
	 * Sets up our join clause for the query.
	 *
	 * @since 5.12.1
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
	 * @since 5.12.1
	 */
	protected function setup_where_clause() {
		/** @var \wpdb $wpdb */
		global $wpdb;

		$clauses = $hide_clauses = [];

		// Standard clauses to include so all events except for the selected ones to hide will be included.
		$clauses[] = " {$this->alias}.meta_value = '' ";
		$clauses[] = " {$this->alias}.meta_value IS NULL ";

		if ( is_array( $this->currentValue ) ) {
			$event_status_ids = implode( ',', array_map( 'esc_sql', $this->currentValue ) );
		} else {
			$event_status_ids = "'" . esc_sql( (string) $this->currentValue ) . "'";
		}

		$hide_clauses[] = $wpdb->prepare(
			" {$this->alias}.meta_value NOT IN (%s) ",
			$event_status_ids
		);

		/**
		 * Allow filtering of the event statuses where clause.
		 *
		 * @since 5.12.1
		 *
		 * @param string                      $where_clause  The empty where clause to filter.
		 * @param string|array<string|string> $current_value A string or array of the current values selected for the filter.
		 * @param string                      $alias         The table alias that will be used for the postmeta table.
		 * @param array<string|string>        $hide_clauses  The hide clauses on whether to hide canceled and postponed events.
		 * @param array<string|string>        $clauses       The standard clauses to get all events.
		 */
		$where_clause = apply_filters( 'tec_event_status_filterbar_where_clause', '', $this->currentValue, $this->alias, $hide_clauses, $clauses );
		if ( $where_clause ) {
			$this->whereClause = $where_clause;

			return;
		}


		// If hiding multiple values, format the where clause and return.
		if ( is_array( $this->currentValue ) && count( $this->currentValue ) > 1 ) {
			$this->whereClause = ' AND ( ( ' . implode( ' AND ', $hide_clauses ) . '  ) AND ' . implode( ' OR ', $clauses ) . ') ';

			return;
		}

		// merge arrays and use this where clause when only one hide value is selected.
		$clauses = array_merge( $hide_clauses, $clauses );
		$this->whereClause = ' AND ( ' . implode( ' OR ', $clauses ) . ') ';
	}
}
