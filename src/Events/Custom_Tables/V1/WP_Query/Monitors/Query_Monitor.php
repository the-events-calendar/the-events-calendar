<?php
/**
 * Provides method to monitor a WP Query to attach and manage monitors.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Monitors
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Monitors;

use SplObjectStorage;
use TEC\Common\Contracts\Container;
use TEC\Events\Custom_Tables\V1\Traits\With_WP_Query_Introspection;
use TEC\Events\Custom_Tables\V1\WP_Query\Modifiers\WP_Query_Modifier;
use WP_Query;

/**
 * Trait Query_Monitor
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Monitors
 */
trait Query_Monitor {
	use With_WP_Query_Introspection;

	/**
	 * A reference to the DI Container the Monitor will use to build the Modifiers.
	 *
	 * @since 6.0.0
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * An object storage that will map from WP_Query instances to their modifiers, if any.
	 *
	 * @since 6.0.0
	 *
	 * @var SplObjectStorage<WP_Query,array<WP_Query_Modifier>>
	 */
	private $modifiers;

	/**
	 * A flag property to indicate whether the Monitor is enabled or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $enabled = true;

	/**
	 * A flag property to indicate whether the Monitor should keep a reference to
	 * the done Modifiers or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $keep_modifiers_reference = false;

	/**
	 * Whether the implementations have been filtered at least once or not.
	 *
	 * @since 6.0.11
	 *
	 * @var bool
	 */
	private bool $filtered_implementations = false;

	/**
	 * Monitor constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Container|null $container    Either a reference to a specific container, or `null` to use the
	 *                                              global one.
	 */
	public function __construct( Container $container = null ) {
		$this->modifiers = new SplObjectStorage();
		$this->container = $container ?: tribe();
		// By default the monitor will be enabled.
		$this->enabled = true;
	}

	/**
	 * Will filter and retrieve the list of WP_Query_Modifier implementations. Any implementation filters
	 * must be applied before the init hook is completed.
	 *
	 * @since 6.0.5
	 *
	 * @return array<WP_Query_Modifier> List of WP_Query_Modifier implementations.
	 */
	public function get_implementations(): array {
		// Keep running filter until init is finished. Will run one or more times.
		if (
			$this->implementations === null // Starting state.
			|| ! $this->filtered_implementations // The filter was never applied.
			|| doing_action( 'init' ) // It's initializing.
			|| ! did_action( 'init' ) // It's not initialized yet.
		) {
			/**
			 * Filters the Query Modifier implementations that will be used in the Query Monitor parsing.
			 *
			 * @since 6.0.5
			 *
			 * @param array<WP_Query_Modifier>  The list of Query Modifier implementations that will be used in the
			 *                                  query parsing.
			 * @param Query_monitor This instance of the Query Monitor retrieving implementations.
			 */
			$this->implementations = apply_filters( 'tec_events_custom_tables_v1_query_modifier_implementations',
				$this->implementations ?? [],
				$this
			);
			$this->implementations = array_unique( $this->implementations );

			// The filter has been applied at least once.
			$this->filtered_implementations = true;
		}

		return $this->implementations;
	}

	/**
	 * Attaches a Modifier to a WP_Query, if required.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query $query A reference to the WP Query object that is currently running.
	 *
	 *
	 * @return array<WP_Query_Modifier> An array of references to the Modifier instances that attached to the
	 *                                  query instance, if any.
	 */
	public function attach( WP_Query $query = null ) {
		if ( ! $this->enabled ) {
			return [];
		}

		if ( ! $this->applies_to_query( $query ) ) {
			return [];
		}

		$ignore_flag = static::ignore_flag();
		if (
			// A property set on the query.
			! empty( $query->{$ignore_flag} )
			// A query argument set on the query.
			|| ! empty( $query->get( $ignore_flag, false ) )
		) {
			// The query should be ignored, move on.
			return [];
		}

		if ( $this->modifiers->contains( $query ) ) {
			// Already attached Modifiers to this query?
			return $this->modifiers[ $query ];
		}

		$modifiers = [];

		foreach ( $this->get_implementations() as $class_name ) {
			$modifier = $this->container->make( $class_name );
			if ( $modifier instanceof WP_Query_Modifier && $modifier->applies_to( $query ) ) {
				$query->set( 'tribe_remove_date_filters', true );
				$query->tribe_remove_date_filters = true;
				$query->set( 'tribe_include_date_meta', false );
				$query->tribe_include_date_meta = false;
				$modifier->set_query( $query );
				$query->ical_modifier = $modifier;
				$this->register_modifier_for( $modifier, $query );
				$modifiers[] = $modifier;
			} else {
				unset( $modifier );
			}
		}

		return $modifiers;
	}

	/**
	 * Registers the attachment of a Modifier to a `WP_Query` instance.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query_Modifier $modifier A reference to the Modifier instance attached to the `WP_Query` instance.
	 * @param WP_Query          $query    A reference to the `WP_Query` object the modifier is attached to.
	 */
	private function register_modifier_for( WP_Query_Modifier $modifier, WP_Query $query ) {
		if ( ! isset( $this->modifiers[ $query ] ) ) {
			$this->modifiers[ $query ] = [];
		}

		$this->modifiers[ $query ] = array_merge( $this->modifiers[ $query ], [ $modifier ] );
		$modifier_class            = get_class( $modifier );
		add_action( "tec_events_custom_tables_v1_{$modifier_class}_done", $this->drop_modifiers( $query ) );
	}

	/**
	 * Returns a closure that will remove a set of Modifiers from the tracked Modifiers.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query $query The query that will be used as key to remove the modifiers.
	 *
	 * @return Closure A Closure that will remove the modifiers, using the Query as key,
	 *                 from the list of modifiers.
	 */
	private function drop_modifiers( WP_Query $query ) {
		return function () use ( $query ) {
			if ( $this->keep_modifiers_reference ) {
				return;
			}

			if ( ! isset( $this->modifiers[ $query ] ) ) {
				return;
			}
			unset( $this->modifiers[ $query ] );
		};
	}

	/**
	 * Return the number of Queries to which at least one modifier is attached.
	 *
	 * @since 6.0.0
	 *
	 * @return int The number of modifier instances.
	 */
	#[\ReturnTypeWillChange]
	public function count() {
		return $this->modifiers->count();
	}

	/**
	 * Remove instances from the modifiers so it can be garbage collected.
	 *
	 * @since 6.0.0
	 */
	public function detach() {
		while ( $this->modifiers->valid() ) {
			$item = $this->modifiers->current();
			$this->modifiers->next();
			$this->modifiers->detach( $item );
		}
	}

	/**
	 * Returns a reference to the Modifier instance attached to a specific `WP_Query` instance, if any.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query $wp_query A reference to the `WP_Query` object to fetch the Modifier instance for.
	 * @param int      $index    The index of the modifier to return
	 *
	 * @return WP_Query_Modifier|null Either a reference to the Modifier instance attached to the `WP_Query`
	 *                                instance, or `null` if no Modifier is attached to it.
	 */
	public function get_modifier_for_query( WP_Query $wp_query, $index = 0 ) {
		if ( $this->modifiers->contains( $wp_query ) ) {
			return $this->modifiers[ $wp_query ][ $index ];
		}

		return null;
	}

	/**
	 * Disables the Monitor.
	 *
	 * If the Monitor was already disabled, the method will not have any effect.
	 *
	 * @since 6.0.0
	 */
	public function disable() {
		$this->enabled = false;
	}

	/**
	 * Enables the Monitor.
	 *
	 * If the Monitor was already enabled, the method will not have any effect.
	 *
	 * @since 6.0.0
	 */
	public function enable() {
		$this->enabled = true;
	}

	/**
	 * Sets whether the Monitor should keep a reference to "done" Modifiers or not.
	 *
	 * By default, the Monitor will try to remove a reference to a Modifier that has
	 * completed its work, and the Query it modified, to allow for the cascading garbage
	 * collection to happen.
	 * Think of it like a "weak map" implementation built on what we have.
	 * Setting the flat to `true` will force the Monitor to keep references to all the modifiers
	 * and cascading objects referenced by them: use only for testing, please.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $keep_modifiers_reference    Whether the Monitor should keep a reference
	 *                                          to "done" Modifiers or not.
	 */
	public function keep_modifiers_reference( $keep_modifiers_reference ) {
		$this->keep_modifiers_reference = (bool) $keep_modifiers_reference;
	}
}
