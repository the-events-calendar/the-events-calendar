<?php
/**
 * The main Classy feature controller for The Events Calendar.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */

namespace TEC\Events\Classy;

use DateTimeZone;
use TEC\Common\Classy\Controller as Common_Controller;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Common\StellarWP\Assets\Asset;
use TEC\Events\Traits\Can_Edit_Events;
use Tribe__Events__Main as TEC;
use Tribe__Date_Utils as Dates;
use WP_Post;

/**
 * Class Controller.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */
class Controller extends Controller_Contract {

	use Can_Edit_Events;

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
	 * Registers the hooks and filters for this controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$this->register_meta_fields();
		add_filter( 'tec_classy_post_types', [ $this, 'add_supported_post_types' ] );
		add_filter( 'tec_classy_localized_data', [ $this, 'filter_data' ] );

		// Register the main assets entry point.
		if ( did_action( 'tec_common_assets_loaded' ) ) {
			$this->register_assets();
		} else {
			add_action( 'tec_common_assets_loaded', [ $this, 'register_assets' ] );
		}

		add_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'on_rest_insert_event' ], 5 );
	}

	/**
	 * Unregisters the hooks and filters added by this controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->unregister_meta_fields();
		remove_filter( 'tec_classy_post_types', [ $this, 'add_supported_post_types' ] );
		remove_filter( 'tec_classy_localized_data', [ $this, 'filter_data' ] );
		remove_action( 'tec_common_assets_loaded', [ $this, 'register_assets' ] );
		remove_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'on_rest_insert_event' ], 5 );
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
				'auth_callback' => fn() => $this->current_user_can_edit_events(),
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
	 * Filters the post types that Classy supports when The Events Calendar is active.
	 *
	 * @since TBD
	 *
	 * @param array $supported_post_types
	 *
	 * @return array<string> The filtered list of supported post types.
	 */
	public function add_supported_post_types( array $supported_post_types ): array {
		return array_unique(
			array_merge(
				$supported_post_types,
				$this->get_supported_post_types()
			)
		);
	}

	/**
	 * Filters the data passed to the Classy application.
	 *
	 * @since TBD
	 *
	 * @param array{settings?: array<string,mixed>} $data The data passed to the Classy application.
	 *
	 * @return array{
	 *     settings:{
	 *          timeRangeSeparator: string,
	 *          endOfDayCutoff:{
	 *              hours: int,
	 *              minutes: int
	 *          }
	 *     }
	 * } The filtered data passed to the Classy application.
	 */
	public function filter_data( array $data ): array {
		$data['settings'] ??= [];

		$time_range_separator                                  = tribe_get_option( 'timeRangeSeparator', ' - ' );
		$multi_day_cutoff                                      = tribe_get_option( 'multiDayCutoff', '00:00' );
		[ $multi_day_cutoff_hours, $multi_day_cutoff_minutes ] = array_replace(
			[ 0, 0 ],
			explode( ':', $multi_day_cutoff, 2 )
		);

		$data['settings']['timeRangeSeparator'] = $time_range_separator;
		$data['endOfDayCutoff']                 = [
			'hours'   => (int) $multi_day_cutoff_hours,
			'minutes' => (int) $multi_day_cutoff_minutes,
		];

		return $data;
	}

	/**
	 * Registers the assets required to extend the Classy application with TEC functionality.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_assets() {
		$post_uses_classy = fn() => $this->container->get( Common_Controller::class )
													->post_uses_classy( get_post_type() );

		Asset::add(
			'tec-classy-events',
			'classy.js'
		)->add_to_group_path( TEC::class . '-packages' )
			// @todo this should be dynamic depending on the loading context.
			->enqueue_on( 'enqueue_block_editor_assets' )
			->set_condition( $post_uses_classy )
			->add_dependency( 'tec-classy' )
			->add_to_group( 'tec-classy' )
			->register();

		Asset::add(
			'tec-classy-events-style',
			'style-classy.css'
		)->add_to_group_path( TEC::class . '-packages' )
			// @todo this should be dynamic depending on the loading context.
			->enqueue_on( 'enqueue_block_editor_assets' )
			->set_condition( $post_uses_classy )
			->add_dependency( 'tec-classy-style' )
			->add_to_group( 'tec-classy' )
			->register();
	}

	/**
	 * Returns the list of post types that this controller supports.
	 *
	 * @since TBD
	 *
	 * @return array<string> The list of supported post types.
	 */
	private function get_supported_post_types(): array {
		return [
			TEC::POSTTYPE,
		];
	}

	/**
	 * Ensures information required to correclty save an Event is provided when saved through the REST API.
	 *
	 * This method "patches" the meta that is saved to the database via the REST API to make sure all the meta
	 * required to correctly build and event and its occurrences will be in the database before the TEC API processes
	 * it at priority 10.
	 *
	 * @since TBD
	 *
	 * @param WP_Post $post The post that has just been saved to the database via the REST API.
	 *
	 * @return void The post meta is updated, if required.
	 */
	public function on_rest_insert_event( $post ): void {
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$post_id = $post->ID;

		$start_date_utc = get_post_meta( $post_id, '_EventStartDateUTC', true );
		$start_date     = get_post_meta( $post_id, '_EventStartDate', true );
		$end_date       = get_post_meta( $post_id, '_EventEndDate', true );
		$timezone       = get_post_meta( $post_id, '_EventTimezone', true );

		if ( ! $start_date_utc && $timezone && $start_date && $end_date ) {
			// If the start date UTC meta is missing, then build it and the end date from the date/time and timezone.
			$utc_timezone   = new DateTimeZone( 'UTC' );
			$start_date_utc = Dates::immutable( $start_date, $timezone )
									->setTimezone( $utc_timezone )
									->format( 'Y-m-d H:i:s' );
			update_post_meta( $post_id, '_EventStartDateUTC', $start_date_utc );
			$end_date_utc = Dates::immutable( $end_date, $timezone )
								->setTimezone( $utc_timezone )
								->format( 'Y-m-d H:i:s' );
			update_post_meta( $post_id, '_EventEndDateUTC', $end_date_utc );
		}
	}
}
