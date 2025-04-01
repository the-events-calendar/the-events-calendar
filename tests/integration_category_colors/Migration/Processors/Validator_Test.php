/**
 * Tests for the Validator class.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */

namespace TEC\Events\Category_Colors\Migration\Processors;

use Closure;
use Generator;
use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;
use Helper\Teccc_Options_Generator;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use Tribe__Events__Main;

/**
 * Class Validator_Test
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */
class Validator_Test extends WPTestCase {
	use With_Uopz;
	use MatchesSnapshots;

	/**
	 * @var Validator
	 */
	private Validator $processor;

	/**
	 * @before
	 */
	public function set_up(): void {
		parent::setUp();

		$this->processor = tribe( Validator::class );

		// Reset migration status before each test
		Status::update_migration_status( Status::$preprocessing_completed );

		// Mock get_terms to return valid category IDs
		$this->set_fn_return( 'get_terms', [ 1, 2 ] );
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		parent::tearDown();
		delete_option( Config::MIGRATION_DATA_OPTION );
		delete_option( Config::MIGRATION_PROCESSING_OPTION );
		Status::update_migration_status( Status::$not_started );
	}

	/**
	 * Data provider for test cases.
	 *
	 * @since TBD
	 *
	 * @return Generator
	 */
	public function validation_data_provider(): Generator {
		$setup_valid_data = function () {
			$migration_data = [
				'categories' => [
					'1' => [
						'taxonomy_id' => 1,
						'tec-events-cat-colors-primary' => '#ff0000',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text' => '#000000',
					],
					'2' => [
						'taxonomy_id' => 2,
						'tec-events-cat-colors-primary' => '#00ff00',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text' => '#000000',
					],
				],
				'settings' => [
					'category-color-legend-show' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
					'category-color-legend-superpowers' => '1',
					'category-color-show-hidden-categories' => '1',
					'category-color-custom-CSS' => '1',
					'category-color-reset-button' => '1',
				],
				'ignored_terms' => [ '3', '4' ],
			];

			// Set up original settings for validation
			$original_settings = [
				'add_legend' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
				'legend_superpowers' => '1',
				'show_ignored_cats_legend' => '1',
				'custom_legend_css' => '1',
				'reset_show' => '1',
			];
			update_option( 'teccc_options', $original_settings );

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		$setup_empty_data = function () {
			update_option( Config::MIGRATION_DATA_OPTION, [] );
		};

		$setup_invalid_structure = function () {
			$migration_data = [
				'categories' => 'not-an-array',
				'settings' => [],
				'ignored_terms' => [],
			];

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		$setup_missing_required_fields = function () {
			$migration_data = [
				'categories' => [],
				'settings' => [],
				'ignored_terms' => [],
			];

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		$setup_invalid_meta_keys = function () {
			$migration_data = [
				'categories' => [
					'1' => [
						'taxonomy_id' => 1,
						'invalid-key' => '#ff0000',
					],
				],
				'settings' => [],
				'ignored_terms' => [],
			];

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		$setup_invalid_category_ids = function () {
			$migration_data = [
				'categories' => [
					'999999' => [ // Invalid category ID
						'taxonomy_id' => 999999,
						'tec-events-cat-colors-primary' => '#ff0000',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text' => '#000000',
					],
				],
				'settings' => [],
				'ignored_terms' => [],
			];

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		$setup_invalid_settings_values = function () {
			$migration_data = [
				'categories' => [],
				'settings' => [
					'category-color-legend-show' => 'not-an-array',
					'category-color-legend-superpowers' => 'invalid',
					'category-color-show-hidden-categories' => 'invalid',
					'category-color-custom-CSS' => 'invalid',
					'category-color-reset-button' => 'invalid',
				],
				'ignored_terms' => [],
			];

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		$setup_invalid_color_values = function () {
			$migration_data = [
				'categories' => [
					'1' => [
						'taxonomy_id' => 1,
						'tec-events-cat-colors-primary' => 'invalid-color',
						'tec-events-cat-colors-secondary' => 'invalid-color',
						'tec-events-cat-colors-text' => 'invalid-color',
					],
				],
				'settings' => [],
				'ignored_terms' => [],
			];

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		$setup_duplicate_ignored_terms = function () {
			$migration_data = [
				'categories' => [
					'1' => [
						'taxonomy_id' => 1,
						'tec-events-cat-colors-primary' => '#ff0000',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text' => '#000000',
					],
				],
				'settings' => [
					'category-color-legend-show' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
					'category-color-legend-superpowers' => '1',
					'category-color-show-hidden-categories' => '1',
				],
				'ignored_terms' => [ '1', '1' ], // Duplicate term ID
			];

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		yield 'valid data' => [
			$setup_valid_data,
			true,
			Status::$validation_completed,
		];

		yield 'empty data' => [
			$setup_empty_data,
			false,
			Status::$validation_failed,
		];

		yield 'invalid structure' => [
			$setup_invalid_structure,
			false,
			Status::$validation_failed,
		];

		yield 'missing required fields' => [
			$setup_missing_required_fields,
			false,
			Status::$validation_failed,
		];

		yield 'invalid meta keys' => [
			$setup_invalid_meta_keys,
			false,
			Status::$validation_failed,
		];

		yield 'invalid category ids' => [
			$setup_invalid_category_ids,
			false,
			Status::$validation_failed,
		];

		yield 'invalid settings values' => [
			$setup_invalid_settings_values,
			false,
			Status::$validation_failed,
		];

		yield 'invalid color values' => [
			$setup_invalid_color_values,
			false,
			Status::$validation_failed,
		];

		yield 'duplicate ignored terms' => [
			$setup_duplicate_ignored_terms,
			false,
			Status::$validation_failed,
		];
	}

	/**
	 * @test
	 * @dataProvider validation_data_provider
	 */
	public function should_validate_migration_data( Closure $setup, bool $expected_result, string $expected_status ): void {
		$setup();

		$result = $this->processor->process();

		$this->assertEquals( $expected_result, $result );
		$status = Status::get_migration_status();
		$this->assertEquals( $expected_status, $status['status'] );
	}

	/**
	 * @test
	 */
	public function should_be_runnable_when_preprocessing_completed(): void {
		$this->assertTrue( $this->processor->is_runnable() );
	}

	/**
	 * @test
	 */
	public function should_not_be_runnable_when_validation_in_progress(): void {
		Status::update_migration_status( Status::$validation_in_progress );
		$this->assertFalse( $this->processor->is_runnable() );
	}

	/**
	 * @test
	 */
	public function should_fire_validator_hooks(): void {
		$pre_hook_fired  = false;
		$post_hook_fired = false;
		$post_hook_data  = null;

		// Set up valid data for the test
		$migration_data = [
			'categories' => [
				'1' => [
					'taxonomy_id' => 1,
					'tec-events-cat-colors-primary' => '#ff0000',
					'tec-events-cat-colors-secondary' => '#ffffff',
					'tec-events-cat-colors-text' => '#000000',
				],
			],
			'settings' => [
				'category-color-legend-show' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
				'category-color-legend-superpowers' => '1',
				'category-color-show-hidden-categories' => '1',
				'category-color-custom-CSS' => '1',
				'category-color-reset-button' => '1',
			],
			'ignored_terms' => [],
		];

		// Set up original settings for validation
		$original_settings = [
			'add_legend' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
			'legend_superpowers' => '1',
			'show_ignored_cats_legend' => '1',
			'custom_legend_css' => '1',
			'reset_show' => '1',
		];
		update_option( 'teccc_options', $original_settings );
		update_option( Config::MIGRATION_DATA_OPTION, $migration_data );

		// Add hooks to track firing
		add_action( 'tec_events_category_colors_migration_validator_pre_process', function() use ( &$pre_hook_fired ) {
			$pre_hook_fired = true;
		} );

		add_action( 'tec_events_category_colors_migration_validator_post_process', function( $data ) use ( &$post_hook_fired, &$post_hook_data ) {
			$post_hook_fired = true;
			$post_hook_data  = $data;
		} );

		$result = $this->processor->process();

		$this->assertTrue( $result );
		$this->assertTrue( $pre_hook_fired );
		$this->assertTrue( $post_hook_fired );
		$this->assertEquals( $migration_data, $post_hook_data );
	}

	/**
	 * @test
	 */
	public function should_handle_get_terms_error(): void {
		// Mock get_terms to return a WP_Error
		$this->set_fn_return( 'get_terms', new \WP_Error( 'test_error', 'Test error message' ) );

		$migration_data = [
			'categories' => [
				'1' => [
					'taxonomy_id' => 1,
					'tec-events-cat-colors-primary' => '#ff0000',
					'tec-events-cat-colors-secondary' => '#ffffff',
					'tec-events-cat-colors-text' => '#000000',
				],
			],
			'settings' => [
				'category-color-legend-show' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
				'category-color-legend-superpowers' => '1',
				'category-color-show-hidden-categories' => '1',
			],
			'ignored_terms' => [],
		];

		update_option( Config::MIGRATION_DATA_OPTION, $migration_data );

		$result = $this->processor->process();

		$this->assertFalse( $result );
		$status = Status::get_migration_status();
		$this->assertEquals( Status::$validation_failed, $status['status'] );
	}
} 