<?php

namespace TEC\Events\Category_Colors\Migration;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use Tribe\Tests\Traits\With_Uopz;

class Migration_Process_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * @before
	 */
	public function setup_environment(): void {
		parent::setUp();
		// Ensure clean slate before each test.
		delete_option( 'teccc_options' );
		delete_option( 'tribe_events_category_colors' );
	}

	/**
	 * @after
	 */
	public function cleanup_environment(): void {
		// Cleanup after test.
		delete_option( 'teccc_options' );
		delete_option( 'tribe_events_category_colors' );
		parent::tearDown();
	}

	/**
	 * Data provider for migration scenarios.
	 */
	public function migration_scenarios(): Generator {
		yield 'Single category' => [ fn() => $this->generate_test_data( 1 ) ];
		yield '50 categories' => [ fn() => $this->generate_test_data( 50 ) ];
		yield '100 categories' => [ fn() => $this->generate_test_data( 100 ) ];
	}

	/**
	 * Generates test terms and options dynamically.
	 */
	protected function generate_test_data( int $num_categories = 3 ): array {
		$terms         = [];
		$teccc_options = [
			'terms'     => [],
			'all_terms' => [],
		];
		for ( $i = 1; $i <= $num_categories; $i++ ) {
			$slug       = "category{$i}";
			$name       = "Category {$i}";
			$border     = sprintf( "#%06X", mt_rand( 0, 0xFFFFFF ) );
			$background = sprintf( "#%06X", mt_rand( 0, 0xFFFFFF ) );
			$text       = mt_rand( 0, 1 ) ? 'no_color' : sprintf( "#%06X", mt_rand( 0, 0xFFFFFF ) );

			$term = wp_insert_term( $name, 'tribe_events_cat', [ 'slug' => $slug ] );
			if ( is_wp_error( $term ) || ! isset( $term['term_id'] ) ) {
				continue;
			}
			$term_id                                = (int) $term['term_id'];
			$terms[ $slug ]                         = $term_id;
			$teccc_options['terms'][ $term_id ]     = [ $slug, htmlentities( $name ) ];
			$teccc_options['all_terms'][ $term_id ] = [ $slug, htmlentities( $name ) ];
			$teccc_options["{$slug}-border"]        = $border;
			$teccc_options["{$slug}-background"]    = $background;
			$teccc_options["{$slug}_text"]          = $text;
		}

		$required_keys = [
			'add_legend'  => 'legend',
			'reset_show'  => 'general',
			'font_weight' => 'general',
		];
		foreach ( $required_keys as $key => $group ) {
			$teccc_options[ $key ] = '';
		}

		update_option( 'teccc_options', $teccc_options );

		return $terms;
	}

	/**
	 * @test
	 * @dataProvider migration_scenarios
	 */
	public function it_transfers_category_colors_correctly( Closure $data_generator ): void {
		$this->set_fn_return( 'current_time', '{time}' );
		$category_ids = $data_generator();

		tribe( Migration_Process::class )->migrate();

		$test_data         = get_option( 'tec_category_colors_migration_data' );
		$logger_data       = Logger::get_logs();
		$migration_status  = get_option( 'tec_events_category_colors_migration_status', [] );
		$original_settings = get_option( 'teccc_options', [] );

		$this->assertSame( 'migration_completed', $migration_status['status'] ?? '', 'Migration did not complete successfully.' );

		// Verify taxonomy meta matches the original settings.
		foreach ( $category_ids as $slug => $term_id ) {
			$expected_meta = [
				'tec-events-cat-colors-primary'   => $original_settings["{$slug}-border"] ?? '',
				'tec-events-cat-colors-secondary' => $original_settings["{$slug}-background"] ?? '',
				'tec-events-cat-colors-text'      => ( $original_settings["{$slug}_text"] ?? '' ) === 'no_color' ? '' : ( $original_settings["{$slug}_text"] ?? '' ),
			];

			foreach ( $expected_meta as $meta_key => $expected_value ) {
				$this->assertSame(
					$expected_value,
					get_term_meta( $term_id, $meta_key, true ),
					"Mismatch in meta value '{$meta_key}' for term '{$slug}'."
				);
			}
		}
	}
}
