<?php
/**
 * Handles the compatibility with the Filter Bar plugin.
 *
 * @since   5.12.1
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

use TEC\Common\StellarWP\Installer\Installer;
use Tribe\Events\Event_Status\Status_Labels;
use Tribe\Events\Filterbar\Views\V2\Filters\Context_Filter;
use Tribe\Events\Event_Status\Event_Meta as Event_Status_Meta;
use WP_Query;
use wpdb;

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
	 * Transient key for caching sold-out event IDs.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $cache_key_soldout_event_ids = 'tribe_filterbar_soldout_event_ids';

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
	 * Value checked for soldout events.
	 *
	 * @since TBD
	 */
	const SOLDOUT = 'soldout';

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
	 * @param Status_Labels $status_labels An instance of the statuses handler.
	 *
	 * @since   5.12.1
	 *
	 */
	public function __construct( Status_Labels $status_labels ) {
		$this->status_labels = $status_labels;
		$name                = $this->status_labels->get_event_status_label();

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
	 * @param string $name The individual name for the individual control (ie radio button).
	 *
	 * @since 5.12.1
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
	 */
	protected function get_values() {
		$events_label_plural = tribe_get_event_label_plural();

		$et_activated = Installer::get()->is_active( 'event-tickets' );

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

		if ( $et_activated ) {

			// only included the sold out filter if ET activated.
			$default_values['soldout'] = [
				// Translators: %s: The plural Events label.
				'name'  => sprintf( _x( 'Hide sold-out %s', 'Sold out label for filter bar to hide sold-out events.', 'the-events-calendar' ), $events_label_plural ),
				'value' => self::SOLDOUT,
			];
		}

		/**
		 * Allow filtering of the event statuses values that show in Filter Bar.
		 *
		 * @param array<string|string> $default_values An array of filter values.
		 *
		 * @since 5.12.1
		 *
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
	 * Modify the query to handle sold-out events filtering.
	 *
	 * @param WP_Query $query The WP Query instance.
	 *
	 * @since TBD
	 */
	protected function pre_get_posts( WP_Query $query ) {
		// Only proceed if we're filtering for sold-out events.
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( ! is_array( $this->currentValue ) && $this->currentValue !== self::SOLDOUT ) {
			return;
		}

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( is_array( $this->currentValue ) && ! in_array( self::SOLDOUT, $this->currentValue ) ) {
			return;
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		// Find events with sold-out tickets using the _stock_status meta key.
		$soldout_events_sql = "
                SELECT DISTINCT event_meta.meta_value as event_id
                FROM {$wpdb->posts} tickets
                INNER JOIN {$wpdb->postmeta} AS stock_status_meta
                    ON tickets.ID = stock_status_meta.post_id
                    AND stock_status_meta.meta_key = '_stock'
                INNER JOIN {$wpdb->postmeta} AS manage_stock_meta
            		ON tickets.ID = manage_stock_meta.post_id
            		AND manage_stock_meta.meta_key = '_manage_stock'
            	INNER JOIN {$wpdb->postmeta} AS capacity_meta
            		ON tickets.ID = capacity_meta.post_id
            		AND capacity_meta.meta_key = '_tribe_ticket_capacity'
                INNER JOIN {$wpdb->postmeta} as event_meta
                    ON tickets.ID = event_meta.post_id
                    AND event_meta.meta_key = '_tec_tickets_commerce_event'
                WHERE
                    tickets.post_type = 'tec_tc_ticket'
                    AND tickets.post_status = 'publish'
                	AND manage_stock_meta.meta_value = 'yes'
					AND CAST(capacity_meta.meta_value as SIGNED) > -1
					AND CAST(stock_status_meta.meta_value as SIGNED) = 0
            ";

		// We always want to get fresh data here, adding caching can create chances that data stale.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$soldout_events = $wpdb->get_col( $soldout_events_sql );

		if ( ! empty( $soldout_events ) ) {
			// If we have sold-out events, exclude them from the query.
			$post__not_in = $query->get( 'post__not_in', [] );

			$post__not_in = array_merge( $post__not_in, $soldout_events );
			$query->set( 'post__not_in', $post__not_in );
		}
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

		// Remove 'soldout' from currentValue since it's handled by pre_get_posts method.
		$current_value = $this->currentValue; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		if ( is_array( $current_value ) ) {
			$current_value = array_diff( $current_value, [ self::SOLDOUT ] );
			$current_value = array_values( $current_value );
		} elseif ( $current_value === self::SOLDOUT ) {
			$current_value = null;
		}

		// If no values left after removing soldout, skip where clause setup.
		if ( empty( $current_value ) ) {
			return;
		}

		if ( is_array( $current_value ) ) {
			$event_status_ids = implode( ',', array_map( 'esc_sql', $current_value ) );
		} else {
			$event_status_ids = "'" . esc_sql( (string) $current_value ) . "'";
		}

		$hide_clauses[] = $wpdb->prepare(
			" {$this->alias}.meta_value NOT IN (%s) ",
			$event_status_ids
		);

		/**
		 * Allow filtering of the event statuses where clause.
		 *
		 * @param string                      $where_clause  The empty where clause to filter.
		 * @param string|array<string|string> $current_value A string or array of the current values selected for the filter.
		 * @param string                      $alias         The table alias that will be used for the postmeta table.
		 * @param array<string|string>        $hide_clauses  The hide clauses on whether to hide canceled and postponed events.
		 * @param array<string|string>        $clauses       The standard clauses to get all events.
		 *
		 * @since 5.12.1
		 *
		 */
		$where_clause = apply_filters( 'tec_event_status_filterbar_where_clause', '', $current_value, $this->alias, $hide_clauses, $clauses );
		if ( $where_clause ) {
			$this->whereClause = $where_clause;

			return;
		}


		// If hiding multiple values, format the where clause and return.
		if ( is_array( $current_value ) && count( $current_value ) > 1 ) {
			$this->whereClause = ' AND ( ( ' . implode( ' AND ', $hide_clauses ) . '  ) AND ' . implode( ' OR ', $clauses ) . ') ';

			return;
		}

		// merge arrays and use this where clause when only one hide value is selected.
		$clauses           = array_merge( $hide_clauses, $clauses );
		$this->whereClause = ' AND ( ' . implode( ' OR ', $clauses ) . ') ';
	}
}
