<?php
/**
 * Provides the date-list migration strategy to the Custom Tables v1 migration.
 *
 * Registered whenever the Custom Tables v1 provider is (NOT gated on the Recurrence
 * feature Controller): the migration runs precisely while the site is not migrated yet,
 * when the Controller — gated on the `ct1_fully_activated` container flag — is inactive.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Migration
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Migration;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Models\Model;
use TEC\Events\Recurrence\Date_Rules;
use TEC\Events\Recurrence\Controller;
use TEC\Events\Recurrence\Engine_Provider;

/**
 * Class Migration_Provider.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Migration
 */
class Migration_Provider extends Service_Provider {
	/**
	 * Registers the migration strategy loader and, during a migration run, the
	 * Occurrence engine the strategy depends on.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		if ( ! has_filter( 'tec_events_custom_tables_v1_migration_strategy', [ $this, 'provide_strategy' ] ) ) {
			/*
			 * Priority 20: Events Calendar Pro's Migration_Strategy_Guide hooks at 10 and,
			 * when active, claims every rules-bearing Event — date-list ones included, which
			 * it routes to its Single Rule strategy (and relates to a Series). This loader
			 * only answers when no earlier filter provided a strategy.
			 */
			add_filter( 'tec_events_custom_tables_v1_migration_strategy', [ $this, 'provide_strategy' ], 20, 3 );
		}

		$phase = $this->container->make( State::class )->get_phase();

		if ( in_array( $phase, [ State::PHASE_PREVIEW_IN_PROGRESS, State::PHASE_MIGRATION_IN_PROGRESS ], true ) ) {
			/*
			 * A migration is running: make the Occurrence engine available NOW, before any
			 * Event or Occurrence model is instantiated in this request. The Model extension
			 * cache locks after `init`: registering the engine later in the request would
			 * silently drop the `rset` and Occurrence recurrence fields the migration of
			 * date-list Events depends on. Only storage hooks are needed; runtime
			 * query and editing ownership stays with the feature Controller.
			 */
			$this->ensure_engine();
		}
	}

	/**
	 * Unregisters the strategy loader.
	 *
	 * Activated runtime storage belongs to the feature Controller. Before activation,
	 * release the storage hooks this provider registered.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'tec_events_custom_tables_v1_migration_strategy', [ $this, 'provide_strategy' ], 20 );
		if ( ! $this->container->getVar( 'tec_events_recurrence_fully_activated', false ) && $this->container->isBound( Engine_Provider::class ) ) {
			$this->container->make( Engine_Provider::class )->unregister_storage();
			Model::reset_extensions();
		}
	}

	/**
	 * Provides the migration strategy for Events whose recurrence is a list of explicit
	 * dates.
	 *
	 * @since TBD
	 *
	 * @param Strategy_Interface|null $strategy The strategy provided by earlier filters,
	 *                                          returned unchanged when not `null`.
	 * @param int|null                $post_id  The post ID of the Event to migrate.
	 * @param bool                    $dry_run  Whether the migration is running in dry-run
	 *                                          mode or not.
	 *
	 * @return Strategy_Interface|null The date-list strategy for a dates-only recurring
	 *                                 Event, the input value otherwise.
	 */
	public function provide_strategy( $strategy = null, $post_id = null, $dry_run = false ) {
		if ( null !== $strategy ) {
			// An earlier filter (e.g. Events Calendar Pro) already chose: respect it.
			return $strategy;
		}

		if ( empty( $post_id ) ) {
			return $strategy;
		}

		$recurrence_meta = get_post_meta( (int) $post_id, '_EventRecurrence', true );

		if ( ! Date_Rules::is_dates_only_meta( $recurrence_meta ) ) {
			/*
			 * Plain single Events fall back to the default Single Event strategy;
			 * rule-based recurring Events keep failing through it with the message
			 * pointing at Events Calendar Pro.
			 */
			return $strategy;
		}

		if ( ! $this->ensure_engine() ) {
			return $strategy;
		}

		return new Date_Rules_Migration_Strategy( (int) $post_id, (bool) $dry_run );
	}

	/**
	 * Ensures the Occurrence engine pieces the strategy depends on are registered.
	 *
	 * The engine derives the dates RSET while the Event data is built from the post and
	 * expands it into Occurrence rows with stable IDs; both `Engine_Provider::register_storage()`
	 * and this method are idempotent.
	 *
	 * @since TBD
	 *
	 * @return bool Whether storage was registered under the current ownership governor.
	 */
	public function ensure_engine(): bool {
		if ( ! Controller::can_provide_storage() ) {
			return false;
		}

		if ( ! $this->container->isBound( Engine_Provider::class ) ) {
			// Re-binding an existing singleton would drop the resolved instance.
			$this->container->singleton( Engine_Provider::class );
		}

		$this->container->make( Engine_Provider::class )->register_storage();

		/*
		 * The Model extension cache is filtered once and locked after `init`: a model
		 * instantiated before the engine registered would keep the un-extended field
		 * set for the rest of the request. Reset so the extensions re-apply.
		 */
		Model::reset_extensions();

		return true;
	}
}
