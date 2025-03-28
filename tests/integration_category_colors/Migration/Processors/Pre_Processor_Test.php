<?php

namespace TEC\Events\Category_Colors\Migration\Processors;

use Closure;
use Generator;
use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;
use Helper\Teccc_Options_Generator;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;

class Pre_Processor_Test extends WPTestCase {
	use With_Uopz;
	use MatchesSnapshots;

	/**
	 * @var Pre_Processor
	 */
	private Pre_Processor $processor;

	/**
	 * @before
	 */
	public function set_up(): void {
		parent::setUp();

		$this->processor = tribe( Pre_Processor::class );

		// Reset migration status before each test
		Status::update_migration_status( Status::$not_started );
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		parent::tearDown();
		delete_option( 'teccc_options' );
		delete_option( Config::$migration_data_option );
		delete_option( Config::$migration_processing_option );
		Status::update_migration_status( Status::$not_started );
	}

	/**
	 * Data provider for category color scenarios
	 *
	 * @return Generator
	 */
	public function category_colors_data_provider(): Generator {
		$setup_basic_categories = function () {
			$teccc_options = Teccc_Options_Generator::generate_teccc_options(
				2,
				[
					'terms'         => [
						1 => [ 'category-1', 'Category 1' ],
						2 => [ 'category-2', 'Category 2' ],
					],
					'ignored_terms' => [ '3', '4' ],
				]
			);

			$teccc_options[ 'category-1-' . Config::$meta_key_prefix . 'color' ]      = '#ff0000';
			$teccc_options[ 'category-1-' . Config::$meta_key_prefix . 'text_color' ] = '#ffffff';
			$teccc_options[ 'category-2-' . Config::$meta_key_prefix . 'color' ]      = '#00ff00';
			$teccc_options[ 'category-2-' . Config::$meta_key_prefix . 'text_color' ] = '#000000';

			update_option( 'teccc_options', $teccc_options );
		};

		$setup_empty_settings = function () {
			delete_option( 'teccc_options' );
		};

		$setup_no_color_value = function () {
			$teccc_options = Teccc_Options_Generator::generate_teccc_options(
				1,
				[
					'terms' => [
						1 => [ 'category-1', 'Category 1' ],
					],
				]
			);
			$teccc_options[ 'category-1-' . Config::$meta_key_prefix . 'color' ] = 'no_color';

			update_option( 'teccc_options', $teccc_options );
		};

		$setup_invalid_color_value = function () {
			$teccc_options = Teccc_Options_Generator::generate_teccc_options(
				1,
				[
					'terms' => [
						1 => [ 'category-1', 'Category 1' ],
					],
				]
			);
			$teccc_options[ 'category-1-' . Config::$meta_key_prefix . 'color' ] = 'invalid-color';

			update_option( 'teccc_options', $teccc_options );
		};

		$setup_malformed_terms = function () {
			$teccc_options = Teccc_Options_Generator::generate_teccc_options(
				1,
				[
					'terms' => [
						1 => [ 'category-1' ], // Missing name
					],
				]
			);
			$teccc_options[ 'category-1-' . Config::$meta_key_prefix . 'color' ] = '#ff0000';

			update_option( 'teccc_options', $teccc_options );
		};

		$setup_duplicate_terms = function () {
			$teccc_options = Teccc_Options_Generator::generate_teccc_options(
				1,
				[
					'terms' => [
						1 => [ 'category-1', 'Category 1' ],
						2 => [ 'category-1', 'Category 2' ], // Duplicate slug
					],
				]
			);
			$teccc_options[ 'category-1-' . Config::$meta_key_prefix . 'color' ] = '#ff0000';

			update_option( 'teccc_options', $teccc_options );
		};

		$setup_corrupted_settings = function () {
			$teccc_options = Teccc_Options_Generator::generate_teccc_options(
				1,
				[
					'terms' => [
						1 => [ 'category-1', 'Category 1' ],
					],
				]
			);
			$teccc_options[ 'category-1-' . Config::$meta_key_prefix . 'color' ] = '#ff0000';
			$teccc_options['terms']                                              = 'not-an-array'; // Corrupt the terms data

			update_option( 'teccc_options', $teccc_options );
		};

		yield 'basic categories with colors' => [
			$setup_basic_categories,
			[
				'categories'    => [
					'1' => [ 'taxonomy_id' => 1 ],
					'2' => [ 'taxonomy_id' => 2 ],
				],
				'settings'      => [
					'category-color-legend-show'            => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
					'category-color-legend-superpowers'     => '',
					'category-color-show-hidden-categories' => '',
				],
				'ignored_terms' => [ '3', '4' ],
			],
			true,
			Status::$preprocessing_completed,
		];

		yield 'empty settings' => [
			$setup_empty_settings,
			[],
			false,
			Status::$preprocessing_skipped,
		];

		yield 'no color value' => [
			$setup_no_color_value,
			[
				'categories'    => [
					'1' => [ 'taxonomy_id' => 1 ],
				],
				'settings'      => [
					'category-color-legend-show'            => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
					'category-color-legend-superpowers'     => '',
					'category-color-show-hidden-categories' => '',
				],
				'ignored_terms' => [],
			],
			true,
			Status::$preprocessing_completed,
		];

		yield 'invalid color value' => [
			$setup_invalid_color_value,
			[
				'categories'    => [
					'1' => [ 'taxonomy_id' => 1 ],
				],
				'settings'      => [
					'category-color-legend-show'            => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
					'category-color-legend-superpowers'     => '',
					'category-color-show-hidden-categories' => '',
				],
				'ignored_terms' => [],
			],
			true,
			Status::$preprocessing_completed,
		];

		yield 'malformed terms data' => [
			$setup_malformed_terms,
			[],
			false,
			Status::$preprocessing_skipped,
		];

		yield 'duplicate term IDs' => [
			$setup_duplicate_terms,
			[],
			false,
			Status::$preprocessing_skipped,
		];

		yield 'corrupted settings data' => [
			$setup_corrupted_settings,
			[],
			false,
			Status::$preprocessing_skipped,
		];
	}

	/**
	 * @test
	 * @dataProvider category_colors_data_provider
	 */
	public function should_process_category_colors( Closure $setup, array $expected_data, bool $expected_result, string $expected_status ): void {
		$setup();

		$result = $this->processor->process();

		$this->assertEquals( $expected_result, $result );
		$status = Status::get_migration_status();
		$this->assertEquals( $expected_status, $status['status'] );

		if ( $expected_result ) {
			$migration_data = get_option( Config::$migration_data_option );
			$this->assertEquals( $expected_data, $migration_data );
		}
	}

	/**
	 * @test
	 */
	public function should_be_runnable_when_migration_not_started(): void {
		$this->assertTrue( $this->processor->is_runnable() );
	}

	/**
	 * @test
	 */
	public function should_not_be_runnable_when_migration_in_progress(): void {
		Status::update_migration_status( Status::$preprocessing_in_progress );
		$this->assertFalse( $this->processor->is_runnable() );
	}

	/**
	 * @test
	 */
	public function should_fire_preprocessor_hooks(): void {
		$pre_hook_fired  = false;
		$post_hook_fired = false;
		$post_hook_data  = null;

		add_action(
			'tec_events_category_colors_migration_preprocessor_start',
			function () use ( &$pre_hook_fired ) {
				$pre_hook_fired = true;
			}
		);

		add_action(
			'tec_events_category_colors_migration_preprocessor_end',
			function ( $data ) use ( &$post_hook_fired, &$post_hook_data ) {
				$post_hook_fired = true;
				$post_hook_data  = $data;
			}
		);

		$this->processor->process();

		$this->assertTrue( $pre_hook_fired );
		$this->assertTrue( $post_hook_fired );
		$this->assertIsArray( $post_hook_data );
	}
}
