<?php
/**
 * Controller for managing event meta fields in the Classy application.
 *
 * @since TBD
 */

declare( strict_types=1 );

namespace TEC\Events\Classy;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use WP_Post_Type;

/**
 * Class Meta
 *
 * @since TBD
 */
class Meta extends Controller_Contract {

	use Supported_Post_Types;

	/**
	 * The list of event meta keys to be registered.
	 *
	 * This list is used to register the post meta fields for the Classy application. The
	 * key is the meta key, and the value is an array of arguments used in the `register_post_meta`
	 * function. The `single` key indicates whether the meta field is a single value or an array,
	 * and the `type` key indicates the type of the value. If no `single` or `type` is provided,
	 * the default is `single` set to `true` and `type` set to `string`.
	 *
	 * In the JS application, these meta fields are defined in a single constants file.
	 *
	 * @see src/resources/packages/classy/constants.tsx
	 * @see self::register_meta_fields()
	 *
	 * @var array<array-key, array<string, mixed>>
	 */
	private const META = [
		'_EventAllDay'           => [],
		'_EventCost'             => [],
		'_EventCurrency'         => [],
		'_EventCurrencyPosition' => [],
		'_EventCurrencySymbol'   => [],
		'_EventEndDate'          => [],
		'_EventIsFree'           => [
			'type' => 'boolean',
		],
		'_EventStartDate'        => [],
		'_EventTimezone'         => [],
		'_EventURL'              => [],
		'_EventOrganizerID'      => [
			'single' => false,
			'type'   => 'integer',
		],
		'_EventVenueID'          => [
			'single' => false,
			'type'   => 'integer',
		],
	];

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$this->register_meta_fields();
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * Bound implementations should not be removed in this method!
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->unregister_meta_fields();
	}

	/**
	 * Registers meta fields for all supported post types.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function register_meta_fields(): void {
		foreach ( self::META as $meta_key => $args ) {
			$post_meta_args = [
				'show_in_rest'  => true,
				'single'        => $args['single'] ?? true,
				'type'          => $args['type'] ?? 'string',
				'auth_callback' => fn() => $this->user_can_edit_meta( ...func_get_args() ),
			];

			foreach ( $this->get_supported_post_types() as $post_type ) {
				register_post_meta( $post_type, $meta_key, $post_meta_args );
			}
		}
	}

	/**
	 * Unregisters the post meta fields for the plugin.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function unregister_meta_fields(): void {
		foreach ( self::META as $meta_key => $args ) {
			foreach ( $this->get_supported_post_types() as $post_type ) {
				unregister_post_meta( $post_type, $meta_key );
			}
		}
	}

	/**
	 * Callback for determining if a user can edit an event meta field.
	 *
	 * This method is added to the `auth_{$object_type}_meta_{$meta_key}_for_{$object_subtype}` filter,
	 * which will be evaulated as `auth_post_meta_{$meta_key}_for_tribe_events`.
	 *
	 * @see map_meta_cap()
	 *
	 * @since TBD
	 *
	 * @param bool   $allowed   Whether the user can add the object meta.
	 * @param string $meta_key  The meta key.
	 * @param int    $object_id Object ID.
	 * @param int    $user_id   User ID.
	 *
	 * @return bool Whether the user can edit the object meta.
	 */
	private function user_can_edit_meta( $allowed, $meta_key, $object_id, $user_id ): bool {
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
}
