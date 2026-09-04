<?php
/**
 * Registers the Occurrence engine pieces of the Recurrence feature.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use Generator;
use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Events\Provisional\Provider as Provisional_Provider;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Extensions\Event as Event_Extension;
use TEC\Events\Custom_Tables\V1\Models\Extensions\Occurrence as Occurrence_Extension;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\WP_Query\Provisional\Provider as Provisional_Queries_Provider;
use TEC\Events\Recurrence\Updates\Single_Occurrence_Update;

/**
 * Class Engine_Provider.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Engine_Provider extends Service_Provider {
	/**
	 * Registers the Occurrence engine: the provisional post ID system, the query
	 * integration, and the Occurrence model extension.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		/*
		 * Registered directly (not through the container provider registry) so a
		 * register/unregister/register cycle re-attaches the hooks; both providers
		 * register idempotently.
		 */
		$this->container->singleton( Provisional_Provider::class );
		$this->container->make( Provisional_Provider::class )->register();
		$this->container->singleton( Provisional_Queries_Provider::class );
		$this->container->make( Provisional_Queries_Provider::class )->register();

		// Saving an Occurrence edit screen moves that Occurrence only.
		if ( ! $this->container->isBound( Single_Occurrence_Update::class ) ) {
			$this->container->singleton( Single_Occurrence_Update::class );
		}
		$this->container->make( Single_Occurrence_Update::class )->register();

		/*
		 * By Day Views (e.g. Month) receive provisional post IDs when the engine is
		 * active: swap the base compatibility for the provisional-aware one.
		 */
		$this->container->singleton(
			\TEC\Events\Custom_Tables\V1\Views\V2\By_Day_View_Compatibility::class,
			\TEC\Events\Custom_Tables\V1\Views\V2\Provisional_By_Day_View_Compatibility::class
		);

		$occurrences = Occurrences::table_name( false );
		if ( ! has_filter( "tec_custom_tables_{$occurrences}_model_v1_extensions", [ $this, 'extend_occurrence_model' ] ) ) {
			add_filter( "tec_custom_tables_{$occurrences}_model_v1_extensions", [ $this, 'extend_occurrence_model' ] );
		}

		$events = Events::table_name( false );
		if ( ! has_filter( "tec_custom_tables_{$events}_model_v1_extensions", [ $this, 'extend_event_model' ] ) ) {
			add_filter( "tec_custom_tables_{$events}_model_v1_extensions", [ $this, 'extend_event_model' ] );
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $this, 'normalize_occurrence_id' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $this, 'normalize_occurrence_id' ] );
		}

		/*
		 * The dates generator runs before (9) any rule engine (Events Calendar Pro hooks
		 * at 10): dates-only RSETs are expanded here, rule-based ones are left for the
		 * rule engine. The freeze generator runs last (100): a rule-based RSET no rule
		 * engine claimed keeps its existing Occurrences instead of collapsing.
		 */
		if ( ! has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $this, 'get_dates_generator' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $this, 'get_dates_generator' ], 9, 2 );
		}
		if ( ! has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $this, 'get_freeze_generator' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $this, 'get_freeze_generator' ], 100, 2 );
		}

		if ( ! has_filter( 'tec_custom_tables_v1_get_occurrence_match', [ $this, 'get_occurrence_match' ] ) ) {
			add_filter( 'tec_custom_tables_v1_get_occurrence_match', [ $this, 'get_occurrence_match' ], 9, 3 );
		}

		if ( ! has_action( 'tec_events_custom_tables_v1_after_save_occurrences', [ $this, 'prune_occurrences_by_sequence' ] ) ) {
			add_action( 'tec_events_custom_tables_v1_after_save_occurrences', [ $this, 'prune_occurrences_by_sequence' ] );
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $this, 'derive_dates_rset_from_meta' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $this, 'derive_dates_rset_from_meta' ], 10, 2 );
		}
	}

	/**
	 * Derives the Event RSET from a dates-only `_EventRecurrence` meta value.
	 *
	 * Keeps the RSET in sync when the Event dates change through flows that do not go
	 * through the Dates_Service (e.g. a classic editor date edit). A rule engine
	 * providing its own derivation (Events Calendar Pro) wins: when the RSET was already
	 * derived by an earlier filter, or the meta contains anything beyond date rules, the
	 * data is left untouched.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed>|mixed $data    The Event data, as derived from the post.
	 * @param int|null                  $post_id The Event post ID.
	 *
	 * @return array<string,mixed>|mixed The Event data, the `rset` entry derived when applicable.
	 */
	public function derive_dates_rset_from_meta( $data, $post_id = null ) {
		if ( ! is_array( $data ) || empty( $post_id ) || ! empty( $data['rset'] ) ) {
			return $data;
		}

		$recurrence_meta = get_post_meta( (int) $post_id, '_EventRecurrence', true );

		if ( ! Date_Rules::is_dates_only_meta( $recurrence_meta ) ) {
			return $data;
		}

		try {
			$timezone    = new \DateTimeZone( (string) ( $data['timezone'] ?? 'UTC' ) );
			$event_start = new \DateTimeImmutable( (string) $data['start_date'], $timezone );
			$event_end   = new \DateTimeImmutable( (string) $data['end_date'], $timezone );
		} catch ( \Exception $e ) {
			return $data;
		}

		$periods = Date_Rules::to_periods( $recurrence_meta, $event_start, $event_end, $timezone );

		if ( null === $periods ) {
			return $data;
		}

		$data['rset'] = Dates::serialize( $event_start, $event_end, $periods );

		return $data;
	}

	/**
	 * Provides the Generator expanding a dates-only RSET into Occurrences.
	 *
	 * @since TBD
	 *
	 * @param Generator|null $generator A reference to the Generator provided by previous
	 *                                  filters; when not `null` it will not be replaced.
	 * @param Event|null     $event     A reference to the Event model Occurrences are being
	 *                                  generated for.
	 *
	 * @return Generator|null Either the dates Generator, or the input value.
	 */
	public function get_dates_generator( ?Generator $generator = null, ?Event $event = null ): ?Generator {
		if ( null !== $generator || ! $event instanceof Event ) {
			return $generator;
		}

		return $this->container->make( Dates_Generator::class )->get_occurrences_generator( $event );
	}

	/**
	 * Provides the Generator freezing the Occurrences of a rule-based RSET no rule engine claimed.
	 *
	 * @since TBD
	 *
	 * @param Generator|null $generator A reference to the Generator provided by previous
	 *                                  filters; when not `null` it will not be replaced.
	 * @param Event|null     $event     A reference to the Event model Occurrences are being
	 *                                  generated for.
	 *
	 * @return Generator|null Either the freeze Generator, or the input value.
	 */
	public function get_freeze_generator( ?Generator $generator = null, ?Event $event = null ): ?Generator {
		if ( null !== $generator || ! $event instanceof Event ) {
			return $generator;
		}

		if ( '' === trim( (string) $event->rset ) ) {
			// No RSET: the default single Occurrence logic applies.
			return $generator;
		}

		/**
		 * Fires when the Occurrences of an Event with a rule-based RSET are preserved
		 * because no plugin providing a rule engine is active.
		 *
		 * @since TBD
		 *
		 * @param int $post_id The Event post ID.
		 */
		do_action( 'tec_events_recurrence_rules_frozen', (int) $event->post_id );

		return $this->container->make( Dates_Generator::class )->get_freeze_generator( $event );
	}

	/**
	 * Filters the Occurrence match to return one matched by dates and post ID.
	 *
	 * @since TBD
	 *
	 * @param Occurrence|null $occurrence Either a reference to an existing, matching, Occurrence
	 *                                    or `null`.
	 * @param Occurrence|null $result     A reference to the Occurrence model instance that will be
	 *                                    inserted if a matching Occurrence cannot be found.
	 * @param int|null        $post_id    The post ID of the Event the match is being searched for.
	 *
	 * @return Occurrence|null Either the reference to an existing Occurrence matching the one
	 *                         that should be inserted, or `null` to indicate none was found.
	 */
	public function get_occurrence_match( ?Occurrence $occurrence = null, ?Occurrence $result = null, ?int $post_id = null ): ?Occurrence {
		if ( ! $result instanceof Occurrence || empty( $post_id ) ) {
			return $occurrence;
		}

		return $this->container->make( Occurrences_Maintenance::class )
			->get_occurrence_match( $occurrence, $result, (int) $post_id );
	}

	/**
	 * Prunes the Occurrences of an Event left behind by a previous sequence.
	 *
	 * @since TBD
	 *
	 * @param int|mixed $post_id The ID of the Event post the Occurrences are being saved for.
	 *
	 * @return void
	 */
	public function prune_occurrences_by_sequence( $post_id ): void {
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		$this->container->make( Occurrences_Maintenance::class )->prune_occurrences( (int) $post_id );
	}

	/**
	 * Unregisters the filters managed by the provider.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$occurrences = Occurrences::table_name( false );
		remove_filter( "tec_custom_tables_{$occurrences}_model_v1_extensions", [ $this, 'extend_occurrence_model' ] );
		$events = Events::table_name( false );
		remove_filter( "tec_custom_tables_{$events}_model_v1_extensions", [ $this, 'extend_event_model' ] );
		remove_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $this, 'normalize_occurrence_id' ] );
		remove_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $this, 'get_dates_generator' ], 9 );
		remove_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $this, 'get_freeze_generator' ], 100 );
		remove_filter( 'tec_custom_tables_v1_get_occurrence_match', [ $this, 'get_occurrence_match' ], 9 );
		remove_action( 'tec_events_custom_tables_v1_after_save_occurrences', [ $this, 'prune_occurrences_by_sequence' ] );
		remove_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $this, 'derive_dates_rset_from_meta' ] );

		// Restore the base By Day compatibility: it expects real post IDs again.
		$this->container->singleton(
			\TEC\Events\Custom_Tables\V1\Views\V2\By_Day_View_Compatibility::class,
			\TEC\Events\Custom_Tables\V1\Views\V2\By_Day_View_Compatibility::class
		);

		$this->container->make( Provisional_Queries_Provider::class )->unregister();
		$this->container->make( Provisional_Provider::class )->unregister();

		if ( $this->container->isBound( Single_Occurrence_Update::class ) ) {
			$this->container->make( Single_Occurrence_Update::class )->unregister();
		}
	}

	/**
	 * Extends the Event base model to add the `rset` field.
	 *
	 * @since TBD
	 *
	 * @param array<string,array<string,mixed>> $extensions A map of the current Model
	 *                                                      extensions.
	 *
	 * @return array<string,array<string,mixed>> The filtered extensions map.
	 */
	public function extend_event_model( array $extensions = [] ): array {
		return $this->container->make( Event_Extension::class )->extend( $extensions );
	}

	/**
	 * Extends the Occurrence base model to add the fields backing multi-Occurrence Events.
	 *
	 * @since TBD
	 *
	 * @param array<string,array<string,mixed>> $extensions A map of the current Model
	 *                                                      extensions.
	 *
	 * @return array<string,array<string,mixed>> The filtered extensions map.
	 */
	public function extend_occurrence_model( array $extensions = [] ): array {
		return $this->container->make( Occurrence_Extension::class )->extend( $extensions );
	}

	/**
	 * Normalizes an Occurrence post ID taking Provisional Post IDs into account.
	 *
	 * @since TBD
	 *
	 * @param int|mixed $id The Occurrence post ID to normalize.
	 *
	 * @return int|mixed The normalized Occurrence post ID.
	 */
	public function normalize_occurrence_id( $id ) {
		if ( ! is_numeric( $id ) ) {
			return $id;
		}

		return $this->container->make( Occurrence_Extension::class )->normalize_occurrence_post_id( (int) $id );
	}
}
