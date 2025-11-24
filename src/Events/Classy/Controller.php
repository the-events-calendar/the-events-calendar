<?php
/**
 * The main Classy feature controller for The Events Calendar.
 *
 * @since TBD
 *
 * @package TEC\Events\Classy;
 */

namespace TEC\Events\Classy;

use DateInterval;
use TEC\Common\Classy\Controller as Common_Controller;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Common\StellarWP\Assets\Asset;
use TEC\Events\Traits\Can_Edit_Events;
use Tribe__Events__Main as TEC;
use Tribe__Date_Utils as Dates;

/**
 * Class Controller.
 *
 * @since TBD
 *
 * @package TEC\Events\Classy;
 */
class Controller extends Controller_Contract {
	use Can_Edit_Events;
	use Supported_Post_Types;

	/**
	 * The action that will be fired when this controller is registered.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static string $registration_action = 'tec_classy_events_registered';

	/**
	 * Registers the hooks and filters for this controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$this->container->register( Meta::class );
		$this->container->register( Legacy_Blocks\Controller::class );
		add_filter( 'tec_classy_post_types', [ $this, 'add_supported_post_types' ] );
		add_filter( 'tec_classy_localized_data', [ $this, 'filter_data' ] );

		// Ensure the Event, Venue, and Organizer post types are registered with REST support.
		add_filter( 'tribe_events_register_event_type_args', [ $this, 'add_rest_support' ] );
		add_filter( 'tribe_events_register_venue_type_args', [ $this, 'add_rest_support' ] );
		add_filter( 'tribe_events_register_organizer_type_args', [ $this, 'add_rest_support' ] );

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
		$this->container->get( Legacy_Blocks\Controller::class )->unregister();
		remove_filter( 'tec_classy_post_types', [ $this, 'add_supported_post_types' ] );
		remove_filter( 'tec_classy_localized_data', [ $this, 'filter_data' ] );
		remove_action( 'tec_common_assets_loaded', [ $this, 'register_assets' ] );
		remove_filter( 'tribe_events_register_event_type_args', [ $this, 'add_rest_support' ] );
		remove_filter( 'tribe_events_register_venue_type_args', [ $this, 'add_rest_support' ] );
		remove_filter( 'tribe_events_register_organizer_type_args', [ $this, 'add_rest_support' ] );
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
	 * Adds REST support to the post types used by the Classy Editor.
	 *
	 * @since TBD
	 *
	 * @param array $args Arguments used to setup the Post Type.
	 *
	 * @return array The modified arguments.
	 */
	public function add_rest_support( array $args ): array {
		$args['show_in_rest'] = true;
		return $args;
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
	 *              minutes: int,
	 *              endHours: int,
	 *              endMinutes: int
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

		$multi_day_cutoff                  = tribe_get_option( 'multiDayCutoff', '00:00' );
		[ $cutoff_hours, $cutoff_minutes ] = array_replace(
			[ 0, 0 ],
			explode( ':', $multi_day_cutoff, 2 )
		);
		// Localize this information to spare the JS code the need to run this calculation over and over.
		$cutoff_end_hours_minutes                  = Dates::immutable( "2020-12-31 {$cutoff_hours}:{$cutoff_minutes}:00" )
										->sub( new DateInterval( ( 'PT1S' ) ) )
										->format( 'H:i' );
		[ $cutoff_end_hours, $cutoff_end_minutes ] = explode( ':', $cutoff_end_hours_minutes );

		$additional_settings['endOfDayCutoff'] = [
			'hours'      => (int) $cutoff_hours,
			'minutes'    => (int) $cutoff_minutes,
			'endHours'   => (int) $cutoff_end_hours,
			'endMinutes' => (int) $cutoff_end_minutes,
		];

		$data['settings'] = array_merge( $data['settings'], $additional_settings );

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
													->is_post_type_supported( get_post_type() );

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

		/*
		 * There is currently no style to load.
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
		*/
	}
}
