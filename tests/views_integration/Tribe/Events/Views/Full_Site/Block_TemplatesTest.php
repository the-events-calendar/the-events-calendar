<?php

namespace Tribe\Events\Views\Full_Site;

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Editor\Full_Site\Controller;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class Block_TemplatesTest extends HtmlTestCase {
	use MatchesSnapshots;

	protected $user;

	public function setUp() {
		parent::setUp();
		$this->user = wp_get_current_user();
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( $this->user->ID );
	}

	/**
	 * Sorts and removed dynamic fields for consistent structures.
	 *
	 * @param $template
	 *
	 * @return array
	 */
	public static function normalize_wp_template( $template ): array {
		$array_template = (array) $template;
		asort( $array_template );
		// Dynamic fields, ditch them.
		foreach ( [ 'wp_id', 'author' ] as $field ) {
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
			$normalized_templateA = self::normalize_wp_template( $templateA );
			$normalized_templateB = self::normalize_wp_template( $templateB );

			// Should have correct content, title, id, slug etc.
			$this->assertEquals( $normalized_templateA, $normalized_templateB );
			$this->assertMatchesSnapshot( $normalized_templateA );
		}
	}
}
