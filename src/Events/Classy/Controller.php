<?php
/**
 * The main Classy feature controller for The Events Calendar.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */

namespace TEC\Events\Classy;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Common\StellarWP\Assets\Asset;
use Tribe__Events__Main as TEC;
use TEC\Common\Classy\Controller as Common_Controller;

/**
 * Class Controller.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */
class Controller extends Controller_Contract {
	/**
	 * The list of single event meta keys to be registered.
	 *
	 * @var array<string>
	 */
	private const SINGLE_META = [
		TEC::POSTTYPE => [
			'_EventURL',
			'_EventStartDate',
			'_EventEndDate',
			'_EventAllDay',
			'_EventTimezone',
		],
	];

	/**
	 * The list of multiple event meta keys to be registered.
	 *
	 * @var array<string>
	 */
	private const MULTIPLE_META = [
		TEC::POSTTYPE => [
			'_EventOrganizerID',
			'_EventVenueID',
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
	}

	/**
	 * Registers meta fields for events.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function register_meta_fields(): void {
		foreach ( self::SINGLE_META as $meta_keys ) {
			foreach ( $meta_keys as $meta_key ) {

				register_post_meta(
					TEC::POSTTYPE,
					$meta_key,
					[
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => static function () {
							return current_user_can( 'edit_posts' );
						},
					]
				);
			}
		}

		foreach ( self::MULTIPLE_META as $post_type => $meta_keys ) {
			foreach ( $meta_keys as $meta_key ) {
				register_post_meta(
					TEC::POSTTYPE,
					$meta_key,
					[
						'show_in_rest'  => true,
						'single'        => false,
						'type'          => 'integer',
						'auth_callback' => static function () {
							return current_user_can( 'edit_posts' );
						},
					]
				);
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
		foreach ( self::SINGLE_META as $post_type => $meta_keys ) {
			foreach ( $meta_keys as $meta_key ) {
				unregister_post_meta( $post_type, $meta_key );
			}
		}

		foreach ( self::MULTIPLE_META as $post_type => $meta_keys ) {
			foreach ( $meta_keys as $meta_key ) {
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
		$supported_post_types[] = TEC::POSTTYPE;

		return $supported_post_types;
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
}
