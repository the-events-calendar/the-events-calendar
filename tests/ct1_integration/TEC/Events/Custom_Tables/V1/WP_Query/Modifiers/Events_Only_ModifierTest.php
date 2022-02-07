<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use Tribe__Events__Main as TEC;

class Events_Only_ModifierTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should not apply to query in admin context
	 *
	 * @test
	 * @preserveGlobalState  disabled
	 * @runInSeparateProcess to avoid carrying over the const to other tests
	 */
	public function should_not_apply_to_query_in_admin_context() {
		$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );
		define( 'WP_ADMIN', true );

		$modifier = new Events_Only_Modifier();
		$applies  = $modifier->applies_to( $query );

		$this->assertFalse( $applies );
	}

	/**
	 * It should apply to query in AJAX context
	 *
	 * @test
	 */
	public function should_apply_to_query_in_ajax_context() {
		$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );
		add_filter( 'wp_doing_ajax', '__return_true' );

		$modifier = new Events_Only_Modifier();
		$applies  = $modifier->applies_to( $query );

		$this->assertTrue( $applies );
	}

	/**
	 * It should apply to query in REST context
	 *
	 * @test
	 * @preserveGlobalState  disabled
	 * @runInSeparateProcess to avoid carrying over the const to other tests
	 */
	public function should_apply_to_query_in_rest_context() {
		$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );
		define( 'REST_REQUEST', true );

		$modifier = new Events_Only_Modifier();
		$applies  = $modifier->applies_to( $query );

		$this->assertTrue( $applies );
	}
}
