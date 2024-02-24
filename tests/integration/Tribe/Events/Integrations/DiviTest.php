<?php

namespace Tribe\Events\Integrations;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Integrations\Divi\Service_Provider;

class DiviTest extends WPTestCase {
	/**
	 * @before
	 */
	public function register_divi_integration(): void {
		update_option( 'stylesheet', 'Divi' );
		update_option( 'template', 'Divi' );
		tribe( Service_Provider::class )->register();
	}

	/**
	 * @after
	 */
	public function unregister_divi_integration(): void {
		tribe( Service_Provider::class )->register();
	}

	/**
	 * It should not reset postdata in custom queries for non-Events
	 *
	 * @test
	 */
	public function should_not_reset_postdata_in_custom_queries_for_non_events() {
		$page = static::factory()->post->create( [ 'post_type' => 'page' ] );
		// Set up a main  query for the page.
		global $wp_query;
		$wp_query = new \WP_Query( [ 'p' => $page, 'post_type' => 'page' ] );
		$wp_query->reset_postdata();
		// Create some posts to iterate on.
		$posts = static::factory()->post->create_many( 3 );

		$query = new \WP_Query( [
			'posts_per_page' => 12,
			'post_type'      => 'post',
			'post_status'    => 'publish',
		] );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$this->assertContains( get_the_ID(), $posts, 'Before the post_id_helper filter application, get_the_ID should return the correct value.' );

				\Tribe__Main::post_id_helper( get_the_ID() ); // This will trigger the issue.

				$this->assertContains( get_the_ID(), $posts, 'The ID filter should not reset the postdata to the page!' );
			}
		}
	}
}
