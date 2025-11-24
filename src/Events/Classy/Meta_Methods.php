<?php
/**
 * Provides common methods to manipulate meta data in the context of the Classy application.
 *
 * @since TBD
 *
 * @package TEC\Events\Classy;
 */

namespace TEC\Events\Classy;

use WP_Post_Type;

/**
 * Trait Meta_Methods.
 *
 * @since TBD
 *
 * @package TEC\Events\Classy;
 */
trait Meta_Methods {
	use Supported_Post_Types;

	/**
	 * Callback for determining if a user can edit an event meta field.
	 *
	 * This method is added to the `auth_{$object_type}_meta_{$meta_key}_for_{$object_subtype}` filter,
	 * which will be evaulated as `auth_post_meta_{$meta_key}_for_tribe_events`.
	 *
	 * @since TBD
	 *
	 * @param bool   $allowed   Whether the user can add the object meta.
	 * @param string $meta_key  The meta key.
	 * @param int    $object_id Object ID.
	 * @param int    $user_id   User ID.
	 *
	 * @return bool Whether the user can edit the object meta.
	 * @see map_meta_cap()
	 */
	public function user_can_edit_meta( $allowed, $meta_key, $object_id, $user_id ): bool {
		// Ensure $allowed is a boolean.
		$allowed = (bool) $allowed;

		// Ensure we are only checking our known meta keys.
		if ( ! array_key_exists( $meta_key, self::META ) ) {
			return $allowed;
		}

		// Get the post type object for the given object ID.
		$post_type_object = get_post_type_object( get_post_type( $object_id ) );
		if ( ! $post_type_object instanceof WP_Post_Type ) {
			return $allowed;
		}

		// Validate that the user can edit the post type.
		return user_can( $user_id, $post_type_object->cap->edit_post, $object_id );
	}

	/**
	 * Sanitizes the meta value based on the meta key and object type.
	 *
	 * @since TBD
	 *
	 * @param mixed  $meta_value     The value of the meta field to sanitize.
	 * @param string $meta_key       The meta key for the value being sanitized.
	 * @param string $object_type    The type of the object the meta is associated with (e.g., 'post').
	 * @param string $object_subtype The subtype of the object (e.g., 'tribe_events').
	 *
	 * @return mixed The sanitized meta value, or the original value if no sanitization is needed.
	 * @see sanitize_meta()
	 */
	public function sanitize_meta_value( $meta_value, $meta_key, $object_type, $object_subtype ) {
		// If this isn't a post type, return the value as-is.
		if ( 'post' !== $object_type ) {
			return $meta_value;
		}

		// If this isn't a supported post type, return the value as-is.
		if ( ! in_array( $object_subtype, $this->get_supported_post_types(), true ) ) {
			return $meta_value;
		}

		// If the meta key is not in our list, return the value as-is.
		if ( ! array_key_exists( $meta_key, self::META ) ) {
			return $meta_value;
		}

		$meta_args = self::META[ $meta_key ];
		$type      = $meta_args['type'] ?? 'text';
		$callback  = $this->get_sanitize_callback_for_type( $type );

		return call_user_func( $callback, $meta_value );
	}

	/**
	 * Convert our custom meta type to the type supported by `register_post_meta`.
	 *
	 * @since TBD
	 *
	 * @param string $type The type of the value being registered.
	 *
	 * @return string The type to use when registering the meta field.
	 */
	private function get_register_meta_type( string $type ): string {
		switch ( $type ) {
			// These types are supported by the `register_post_meta` function.
			case 'array':
			case 'boolean':
			case 'integer':
			case 'number':
			case 'object':
			case 'string':
				return $type;

			// These are our custom types that we map to a string.
			case 'separator':
			case 'text':
			case 'textarea':
			case 'url':
			default:
				return 'string';
		}
	}

	/**
	 * Returns the appropriate sanitize callback for the given type.
	 *
	 * @since TBD
	 *
	 * @param string $type The type of the value to sanitize.
	 *
	 * @return callable The sanitize callback function.
	 */
	private function get_sanitize_callback_for_type( string $type ): callable {
		switch ( $type ) {
			case 'boolean':
				return static fn( $value ) => filter_var( $value, FILTER_VALIDATE_BOOLEAN );

			case 'integer':
			case 'number':
				return 'absint';

			case 'separator':
				return static fn( $value ) => tec_sanitize_string( $value );

			case 'string':
			case 'text':
			default:
				return 'sanitize_text_field';

			case 'textarea':
				return 'sanitize_textarea_field';

			case 'url':
				return 'esc_url_raw';
		}
	}
}
