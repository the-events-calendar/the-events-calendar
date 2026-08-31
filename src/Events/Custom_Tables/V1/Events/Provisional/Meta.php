<?php
/**
 * Updates event metadata using the real post ID.
 *
 * @since 7.4.1
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Provisional
 */

namespace TEC\Events\Custom_Tables\V1\Events\Provisional;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;

/**
 * Class Meta
 *
 * @since 7.4.1
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Provisional
 */
class Meta {
	/**
	 * Correctly updates the post metadata of an event occurrence.
	 * Third-party plugins might try to update the post meta by using the provisional post ID.
	 * This method preempts that by updating the metadata before using the real post ID.
	 *
	 * @see update_metadata() wp-includes/meta.php:182
	 *
	 * @since 7.4.1
	 *
	 * @param null|bool $check      Whether to allow updating metadata for the given type.
	 * @param int       $object_id  ID of the object metadata is for.
	 * @param string    $meta_key   Metadata key.
	 * @param mixed     $meta_value Metadata value. Must be serializable if non-scalar.
	 * @param mixed     $prev_value Optional. Previous value to check before updating.
	 *                              If specified, only update existing metadata entries with
	 *                              this value. Otherwise, update all entries.
	 *
	 * @return bool|int|mixed|null
	 */
	public function update_metadata( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( $check !== null ) {
			return $check;
		}

		// Bail if not provisional post ID.
		if ( ! tribe( Provisional_Post::class )->is_provisional_post_id( $object_id ) ) {
			return $check;
		}

		remove_filter( 'update_post_metadata', [ $this, 'update_metadata' ], 0 );

		$good_id = Occurrence::normalize_id( $object_id );
		$result  = update_post_meta( $good_id, $meta_key, $meta_value, $prev_value );

		add_filter( 'update_post_metadata', [ $this, 'update_metadata' ], 0, 5 );

		return $result;
	}
}
