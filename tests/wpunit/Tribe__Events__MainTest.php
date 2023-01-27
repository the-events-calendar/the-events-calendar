<?php

use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

class Tribe__Events__MainTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	/**
	 * @after
	 */
	public function reregister_taxonomies(): void {
		TEC::instance()->register_taxonomy();
	}

	public function test_post_class_will_work_when_cat_tax_unregistered(): void {
		global $post;
		$post = tribe_events()->set_args( [
			'title'      => 'Test Event',
			'status'     => 'publish',
			'start_date' => '2018-01-01 08:00:00',
			'end_date'   => '2018-01-01 10:00:00',
		] )->create();
		unregister_taxonomy( TEC::TAXONOMY );

		$main       = TEC::instance();
		$post_class = $main->post_class( [] );

		$this->assertEquals( [], $post_class );
	}

	public function test_post_class_will_work_when_some_terms_are_not_valid(): void {
		global $post;
		$post = tribe_events()->set_args( [
			'title'      => 'Test Event',
			'status'     => 'publish',
			'start_date' => '2018-01-01 08:00:00',
			'end_date'   => '2018-01-01 10:00:00',
		] )->create();
		$this->set_fn_return( 'get_the_terms', [
			static::factory()->term->create_and_get( [
				'taxonomy' => TEC::TAXONOMY,
				'name'     => 'Test Category',
			] ),
			new WP_Error( 'invalid_term', 'Invalid term.' ),
		] );

		$main       = TEC::instance();
		$post_class = $main->post_class( [] );

		$this->assertEquals( [ 'cat_test-category' ], $post_class );
	}
}
