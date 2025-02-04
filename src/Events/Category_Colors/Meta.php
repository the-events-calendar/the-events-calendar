<?php
/** Meta class for taxonomy meta.
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
 * Class Meta
 *
 * Handles metadata for terms within the `tribe_events_cat` taxonomy.
 *
 * This class provides an object-oriented way to set, retrieve, and delete metadata
 * associated with event categories. It ensures that only valid terms within the
 * `tribe_events_cat` taxonomy can have metadata operations performed on them.
 *
 * @since TBD
 */
class Meta {

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
	 * Checks if the instance is valid.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error True if valid, WP_Error if not.
	 */
	public function is_valid() {
		if ( $this->term_id <= 0 ) {
			return new WP_Error( 'invalid_term', __( 'Invalid term ID or term does not exist.', 'the-events-calendar' ) );
		}

		return true;
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

		return $key;
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

		return $value;
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
		if ( $this->is_valid() instanceof WP_Error ) {
			return $this->is_valid();
		}

		if ( null === $key ) {
			$all_meta = get_term_meta( $this->term_id );

			return array_map( fn( $values ) => $values[0] ?? null, $all_meta );
		}

		$key = $this->validate_key( $key );
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$value = get_term_meta( $this->term_id, $key, true );

		return ( '' !== $value ) ? $value : null;
	}

	public function set( string $key, $value ) {
		if ( $this->is_valid() instanceof WP_Error ) {
			return $this->is_valid();
		}

		$key   = $this->validate_key( $key );
		$value = $this->validate_value( $value );

		if ( is_wp_error( $key ) || is_wp_error( $value ) ) {
			return is_wp_error( $key ) ? $key : $value;
		}

		update_term_meta( $this->term_id, $key, $value );

		return $this;
	}

	public function delete( ?string $key = null ) {
		if ( $this->is_valid() instanceof WP_Error ) {
			return $this->is_valid();
		}

		if ( null === $key ) {
			$meta_keys = array_keys( get_term_meta( $this->term_id ) );
			foreach ( $meta_keys as $meta_key ) {
				delete_term_meta( $this->term_id, $meta_key );
			}

			return $this;
		}

		$key = $this->validate_key( $key );
		if ( is_wp_error( $key ) ) {
			return $key; // Stop chaining here
		}

		delete_term_meta( $this->term_id, $key );

		return $this;
	}
}
