<?php

namespace TEC\Tests\Events\Classy;

use TEC\Common\Tests\Provider\Controller_Test_Case;
use TEC\Events\Classy\Controller;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

class Controller_Test extends Controller_Test_Case {
	use With_Uopz;

	protected $controller_class = Controller::class;

	/**
	 * @covers \TEC\Events\Classy\Controller::early_register
	 */
	public function test_early_register_when_feature_active(): void {
		// The controller unregister function already ran.
		$controller = $this->make_controller();
		// Define the constant that will disable the feature.
		$this->set_const_value( Controller::DISABLED, false );

		Controller::early_register();

		$this->assertEquals(
			10,
			has_filter( 'tec_using_classy_editor', [ Controller::class, 'return_true' ] )
		);
		$this->assertFalse( has_action( 'tec_using_classy_editor', [ Controller::class, 'return_false' ] ) );
	}

	/**
	 * @covers \TEC\Events\Classy\Controller::early_register
	 */
	public function test_early_register_with_feature_inactive(): void {
		// The controller unregister function already ran.
		$controller = $this->make_controller();
		// Define the constant that will disable the feature.
		$this->set_const_value( Controller::DISABLED, true );

		Controller::early_register();

		$this->assertEquals(
			10,
			has_filter( 'tec_using_classy_editor', [ Controller::class, 'return_false' ] )
		);
		$this->assertFalse(
			has_action( 'tec_using_classy_editor', [ Controller::class, 'return_true' ] )
		);
	}

	/**
	 * @covers \TEC\Events\Classy\Controller::post_uses_new_editor
	 */
	public function test_post_uses_new_editor(): void {
		add_filter( 'tec_events_classy_post_types', fn() => [ 'page', TEC::POSTTYPE ] );

		$controller = $this->make_controller();
		$controller->register();

		$this->assertTrue( $controller->post_uses_new_editor( TEC::POSTTYPE ) );
		$this->assertTrue( $controller->post_uses_new_editor( 'page' ) );
		$this->assertFalse( $controller->post_uses_new_editor( 'post' ) );
		$this->assertFalse( $controller->post_uses_new_editor( TEC::VENUE_POST_TYPE ) );
		$this->assertFalse( $controller->post_uses_new_editor( TEC::ORGANIZER_POST_TYPE ) );
	}

	public static function block_editor_settings_provider(): array {
		return [
			'no post'                  => [
				function (): \WP_Block_Editor_Context {
					return new \WP_Block_Editor_Context( [
						'name' => 'some-context',
						'post' => null,
					] );
				},
				[]
			],
			'post of unsupported type' => [
				function (): \WP_Block_Editor_Context {
					return new \WP_Block_Editor_Context( [
						'name' => 'some-context',
						'post' => static::factory()->post->create_and_get(),
					] );
				},
				[]
			],
			'post of supported type'   => [
				function (): \WP_Block_Editor_Context {
					return new \WP_Block_Editor_Context( [
						'name' => 'some-context',
						'post' => static::factory()->post->create_and_get( [ 'post_type' => 'page' ] ),
					] );
				},
				[ 'templateLock' => true ]
			],
			'event post'               => [
				function (): \WP_Block_Editor_Context {
					return new \WP_Block_Editor_Context( [
						'name' => 'some-context',
						'post' => tribe_events()->set_args( [
							'title'      => 'Test Event',
							'status'     => 'publish',
							'start_date' => 'tomorrow 10am',
							'duration'   => 4 * HOUR_IN_SECONDS
						] )->create(),
					] );
				},
				[ 'templateLock' => true ]
			]
		];
	}

	/**
	 * @dataProvider block_editor_settings_provider
	 * @covers       \TEC\Events\Classy\Controller::early_register
	 * @covers       \TEC\Events\Classy\Controller::filter_block_editor_settings
	 */
	public function test_filter_block_editor_settings( \Closure $fixture, array $expected ): void {
		// Become a user that can edit posts.
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		add_filter( 'tec_events_classy_post_types', fn() => [ 'page', TEC::POSTTYPE ] );

		// Run the controller early registration now: it would have run at the request start.
		Controller::early_register();
		// Register the controller before setting up the fixture to ensure bindings are in place for TEC APIs using them.
		$controller = $this->make_controller();
		$controller->register();

		/** @var \WP_Block_Editor_Context $context */
		$context = $fixture();

		$this->assertEquals( $expected, $controller->filter_block_editor_settings( [], $context ) );
	}

	/**
	 * @covers \TEC\Events\Classy\Controller::get_data
	 */
	public function test_get_data(): void {
		$controller = $this->make_controller();

		$data = $controller->get_data();

		$this->assertArrayHasKey( 'settings', $data );
		// Verify the `settings.timezoneChoice` entry, then remove it from the array to avoid noise.
		$this->assertArrayHasKey( 'timezoneChoice', $data['settings'] );
		$this->assertStringStartsWith( '<option', $data['settings']['timezoneChoice'] );
		unset($data['settings']['timezoneChoice']);
		$this->assertEquals( [
			'settings' =>
				[
					'timezoneString'        => '',
					'startOfWeek'           => '1',
					'endOfDayCutoff'        =>
						[
							'hours'   => 0,
							'minutes' => 0,
						],
					'dateWithYearFormat'    => 'F j, Y',
					'dateWithoutYearFormat' => 'F j',
					'monthAndYearFormat'    => 'F Y',
					'compactDateFormat'     => 'n/j/Y',
					'dataTimeSeparator'     => ' @ ',
					'timeRangeSeparator'    => ' - ',
					'timeFormat'            => 'g:i a',
					'timeInterval'          => 15,
				],
		], $data );
	}
}
