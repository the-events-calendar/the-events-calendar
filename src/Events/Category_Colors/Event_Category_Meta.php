<?php
/**
 * Event_Category_Meta class for taxonomy meta.
 * Handles metadata for terms within the `tribe_events_cat` taxonomy.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors
 */

declare( strict_types=1 );

namespace TEC\Events\Category_Colors;

use InvalidArgumentException;
use Tribe__Events__Main;
use WP_Term;

/**
 * Class Event_Category_Meta
 * Provides an object-oriented way to set, retrieve, and delete metadata
 * associated with event categories. It ensures that only valid terms within
 * the `tribe_events_cat` taxonomy can have metadata operations performed on them.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors
 */
class Event_Category_Meta {

	/**
	 * The taxonomy associated with event categories.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	protected string $taxonomy = Tribe__Events__Main::TAXONOMY;

	/**
	 * The ID of the term this instance operates on.
	 *
	 * @since 6.14.0
	 *
	 * @var int
	 */
	protected int $term_id;

	/**
	 * Stores pending metadata deletions before saving.
	 *
	 * @since 6.14.0
	 *
	 * @var array
	 */
	protected array $pending_deletes = [];

	/**
	 * Stores pending metadata updates before saving.
	 *
	 * @since 6.14.0
	 *
	 * @var array
	 */
	protected array $pending_updates = [];

	/**
	 * Sets the term ID for the instance, ensuring it exists within the taxonomy.
	 *
	 * @since 6.14.0
	 *
	 * @throws InvalidArgumentException If the term ID is invalid or does not exist in the taxonomy.
	 *
	 * @param int $term_id The term ID to be set.
	 *
	 * @return self Returns the current instance for method chaining.
	 */
	public function set_term( int $term_id ): self {
		if ( $term_id <= 0 ) {
			throw new InvalidArgumentException( __( 'Invalid term ID.', 'the-events-calendar' ) );
		}

		$term = get_term( $term_id, $this->taxonomy );

		if ( ! $term instanceof WP_Term ) {
			throw new InvalidArgumentException(
			/* translators: 1: term ID, 2: taxonomy name */
				sprintf( __( 'Term ID %1$d does not exist in taxonomy %2$s.', 'the-events-calendar' ), $term_id, $this->taxonomy )
			);
		}

		$this->term_id = $term_id;

		return $this;
	}

	/**
	 * Sanitizes and validates a meta key.
	 *
	 * @since 6.14.0
	 *
	 * @throws InvalidArgumentException If the key is invalid.
	 *
	 * @param string $key The meta key to sanitize and validate.
	 *
	 * @return string The sanitized and validated key.
	 */
	protected function sanitize_and_validate_key( string $key ): string {
		$key = strtolower( trim( $key ) );

		if ( '' === $key ) {
			throw new InvalidArgumentException( __( 'Meta key cannot be empty.', 'the-events-calendar' ) );
		}

		/**
		 * Filter the validated meta key before it is used.
		 *
		 * @since 6.14.0
		 *
		 * @param string $key     The sanitized meta key.
		 * @param int    $term_id The term ID the meta key belongs to.
		 *
		 * @return string The filtered meta key.
		 */
		return apply_filters( 'tec_events_category_validate_meta_key', $key, $this->term_id );
	}

	/**
	 * Retrieves metadata for the term.
	 *
	 * @since 6.14.0
	 *
	 * @throws InvalidArgumentException If the key is invalid.
	 *
	 * @param string|null $key Optional. The meta key to retrieve.
	 *
	 * @return mixed The meta value, or an array of all metadata if no key is provided.
	 */
	public function get( ?string $key = '' ) {
		$this->ensure_term_is_set();
		if ( empty( $key ) ) {
			$all_meta = get_term_meta( $this->term_id );

			foreach ( $all_meta as $meta_key => &$value ) {
				$value = $this->normalize_meta( $value );
			}

			return $all_meta;
		}

		$key = $this->sanitize_and_validate_key( $key );

		return metadata_exists( 'term', $this->term_id, $key )
			? $this->normalize_meta( get_term_meta( $this->term_id, $key, true ) )
			: '';
	}

	/**
	 * Sets metadata for the term.
	 *
	 * @since 6.14.0
	 *
	 * @throws InvalidArgumentException If the key or value is invalid.
	 *
	 * @param string $key   The meta key to update.
	 * @param mixed  $value The value to store.
	 *
	 * @return self
	 */
	public function set( string $key, $value ): self {
		$this->ensure_term_is_set();
		$key = $this->sanitize_and_validate_key( $key );

		// Ensure we're not setting term meta for a shared term.
		if ( wp_term_is_shared( $this->term_id ) ) {
			throw new InvalidArgumentException(
				sprintf( "Meta cannot be added to term ID %d because it's shared between taxonomies.", $this->term_id )
			);
		}

		$value = $this->validate_value( $value );

		$this->pending_updates[ $key ] = $value;

		return $this;
	}

	/**
	 * Marks metadata for deletion but does not delete immediately.
	 *
	 * @since 6.14.0
	 *
	 * @throws InvalidArgumentException If the key is invalid.
	 *
	 * @param string $key The meta key to delete.
	 *
	 * @return self
	 */
	public function delete( string $key ): self {
		$this->ensure_term_is_set();
		$key = $this->sanitize_and_validate_key( $key );

		$this->pending_deletes[] = $key;

		// Remove from pending updates in case it was set before.
		unset( $this->pending_updates[ $key ] );

		return $this;
	}

	/**
	 * Save all queued meta updates and deletions.
	 *
	 * @since 6.14.0
	 *
	 * @return $this
	 */
	public function save(): self {
		$this->ensure_term_is_set();
		foreach ( $this->pending_deletes as $key ) {
			delete_term_meta( $this->term_id, $key );
		}

		// Update meta and remove from pending deletes in case it was set before.
		foreach ( $this->pending_updates as $key => $value ) {
			update_term_meta( $this->term_id, $key, $value );
			unset( $this->pending_deletes[ $key ] );
		}

		// Clear queues after saving.
		$this->pending_updates = [];
		$this->pending_deletes = [];

		return $this;
	}

	/**
	 * Ensures meta values are consistently formatted.
	 *
	 * Due to a bug in WordPress 6.7.2, taxonomy meta values do not retain their original data type.
	 * For example, inserting an integer `0` returns the string `'0'`, and inserting `false` returns an empty string `''`.
	 * This method normalizes only booleans and integers, leaving all other data types unchanged.
	 *
	 * @since 6.14.0
	 *
	 * @param mixed $value The raw value retrieved from get_term_meta().
	 *
	 * @return mixed The normalized meta value.
	 */
	protected function normalize_meta( $value ) {
		if ( null === $value ) {
			return '';
		}

		if ( is_bool( $value ) || is_numeric( $value ) ) {
			return (string) $value;
		}

		return $value;
	}

	/**
	 * Validates a meta value.
	 *
	 * @since 6.14.0
	 *
	 * @throws InvalidArgumentException If the value is invalid.
	 *
	 * @param mixed $value The meta value.
	 *
	 * @return mixed The validated value.
	 */
	protected function validate_value( $value ) {
		/**
		 * Filter the meta value before it is saved.
		 *
		 * Developers can throw an InvalidArgumentException to indicate validation failure.
		 *
		 * @since 6.14.0
		 *
		 * @throws InvalidArgumentException If the value is invalid.
		 *
		 * @param int   $term_id The term ID the meta value belongs to.
		 * @param mixed $value   The sanitized meta value.
		 *
		 * @return mixed The validated meta value.
		 */
		return apply_filters( 'tec_events_category_validate_meta_value', $value, $this->term_id );
	}

	/**
	 * Ensures that a term ID is set before performing operations.
	 *
	 * @since 6.14.0
	 *
	 * @throws InvalidArgumentException If `set_term()` has not been called before using methods that require it.
	 */
	protected function ensure_term_is_set(): void {
		if ( isset( $this->term_id ) ) {
			return;
		}
		throw new InvalidArgumentException( __( 'set_term() must be called before using this method.', 'the-events-calendar' ) );
	}
}
