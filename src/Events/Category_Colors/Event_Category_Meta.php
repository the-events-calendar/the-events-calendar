<?php
/** Event_Category_Meta class for taxonomy meta.
 *
 * @since TBD
 */

declare( strict_types=1 );

namespace TEC\Events\Category_Colors;

use InvalidArgumentException;
use Tribe__Events__Main;
use WP_Term;
use WP_Error;

/**
 * Class Event_Category_Meta
 *
 * Handles metadata for terms within the `tribe_events_cat` taxonomy.
 *
 * This class provides an object-oriented way to set, retrieve, and delete metadata
 * associated with event categories. It ensures that only valid terms within the
 * `tribe_events_cat` taxonomy can have metadata operations performed on them.
 *
 * @since TBD
 */
class Event_Category_Meta {

	/**
	 * The taxonomy associated with event categories.
	 *
	 * This class only operates on terms within this taxonomy.
	 *
	 * @since TBD
	 * @var string
	 */
	protected string $taxonomy = Tribe__Events__Main::TAXONOMY;

	/**
	 * The ID of the term this instance operates on.
	 *
	 * Ensures that metadata operations are only performed on a valid term.
	 *
	 * @since TBD
	 * @var int
	 */
	protected int $term_id;

	/**
	 * Constructor.
	 *
	 * Ensures the term belongs to `tribe_events_cat` before initializing.
	 *
	 * @since TBD
	 *
	 * @param int $term_id The term ID.
	 *
	 * @throws InvalidArgumentException Thrown when an invalid term ID, or it does not exist in the taxonomy.
	 */
	public function __construct( int $term_id ) {
		if ( $term_id <= 0 ) {
			throw new InvalidArgumentException( __( 'Invalid term ID.', 'the-events-calendar' ) );
		}

		$term = get_term( $term_id, $this->taxonomy );

		if ( ! $term instanceof WP_Term || is_wp_error( $term ) ) {
			throw new InvalidArgumentException(
			/* translators: %1$d is the term ID, %2$s is the taxonomy name. */
				sprintf( __( 'Term ID %1$d does not exist in taxonomy %2$s.', 'the-events-calendar' ), $term_id, $this->taxonomy )
			);
		}

		$this->term_id = $term_id;
	}

	/**
	 * Validates a meta key.
	 *
	 * @since TBD
	 *
	 * @param string $key The meta key.
	 *
	 * @return string|WP_Error The sanitized key or WP_Error if invalid.
	 */
	protected function validate_key( string $key ) {
		$key = strtolower( trim( $key ) );

		if ( '' === $key ) {
			return new WP_Error( 'invalid_key', __( 'Meta key cannot be empty.', 'the-events-calendar' ) );
		}

		/**
		 * Filter the validated meta key before it is used.
		 *
		 * @since TBD
		 *
		 * @param string $key     The sanitized meta key.
		 * @param int    $term_id The term ID the meta key belongs to.
		 */
		return apply_filters( 'tec_events_category_validate_meta_key', $key, $this->term_id );
	}

	/**
	 * Validates a meta value.
	 *
	 * @since TBD
	 *
	 * @param mixed $value The meta value.
	 *
	 * @return mixed|WP_Error The validated value or WP_Error if invalid.
	 */
	protected function validate_value( $value ) {
		if ( null === $value ) {
			return new WP_Error( 'invalid_value', __( 'Meta value cannot be null.', 'the-events-calendar' ) );
		}

		/**
		 * Filter the meta value before it is saved.
		 *
		 * @since TBD
		 *
		 * @param mixed $value   The sanitized meta value.
		 * @param int   $term_id The term ID the meta value belongs to.
		 */
		return apply_filters( 'tec_events_category_validate_meta_value', $value, $this->term_id );
	}

	/**
	 * Gets metadata for the term.
	 *
	 * @since TBD
	 *
	 * @param string|null $key Optional. The meta key to retrieve.
	 *
	 * @return mixed The meta value, or WP_Error if invalid.
	 */
	public function get( ?string $key = null ) {
		if ( null === $key ) {
			$all_meta = $this->get_meta();

			return array_map(
				function ( $values ) {
					return is_array( $values )
						&& isset( $values[0] ) ? $values[0] : $values;
				},
				$all_meta
			);
		}

		$key = $this->validate_key( $key );
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$value = $this->get_meta( $key );

		return ( '' !== $value ) ? $value : null;
	}

	/**
	 * Sets metadata for the term.
	 *
	 * @since TBD
	 *
	 * @param string $key   The meta key to update.
	 * @param mixed  $value The value to store.
	 *
	 * @return $this|WP_Error Returns the instance for chaining or WP_Error if invalid.
	 */
	public function set( string $key, $value ) {
		$key   = $this->validate_key( $key );
		$value = $this->validate_value( $value );

		if ( is_wp_error( $key ) || is_wp_error( $value ) ) {
			return is_wp_error( $key ) ? $key : $value;
		}

		update_term_meta( $this->term_id, $key, $value );

		/**
		 * Fires after metadata has been updated.
		 *
		 * @since TBD
		 *
		 * @param int    $term_id The term ID where metadata was set.
		 * @param string $key     The meta key that was updated.
		 * @param mixed  $value   The new value that was stored.
		 */
		do_action( 'tec_events_category_set_meta', $this->term_id, $key, $value );

		return $this;
	}

	/**
	 * Deletes metadata for the term.
	 *
	 * @since TBD
	 *
	 * @param string|null $key Optional. The meta key to delete.
	 *
	 * @return $this|WP_Error Returns the instance for chaining or WP_Error if invalid.
	 */
	public function delete( ?string $key = null ) {
		$deleted_keys = [];

		if ( null === $key ) {
			$meta_keys = array_keys( $this->get_meta() );
			foreach ( $meta_keys as $meta_key ) {
				if ( delete_term_meta( $this->term_id, $meta_key ) ) {
					$deleted_keys[] = $meta_key;
				}
			}
		}

		if ( null !== $key ) {
			$key = $this->validate_key( $key );
			if ( is_wp_error( $key ) ) {
				return $key;
			}

			if ( delete_term_meta( $this->term_id, $key ) ) {
				$deleted_keys[] = $key;
			}
		}

		/**
		 * Fires after one or more metadata keys have been deleted.
		 *
		 * @since TBD
		 *
		 * @param int      $term_id The term ID where metadata was deleted.
		 * @param string[] $keys    The meta keys that were deleted.
		 */
		if ( ! empty( $deleted_keys ) ) {
			do_action( 'tec_events_category_delete_meta', $this->term_id, $deleted_keys );
		}

		return $this;
	}

	/**
	 * Retrieves metadata for the term, with a filter hook for extensibility.
	 *
	 * @since TBD
	 *
	 * @param string|null $key Optional. The meta key to retrieve.
	 *
	 * @return mixed The meta value or an array of all metadata if no key is provided.
	 */
	public function get_meta( ?string $key = null ) {
		if ( null === $key ) {
			$all_meta = get_term_meta( $this->term_id );

			$filtered_meta = array_map( fn( $values ) => $values[0] ?? null, $all_meta );

			/**
			 * Filter all metadata before returning.
			 *
			 * @since TBD
			 *
			 * @param array $filtered_meta The retrieved metadata.
			 * @param int   $term_id       The term ID.
			 */
			return apply_filters( 'tec_events_category_get_meta_all', $filtered_meta, $this->term_id );
		}

		$value = get_term_meta( $this->term_id, $key, true );

		/**
		 * Filter a specific meta key value before returning.
		 *
		 * @since TBD
		 *
		 * @param mixed  $value   The retrieved metadata value.
		 * @param string $key     The meta key being retrieved.
		 * @param int    $term_id The term ID.
		 */
		return apply_filters( 'tec_events_category_get_meta', ( '' !== $value ? $value : null ), $key, $this->term_id );
	}

}
