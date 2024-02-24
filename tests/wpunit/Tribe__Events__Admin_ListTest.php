<?php

use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Admin_List as Admin_List;
use Tribe__Events__Main as TEc;

class Tribe__Events__Admin_ListTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	/**
	 * @after
	 */
	public function reregister_taxonomies(): void {
		TEC::instance()->register_taxonomy();
	}

	public function test_custom_columns_w_deregistered_cat_tax(): void {
		$post_id = tribe_events()->set_args( [
			'title'      => 'Test Event',
			'status'     => 'publish',
			'start_date' => '2018-01-01 08:00:00',
			'end_date'   => '2018-01-01 10:00:00',
		] )->create()->ID;
		unregister_taxonomy( TEC::TAXONOMY );

		$this->expectOutputString( '' );

		$admin_list = new Admin_List();
		$admin_list->custom_columns( 'events-cats', $post_id );
	}

	public function test_custom_columns_with_bad_terms(): void {
		$post_id = tribe_events()->set_args( [
			'title'      => 'Test Event',
			'status'     => 'publish',
			'start_date' => '2018-01-01 08:00:00',
			'end_date'   => '2018-01-01 10:00:00',
		] )->create()->ID;
		$this->set_fn_return( 'wp_get_post_terms', [
			null,
			'Good Term',
			new WP_Error( 'bad term', 'bad term' ),
		] );

		$this->expectOutputString( 'Good Term' );

		$admin_list = new Admin_List();
		$admin_list->custom_columns( 'events-cats', $post_id );
	}
}
