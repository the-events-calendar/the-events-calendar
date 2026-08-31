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

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Events\Provisional\Provider as Provisional_Provider;
use TEC\Events\Custom_Tables\V1\Models\Extensions\Occurrence as Occurrence_Extension;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\WP_Query\Provisional\Provider as Provisional_Queries_Provider;

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

		$this->container->register( Provisional_Provider::class );
		$this->container->register( Provisional_Queries_Provider::class );

		$occurrences = Occurrences::table_name( false );
		if ( ! has_filter( "tec_custom_tables_{$occurrences}_model_v1_extensions", [ $this, 'extend_occurrence_model' ] ) ) {
			add_filter( "tec_custom_tables_{$occurrences}_model_v1_extensions", [ $this, 'extend_occurrence_model' ] );
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $this, 'normalize_occurrence_id' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $this, 'normalize_occurrence_id' ] );
		}
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
		remove_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $this, 'normalize_occurrence_id' ] );

		$this->container->make( Provisional_Queries_Provider::class )->unregister();
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
