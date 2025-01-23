<?php

namespace TEC\Events\Category_Colors\Tests;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Category_Colors;
use TEC\Events\Category_Colors\Quick_Edit;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main;

class Quick_EditTest extends WPTestCase {
	use With_Uopz;

	/**
	 * Instance of the Quick_Edit class.
	 *
	 * @var Quick_Edit
	 */
	protected Quick_Edit $quick_edit;

	/**
	 * @before
	 * Set up the test environment.
	 */
	protected function setuptests(): void {
		$this->quick_edit = tribe( Quick_Edit::class );
		$this->set_fn_return( 'check_ajax_referer', true ); // Override `check_ajax_referer` to prevent `wp_die`.
	}



	/**
	 * Data provider for testing different quick edit scenarios.
	 *
	 * @return array
	 */
	public function quick_edit_data_provider(): array {
		return [
			'valid data'     => [
				'input'    => [
					'tec-category-color-foreground' => '#ffffff',
					'tec-category-color-background' => '#000000',
					'tec-category-color-text-color' => '#ff0000',
				],
				'expected' => [
					Category_Colors::$meta_foreground_slug => '#ffffff',
					Category_Colors::$meta_background_slug => '#000000',
					Category_Colors::$meta_text_color_slug => '#ff0000',
				],
			],
			'missing fields' => [
				'input'    => [
					'tec-category-color-foreground' => '#123456',
				],
				'expected' => [
					Category_Colors::$meta_foreground_slug => '#123456',
					Category_Colors::$meta_background_slug => '',
					Category_Colors::$meta_text_color_slug => '',
				],
			],
			'invalid data'   => [
				'input'    => [
					'tec-category-color-foreground' => '<script>alert("hack")</script>',
					'tec-category-color-background' => '',
					'tec-category-color-text-color' => 'not-a-color',
				],
				'expected' => [
					Category_Colors::$meta_foreground_slug => 'alert("hack")',
					Category_Colors::$meta_background_slug => '',
					Category_Colors::$meta_text_color_slug => 'not-a-color',
				],
			],
			'empty input'    => [
				'input'    => [],
				'expected' => [
					Category_Colors::$meta_foreground_slug => '',
					Category_Colors::$meta_background_slug => '',
					Category_Colors::$meta_text_color_slug => '',
				],
			],
		];
	}

	/**
	 * @test
	 * @dataProvider quick_edit_data_provider
	 * Tests saving custom quick edit fields with various data inputs.
	 *
	 * @param array $input    The input data for the quick edit fields.
	 * @param array $expected The expected meta values after saving.
	 */
	public function it_saves_quick_edit_custom_fields( array $input, array $expected ): void {
		$term    = wp_insert_term( 'Test Category', Tribe__Events__Main::TAXONOMY );
		$term_id = $term['term_id'];

		$_POST = array_merge( [ '_inline_edit' => 'nonce_value' ], $input );

		$this->quick_edit->save_quick_edit_custom_fields( $term_id, Tribe__Events__Main::TAXONOMY );

		foreach ( $expected as $meta_key => $expected_value ) {
			$actual_value = get_term_meta( $term_id, $meta_key, true );
			$this->assertEquals( $expected_value, $actual_value, "Meta key {$meta_key} did not save as expected." );
		}
	}

	/**
	 * @test
	 * Adds custom taxonomy columns correctly.
	 */
	public function it_adds_custom_taxonomy_columns(): void {
		$columns = [ 'name' => 'Name', 'slug' => 'Slug' ];
		$result  = $this->quick_edit->add_custom_taxonomy_columns( $columns );

		$this->assertArrayHasKey( 'tec-category-colors', $result );
		$this->assertEquals( 'Category Color Options', $result['tec-category-colors'] );
	}

	/**
	 * @test
	 * Populates custom taxonomy column values.
	 */
	public function it_populates_custom_taxonomy_column(): void {
		$term    = wp_insert_term( 'Test Category', Tribe__Events__Main::TAXONOMY );
		$term_id = $term['term_id'];

		update_term_meta( $term_id, Category_Colors::$meta_foreground_slug, '#ffffff' );
		update_term_meta( $term_id, Category_Colors::$meta_background_slug, '#000000' );
		update_term_meta( $term_id, Category_Colors::$meta_text_color_slug, '#ff0000' );

		ob_start();
		$this->quick_edit->populate_custom_taxonomy_column( '', 'tec-category-colors', $term_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( '#ffffff', $output );
		$this->assertStringContainsString( '#000000', $output );
		$this->assertStringContainsString( '#ff0000', $output );
	}

	/**
	 * @test
	 * Skips saving when no quick edit fields are provided.
	 */
	public function it_skips_saving_without_quick_edit_fields(): void {
		$term    = wp_insert_term( 'Test Category', Tribe__Events__Main::TAXONOMY );
		$term_id = $term['term_id'];

		$_POST = [ '_inline_edit' => 'nonce_value' ];

		$this->quick_edit->save_quick_edit_custom_fields( $term_id, Tribe__Events__Main::TAXONOMY );

		$this->assertEmpty( get_term_meta( $term_id, Category_Colors::$meta_foreground_slug, true ) );
		$this->assertEmpty( get_term_meta( $term_id, Category_Colors::$meta_background_slug, true ) );
		$this->assertEmpty( get_term_meta( $term_id, Category_Colors::$meta_text_color_slug, true ) );
	}
}
