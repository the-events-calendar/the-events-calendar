<?php
/**
 * Event_Category_Meta class for taxonomy meta.
 * Handles metadata for terms within the `tribe_events_cat` taxonomy.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors
 */

declare( strict_types=1 );

namespace TEC\Events\Category_Colors;

use InvalidArgumentException;
use Tribe__Events__Main;
use WP_Term;
use WP_Error;

/**
 * Class Event_Category_Meta
 * Provides an object-oriented way to set, retrieve, and delete metadata
 * associated with event categories. It ensures that only valid terms within
 * the `tribe_events_cat` taxonomy can have metadata operations performed on them.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors
 */
class Event_Category_Meta {

	/**
	 * The taxonomy associated with event categories.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $taxonomy = Tribe__Events__Main::TAXONOMY;

	/**
	 * The ID of the term this instance operates on.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected int $term_id;

	/**
	 * Stores pending metadata updates before saving.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected array $pending_meta = [];

	/**
	 * Stores pending metadata deletions before saving.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected array $pending_deletes = [];

	/**
	 * Stores pending metadata updates before saving.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected array $pending_updates = [];

	/**
	 * Constructor.
	 * Ensures the term belongs to `tribe_events_cat` before initializing.
	 *
	 * @since TBD
	 * @throws InvalidArgumentException Thrown when an invalid term ID is provided or the term does not exist.
	 *
	 * @param int $term_id The term ID.
	 */
	public function __construct( int $term_id ) {
		if ( $term_id <= 0 ) {
			throw new InvalidArgumentException( __( 'Invalid term ID.', 'the-events-calendar' ) );
		}

		$term = get_term( $term_id, $this->taxonomy );

		if ( ! $term instanceof WP_Term ) {
			throw new InvalidArgumentException(
			/* translators: %1$d is the term ID, %2$s is the taxonomy name. */
				sprintf( __( 'Term ID %1$d does not exist in taxonomy %2$s.', 'the-events-calendar' ), $term_id, $this->taxonomy )
			);
		}

		$this->term_id = $term_id;
	}

	/**
	 * Sets metadata for the term.
	 *
	 * @since TBD
	 *
	 * @param string $key   The meta key to update.
	 * @param mixed  $value The value to store.
	 *
	 * @return $this Returns the instance for chaining.
	 */
	public function set( string $key, $value ): self {
		$key   = $this->validate_key( $key );
		$value = $this->validate_value( $value );

		// If key or value is invalid, skip but don't break chaining.
		if ( is_wp_error( $key ) || is_wp_error( $value ) ) {
			return $this;
		}

		$this->pending_updates[ $key ] = $value;

		return $this;
	}

	/**
	 * Marks metadata for deletion but does not delete immediately.
	 *
	 * @since TBD
	 *
	 * @param string $key The meta key to delete.
	 *
	 * @return $this|wp_error  Returns the instance for chaining.
	 */
	public function delete( string $key ): self {
		$key = $this->validate_key( $key );

		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$this->pending_deletes[] = $key;

		return $this;
	}

	/**
	 * Save all queued meta updates and deletions.
	 *
	 * @since TBD
	 *
	 * @return $this
	 */
	public function save(): self {
		foreach ( $this->pending_updates as $key => $value ) {
			update_term_meta( $this->term_id, $key, $value );
		}

		foreach ( $this->pending_deletes as $key ) {
			delete_term_meta( $this->term_id, $key );
		}

		// Clear queues after saving.
		$this->pending_updates = [];
		$this->pending_deletes = [];

		return $this;
	}

	/**
	 * Retrieves metadata for the term.
	 *
	 * @since TBD
	 *
	 * @param string|null $key Optional. The meta key to retrieve.
	 *
	 * @return mixed The meta value, or an array of all metadata if no key is provided.
	 */
	public function get( ?string $key = null ) {
		if ( null === $key ) {
			$all_meta = get_term_meta( $this->term_id );

			foreach ( $all_meta as $meta_key => &$value ) {
				$value = $this->normalize_meta( $meta_key, $value );
			}

			return $all_meta;
		}

		$key = $this->validate_key( $key );

		return metadata_exists( 'term', $this->term_id, $key )
			? $this->normalize_meta( $key, get_term_meta( $this->term_id, $key, false ) )
			: null;
	}

	/**
	 * Normalizes meta values to ensure consistency.
	 *
	 * @since TBD
	 *
	 * @param string $key   The meta key.
	 * @param mixed  $value The raw value from get_term_meta().
	 *
	 * @return mixed The normalized value.
	 */
	protected function normalize_meta( string $key, $value ) {
		if ( ! is_array( $value ) ) {
			return is_scalar( $value ) ? (string) $value : $value;
		}

		return count( $value ) > 1 ? $value : ( is_scalar( $value[0] ) ? (string) $value[0] : $value[0] );
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
		 *
		 * @return string The filtered meta key.
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
		 *
		 * @return mixed The filtered meta value.
		 */
		return apply_filters( 'tec_events_category_validate_meta_value', $value, $this->term_id );
	}
}
