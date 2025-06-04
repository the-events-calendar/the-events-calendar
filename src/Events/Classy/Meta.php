<?php
/**
 * Controller for managing event meta fields in the Classy application.
 *
 * @since TBD
 */

declare( strict_types=1 );

namespace TEC\Events\Classy;

use DateTimeZone;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe__Date_Utils as Date;
use Tribe__Events__API as API;
use Tribe__Timezones as Timezones;
use WP_Post as Post;
use WP_Post_Type;
use WP_REST_Request as Request;

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
		// Meta keys for event details.
		'_EventAllDay'             => [
			'type' => 'boolean',
		],
		'_EventCost'               => [],
		'_EventCostDescription'    => [],
		'_EventCurrencyCode'       => [],
		'_EventCurrencyPosition'   => [],
		'_EventCurrencySymbol'     => [],
		'_EventDateTimeSeparator'  => [
			'type' => 'separator',
		],
		'_EventEndDate'            => [],
		'_EventEndDateUTC'         => [],
		'_EventIsFree'             => [
			'type' => 'boolean',
		],
		'_EventOrganizerID'        => [
			'single' => false,
			'type'   => 'integer',
		],
		'_EventShowMap'            => [
			'type' => 'boolean',
		],
		'_EventShowMapLink'        => [
			'type' => 'boolean',
		],
		'_EventStartDate'          => [],
		'_EventStartDateUTC'       => [],
		'_EventTimeRangeSeparator' => [
			'type' => 'separator',
		],
		'_EventTimezone'           => [],
		'_EventURL'                => [],
		'_EventVenueID'            => [
			'single' => false,
			'type'   => 'integer',
		],

		// Meta keys for event organizer details.
		'_OrganizerEmail'   => [],
		'_OrganizerPhone'   => [],
		'_OrganizerWebsite' => [],

		// Meta keys for event venue details.
		'_VenueAddress'       => [],
		'_VenueCity'          => [],
		'_VenueCountry'       => [],
		'_VenueLat'           => [],
		'_VenueLng'           => [],
		'_VenuePhone'         => [],
		'_VenueProvince'      => [],
		'_VenueShowMap'       => [
			'type' => 'boolean',
		],
		'_VenueShowMapLink'   => [
			'type' => 'boolean',
		],
		'_VenueState'         => [],
		'_VenueStateProvince' => [],
		'_VenueURL'           => [],
		'_VenueZip'           => [],
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

		// Add actions for each supported post type.
		foreach ( $this->get_supported_post_types() as $post_type ) {
			add_action( "rest_after_insert_{$post_type}", [ $this, 'add_utc_dates' ], 10, 2 );
			add_action( "rest_after_insert_{$post_type}", [ $this, 'update_cost' ], 10, 2 );
		}
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

		// Remove actions for each supported post type.
		foreach ( $this->get_supported_post_types() as $post_type ) {
			remove_action( "rest_after_insert_{$post_type}", [ $this, 'add_utc_dates' ] );
			remove_action( "rest_after_insert_{$post_type}", [ $this, 'update_cost' ] );
		}
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
				'show_in_rest'      => true,
				'single'            => $args['single'] ?? true,
				'type'              => $this->get_register_meta_type( $args['type'] ?? 'text' ),
				'auth_callback'     => fn() => $this->user_can_edit_meta( ...func_get_args() ),
				'sanitize_callback' => fn() => $this->sanitize_meta_value( ...func_get_args() ),
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

	/**
	 * Sanitizes the meta value based on the meta key and object type.
	 *
	 * @see sanitize_meta()
	 *
	 * @since TBD
	 *
	 * @param mixed  $meta_value     The value of the meta field to sanitize.
	 * @param string $meta_key       The meta key for the value being sanitized.
	 * @param string $object_type    The type of the object the meta is associated with (e.g., 'post').
	 * @param string $object_subtype The subtype of the object (e.g., 'tribe_events').
	 *
	 * @return mixed The sanitized meta value, or the original value if no sanitization is needed.
	 */
	private function sanitize_meta_value( $meta_value, $meta_key, $object_type, $object_subtype ) {
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
		$single    = $meta_args['single'] ?? true;
		$callback  = $this->get_sanitize_callback_for_type( $type );

		return $single
			? call_user_func( $callback, $meta_value )
			: array_map( $callback, (array) $meta_value );
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

	/**
	 * Adds UTC dates to the event post after it has been inserted.
	 *
	 * This method is called after an event post is created or updated, and it adds the UTC
	 * versions of the start and end dates to the post meta.
	 *
	 * @since TBD
	 *
	 * @param Post    $post    Inserted or updated post object.
	 * @param Request $request Request object.
	 *
	 * @return void
	 */
	public function add_utc_dates( $post, $request ): void {
		// If for some reason we don't have the correct object types, return early.
		if ( ! $post instanceof Post || ! $request instanceof Request ) {
			return;
		}

		$meta    = $request->get_param( 'meta' ) ?? [];
		$post_id = $post->ID;

		// If there isn't a start or end date, return early.
		if ( empty( $meta['_EventStartDate'] ) && empty( $meta['_EventEndDate'] ) ) {
			return;
		}

		try {
			$timezone_string = ( ! empty( $meta['_EventTimezone'] ) )
				? $meta['_EventTimezone']
				: $this->get_timezone_string( $post_id );

			// Attempt to create a DateTimeZone object from the timezone string.
			$timezone = new DateTimeZone( $timezone_string );
			$utc      = new DateTimeZone( 'UTC' );
		} catch ( \Exception $e ) {
			// @todo: Decide how to handle the exception.
			// for now, just return early.
			return;
		}

		// Get the start and end dates from the meta or post meta.
		$start_date = ( ! empty( $meta['_EventStartDate'] ) )
			? $meta['_EventStartDate']
			: get_post_meta( $post_id, '_EventStartDate', true );

		$end_date = ( ! empty( $meta['_EventEndDate'] ) )
			? $meta['_EventEndDate']
			: get_post_meta( $post_id, '_EventEndDate', true );

		$utc_start_date = Date::build_date_object( $start_date, $timezone )->setTimezone( $utc );
		$utc_end_date   = Date::build_date_object( $end_date, $timezone )->setTimezone( $utc );

		update_post_meta( $post_id, '_EventStartDateUTC', $utc_start_date->format( Date::DBDATETIMEFORMAT ) );
		update_post_meta( $post_id, '_EventEndDateUTC', $utc_end_date->format( Date::DBDATETIMEFORMAT ) );
	}

	/**
	 * Retrieves the timezone string for the event.
	 *
	 * This method checks the post meta for the `_EventTimezone` key and returns its value.
	 * If it is not set, it defaults to the WordPress timezone string.
	 *
	 * @since TBD
	 *
	 * @param int $event_id The ID of the event post.
	 *
	 * @return string The timezone string for the event.
	 */
	private function get_timezone_string( int $event_id ): string {
		$timezone = get_post_meta( $event_id, '_EventTimezone', true );
		if ( empty( $timezone ) ) {
			$timezone = Timezones::wp_timezone_string();
		}

		return $timezone;
	}

	/**
	 * Updates the cost of an event.
	 *
	 * This method is called when the cost of an event is updated via the REST API.
	 * It retrieves the cost from the request and updates the post meta accordingly.
	 *
	 * @since TBD
	 *
	 * @param Post    $post    The post object being updated.
	 * @param Request $request The request object containing the new cost data.
	 *
	 * @return void
	 */
	public function update_cost( $post, $request ): void {
		// If for some reason we don't have the correct object types, return early.
		if ( ! $post instanceof Post || ! $request instanceof Request ) {
			return;
		}

		$meta    = $request->get_param( 'meta' ) ?? [];
		$post_id = $post->ID;

		$cost = (array) ( ! empty( $meta['_EventCost'] ) ? $meta['_EventCost'] : tribe_get_cost( $post_id ) );
		API::update_event_cost( $post_id, $cost );
	}
}
