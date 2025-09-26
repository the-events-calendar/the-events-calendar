<?php

namespace TEC\Tests\Events\Classy;

use TEC\Common\Tests\Provider\Controller_Test_Case;
use TEC\Events\Classy\Controller;
use Tribe__Events__Main as TEC;

class Controller_Test extends Controller_Test_Case {
	protected $controller_class = Controller::class;

	/**
	 * @covers \TEC\Events\Classy\Controller::add_supported_post_types
	 */
	public function test_add_supported_post_types(): void {
		/** @var Controller $controller */
		$controller = $this->make_controller();

		$post_types = $controller->add_supported_post_types( [] );

		$this->assertEquals( [ TEC::POSTTYPE ], $post_types );
	}

	/**
	 * @covers \TEC\Events\Classy\Controller::filter_data
	 */
	public function test_filter_data(): void {
		$controller = $this->make_controller();

		$data = $controller->filter_data( [] );

		$this->assertEquals( [
			'settings'       =>
				[
					'defaultCurrency'    =>
						[
							'code'     => 'USD',
							'symbol'   => '$',
							'position' => 'prefix',
						],
					'timeRangeSeparator' => ' - ',
					'venuesLimit'        => 1,
				],
			'endOfDayCutoff' =>
				[
					'hours'   => 0,
					'minutes' => 0,
				],
		], $data );

		// Update the options.
		tribe_update_option( 'defaultCurrencyCode', 'EUR' );
		tribe_update_option( 'defaultCurrencySymbol', '€' );
		tribe_update_option( 'reverseCurrencyPosition', 'postfix' );
		tribe_update_option( 'timeRangeSeparator', ' ~ ' );
		tribe_update_option( 'multiDayCutoff', '02:30' );

		$data = $controller->filter_data( [] );

		$this->assertEquals( [
			'settings'       =>
				[
					'defaultCurrency'    =>
						[
							'code'     => 'EUR',
							'symbol'   => '€',
							'position' => 'postfix',
						],
					'timeRangeSeparator' => ' ~ ',
					'venuesLimit'        => 1,
				],
			'endOfDayCutoff' =>
				[
					'hours'   => 2,
					'minutes' => 30,
				],
		], $data );
	}
}
