<?php

namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

use TEC\Events\Custom_Tables\V1\Activation;

class Schema_BuilderTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should update blog tables at most once a day on switch_blog
	 *
	 * Here we exercise the `switch_blog` action to test the transient
	 * based call to the Schema up logic.
	 *
	 * @test
	 */
	public function should_update_blog_tables_at_most_once_a_day_on_switch_blog() {
		$main_blog_id = get_current_blog_id();
		$blog_2_id = static::factory()->blog->create( [ 'path' => '/' . md5( uniqid( 'site_', true ) ) ] );
		$blog_3_id = static::factory()->blog->create( [ 'path' => '/' . md5( uniqid( 'site_', true ) ) ] );

		$this->reset_activation_state( $main_blog_id );
		$this->reset_activation_state( $blog_2_id );
		$this->reset_activation_state( $blog_3_id );

		$calls_by_blog_id = (object) [
			$main_blog_id => 0,
			$blog_2_id    => 0,
			$blog_3_id    => 0,
		];
		add_action( 'tec_events_custom_tables_v1_schema_builder_after_up', static function () use ( $calls_by_blog_id ): void {
			$calls_by_blog_id->{get_current_blog_id()} ++;
		} );

		$main_blog_now = time();
		switch_to_blog( $main_blog_id );
		switch_to_blog( $main_blog_id );
		switch_to_blog( $main_blog_id );
		switch_to_blog( $main_blog_id );
		switch_to_blog( $main_blog_id );
		// Use a delta: being down to the second is not relevant as long as it's about this time. CI can be slower.
		$this->assertEqualsWithDelta( $main_blog_now, get_transient( Activation::ACTIVATION_TRANSIENT ), 2.0 );
		$this->assertEquals( 1, $calls_by_blog_id->{$main_blog_id} );

		$blog_2_now = time();
		switch_to_blog( $blog_2_id );
		switch_to_blog( $blog_2_id );
		switch_to_blog( $blog_2_id );
		switch_to_blog( $blog_2_id );
		switch_to_blog( $blog_2_id );
		switch_to_blog( $blog_2_id );
		// Use a delta: being down to the second is not relevant as long as it's about this time. CI can be slower.
		$this->assertEqualsWithDelta( $blog_2_now, get_transient( Activation::ACTIVATION_TRANSIENT ), 2.0 );
		$this->assertEquals( 1, $calls_by_blog_id->{$blog_2_id} );

		$blog_3_now = time();
		switch_to_blog( $blog_3_id );
		switch_to_blog( $blog_3_id );
		switch_to_blog( $blog_3_id );
		switch_to_blog( $blog_3_id );
		switch_to_blog( $blog_3_id );
		switch_to_blog( $blog_3_id );
		// Use a delta: being down to the second is not relevant as long as it's about this time. CI can be slower.
		$this->assertEqualsWithDelta( $blog_3_now, get_transient( Activation::ACTIVATION_TRANSIENT ), 2.0 );
		$this->assertEquals( 1, $calls_by_blog_id->{$blog_3_id} );
	}

	/**
	 * It should update blog tables once on call to update_blog_tables
	 *
	 * Here we call the call the Schema up logic directly to ensure it
	 * will not fire more than once per request, per blog.
	 *
	 * @test
	 */
	public function should_update_blog_tables_once_on_call_to_update_blog_tables() {
		$main_blog_id = get_current_blog_id();
		$blog_2_id = static::factory()->blog->create( [ 'path' => '/' . md5( uniqid( 'site_', true ) ) ] );
		$blog_3_id = static::factory()->blog->create( [ 'path' => '/' . md5( uniqid( 'site_', true ) ) ] );

		$calls_by_blog_id = (object) [
			$main_blog_id => 0,
			$blog_2_id    => 0,
			$blog_3_id    => 0,
		];
		add_action( 'tec_events_custom_tables_v1_schema_builder_after_up', static function () use ( $calls_by_blog_id ): void {
			$calls_by_blog_id->{get_current_blog_id()} ++;
		} );

		$schema_builder = tribe( Schema_Builder::class );

		$this->assertEquals( 0, $calls_by_blog_id->{$main_blog_id} );
		$this->assertEquals( 0, $calls_by_blog_id->{$blog_2_id} );
		$this->assertEquals( 0, $calls_by_blog_id->{$blog_3_id} );

		switch_to_blog( $main_blog_id );
		$this->reset_activation_state( $main_blog_id );
		$calls_by_blog_id->{$main_blog_id} = 0;
		$this->assertNotEquals( [], $schema_builder->update_blog_tables( $main_blog_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $main_blog_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $main_blog_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $main_blog_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $main_blog_id ) );
		$this->assertEquals( 1, $calls_by_blog_id->{$main_blog_id} );

		switch_to_blog( $blog_2_id );
		$this->reset_activation_state( $blog_2_id );
		$calls_by_blog_id->{$blog_2_id} = 0;
		$this->assertNotEquals( [], $schema_builder->update_blog_tables( $blog_2_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $blog_2_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $blog_2_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $blog_2_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $blog_2_id ) );
		$this->assertEquals( 1, $calls_by_blog_id->{$blog_2_id} );

		switch_to_blog( $blog_3_id );
		$this->reset_activation_state( $blog_3_id );
		$calls_by_blog_id->{$blog_3_id} = 0;
		$this->assertNotEquals( [], $schema_builder->update_blog_tables( $blog_3_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $blog_3_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $blog_3_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $blog_3_id ) );
		$this->assertEquals( [], $schema_builder->update_blog_tables( $blog_3_id ) );
		$this->assertEquals( 1, $calls_by_blog_id->{$blog_3_id} );
	}

	private function reset_activation_state( int $blog_id ): void {
		global $wpdb;
		switch_to_blog( $blog_id );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}options
       			WHERE option_name LIKE '_transient_%'
       			   OR option_name LIKE 'tec_ct1_%_schema_version'" );
		wp_cache_flush();
	}
}
