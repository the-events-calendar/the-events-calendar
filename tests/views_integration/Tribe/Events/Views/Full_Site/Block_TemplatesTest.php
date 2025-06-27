<?php

namespace Tribe\Events\Views\Full_Site;

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Block_Templates\Controller;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class Block_TemplatesTest extends HtmlTestCase {
	use MatchesSnapshots;

	public function setUp() {
		parent::setUp();
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	/**
	 * Sorts and removed dynamic fields for consistent structures to use in snapshots.
	 *
	 * @param $template
	 *
	 * @return array
	 */
	public static function normalize_wp_template( $template ): array {
		$array_template = (array) $template;
		asort( $array_template );
		// Dynamic fields, ditch them for snapshots.
		foreach ( [ 'wp_id', 'author', 'modified', 'plugin' ] as $field ) {
			$array_template[ $field ] = null;
		}

		return $array_template;
	}

	/**
	 * Testing when the template file is queried.
	 */
	public function test_get_queried_template() {
		global $wp_query;
		$old_post_type = $wp_query->get( 'post_type' );
		$wp_query->set( 'post_type', [ 'tribe_events' ] );

		// Check we find the correct template without error.
		$template = get_archive_template();
		$this->assertMatchesSnapshot( $template );

		$wp_query->set( 'post_type', $old_post_type );
	}

	/**
	 * Tests the WP Block Template services are generated correctly, with correct values and are retrievable from WP
	 * Core API.
	 */
	public function test_wp_block_templates_shape() {
		$controller = tribe( Controller::class );

		// Get our templates to test, make sure we have them.
		$template_services = $controller->get_filtered_block_templates();
		$this->assertCount( 2, $template_services );

		foreach ( $template_services as $template_service ) {
			$this->assertMatchesSnapshot( $template_service->id() );
			$templateA = $template_service->get_block_template();
			$templateB = get_block_template( $template_service->id() );

			// Should be a legit ID.
			$this->assertIsInt( $templateA->wp_id );
			$this->assertGreaterThan( 0, $templateA->wp_id );

			// Should be the same DB template.
			$this->assertEquals( $templateA->wp_id, $templateB->wp_id );

			// Normalize for comparisons.
			$normalized_templateA = $templateA;
			$normalized_templateB = $templateB;

			// This is only hydrated in WP 6.3+ - removing for some backwards compat in tests.
			$normalized_templateA->modified = null;
			$normalized_templateB->modified = null;

			// Should have correct content, title, id, slug etc.
			$this->assertEquals( $normalized_templateA, $normalized_templateB );
			$this->assertMatchesSnapshot( self::normalize_wp_template( $normalized_templateA ) );
		}
	}
}
