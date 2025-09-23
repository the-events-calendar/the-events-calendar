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
use Tribe__Timezones as Timezones;
use WP_Post as Post;
use WP_REST_Request as Request;

/**
 * Class Meta
 *
 * @since TBD
 */
class Meta extends Controller_Contract {
	use Meta_Methods;

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
		'_OrganizerEmail'          => [],
		'_OrganizerPhone'          => [],
		'_OrganizerWebsite'        => [],

		// Meta keys for event venue details.
		'_VenueAddress'            => [],
		'_VenueCity'               => [],
		'_VenueCountry'            => [],
		'_VenueLat'                => [],
		'_VenueLng'                => [],
		'_VenuePhone'              => [],
		'_VenueProvince'           => [],
		'_VenueShowMap'            => [
			'type' => 'boolean',
		],
		'_VenueShowMapLink'        => [
			'type' => 'boolean',
		],
		'_VenueState'              => [],
		'_VenueStateProvince'      => [],
		'_VenueURL'                => [],
		'_VenueZip'                => [],
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
			add_action( "rest_after_insert_{$post_type}", [ $this, 'add_utc_dates' ], 5, 2 );
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
			remove_action( "rest_after_insert_{$post_type}", [ $this, 'add_utc_dates' ], 5 );
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
				'auth_callback'     => [ $this, 'user_can_edit_meta' ],
				'sanitize_callback' => [ $this, 'sanitize_meta_value' ],
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

			// Handle UTC offsets like "UTC+6" using the proper timezone conversion method.
			if ( Timezones::is_utc_offset( $timezone_string ) ) {
				$timezone_string = Timezones::timezone_from_utc_offset( $timezone_string )->getName();
			}

			$timezone = Timezones::build_timezone_object( $timezone_string );
			$utc      = new DateTimeZone( 'UTC' );
		} catch ( \Exception $e ) {
			do_action(
				'tribe_log',
				'error',
				'Failed to save UTC dates for event.',
				[
					'post_id' => $post_id,
					'error'   => $e->getMessage(),
				]
			);

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
}
