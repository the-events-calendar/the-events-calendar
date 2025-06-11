<?php
/**
 * The main Classy feature controller for The Events Calendar.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */

namespace TEC\Events\Classy;

use TEC\Common\Classy\Controller as Common_Controller;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Common\StellarWP\Assets\Asset;
use TEC\Events\Traits\Can_Edit_Events;
use Tribe__Events__Main as TEC;

/**
 * Class Controller.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */
class Controller extends Controller_Contract {

	use Can_Edit_Events;
	use Supported_Post_Types;

	/**
	 * Registers the hooks and filters for this controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$this->container->register( Meta::class );
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
		$this->container->get( Meta::class )->unregister();
		remove_filter( 'tec_classy_post_types', [ $this, 'add_supported_post_types' ] );
		remove_filter( 'tec_classy_localized_data', [ $this, 'filter_data' ] );
		remove_action( 'tec_common_assets_loaded', [ $this, 'register_assets' ] );
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
	 *          },
	 *          defaultCurrency: array{
	 *              code: string,
	 *              symbol: string,
	 *              position: string
	 *          },
	 *          venuesLimit: int
	 *     }
	 * } The filtered data passed to the Classy application.
	 */
	public function filter_data( array $data ): array {
		$data['settings'] ??= [];

		$additional_settings = [
			'defaultCurrency'    => [
				'code'     => tribe_get_option( 'defaultCurrencyCode', 'USD' ),
				'symbol'   => tribe_get_option( 'defaultCurrencySymbol', '$' ),
				'position' => tribe_get_option( 'reverseCurrencyPosition', false ) ? 'postfix' : 'prefix',
			],
			'timeRangeSeparator' => tribe_get_option( 'timeRangeSeparator', ' - ' ),
			'venuesLimit'        => 1,
		];

		$data['settings'] = array_merge( $data['settings'], $additional_settings );

		$multi_day_cutoff                  = tribe_get_option( 'multiDayCutoff', '00:00' );
		[ $cutoff_hours, $cutoff_minutes ] = array_replace(
			[ 0, 0 ],
			explode( ':', $multi_day_cutoff, 2 )
		);

		$data['endOfDayCutoff'] = [
			'hours'   => (int) $cutoff_hours,
			'minutes' => (int) $cutoff_minutes,
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
