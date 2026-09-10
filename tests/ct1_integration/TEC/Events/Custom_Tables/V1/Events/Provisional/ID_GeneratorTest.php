<?php

namespace TEC\Events\Custom_Tables\V1\Events\Provisional;

use Codeception\TestCase\WPTestCase;

/**
 * Ported from the Events Calendar Pro suite together with the class.
 *
 * The `AUTO_INCREMENT` manipulations are DDL: they commit the test transaction, so the
 * teardown cleans the posts table and the generator option by hand.
 */
class ID_GeneratorTest extends WPTestCase {
	/**
	 * Cleanup our provisional ID mutations.
	 */
	public function _tearDown() {
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->posts WHERE 1=1" );
		$wpdb->query( "ALTER TABLE $wpdb->posts AUTO_INCREMENT = 1" );

		parent::_tearDown();

		$generator = tribe( ID_Generator::class );
		$generator->uninstall();
		$generator->install();
	}

	/**
	 * @test
	 */
	public function should_install_base_id_when_no_posts() {
		global $wpdb;
		$post      = self::factory()->post->create( [ 'post_type' => 'post' ] );
		$generator = tribe( ID_Generator::class );

		$generator->uninstall();
		$this->assertEquals( $generator->initial_base(), $generator->current() );
		$wpdb->query( "ALTER TABLE $wpdb->posts AUTO_INCREMENT = 1" );
		wp_delete_post( $post, true );
		$generator->install();
		$this->assertGreaterThan( 1, $generator->current() );
		$this->assertGreaterThan( $post, $generator->current() );
		$this->assertGreaterThan( $generator->max_post_id(), $generator->current() );
		$this->assertEquals( $generator->initial_base(), $generator->current() );
	}

	/**
	 * @test
	 */
	public function should_do_single_update() {
		$generator = tribe( ID_Generator::class );

		$generator->uninstall();
		$this->assertEquals( $generator->initial_base(), $generator->current() );
		$generator->update();
		$this->assertEquals( $generator->initial_base() * 2, $generator->current() );
	}

	/**
	 * @test
	 */
	public function should_detect_correct_max_id() {
		global $wpdb;
		$post      = self::factory()->post->create( [ 'post_type' => 'post' ] );
		$generator = tribe( ID_Generator::class );

		$generator->uninstall();
		$this->assertEquals( $generator->initial_base(), $generator->current() );
		// Push our auto increment ID to validate we detect a valid max ID.
		$multiple_increment = 3;
		$auto_increment_id  = $post + $generator->initial_base() * $multiple_increment;
		$wpdb->query( "ALTER TABLE $wpdb->posts AUTO_INCREMENT = $auto_increment_id" );
		$generator->install();
		$this->assertGreaterThan( $auto_increment_id, $generator->current() );
		$this->assertEquals( $auto_increment_id, $generator->max_post_id() );
	}

	/**
	 * @test
	 */
	public function should_sync_past_max_id() {
		global $wpdb;
		$post      = self::factory()->post->create( [ 'post_type' => 'post' ] );
		$generator = tribe( ID_Generator::class );

		$generator->uninstall();
		$this->assertEquals( $generator->initial_base(), $generator->current() );
		// Push our auto increment ID to validate we detect a valid max ID.
		$auto_increment_id = $post + $generator->initial_base() * 3;
		$wpdb->query( "ALTER TABLE $wpdb->posts AUTO_INCREMENT = $auto_increment_id" );
		$generator->sync_above_max_id();
		$this->assertGreaterThan( $generator->max_post_id(), $generator->current() );
	}

	/**
	 * It should provide and unprovide occurrence ids around the current base
	 *
	 * @test
	 */
	public function should_provide_and_unprovide_ids_around_the_current_base() {
		$generator = tribe( ID_Generator::class );
		$generator->uninstall();
		$base = $generator->current();

		$this->assertEquals( $base + 23, $generator->provide_id( 23 ) );
		$this->assertEquals( 23, $generator->unprovide_id( $base + 23 ) );
		// An already baseless ID is returned as is.
		$this->assertEquals( 23, $generator->unprovide_id( 23 ) );
		$this->assertEquals( 0, $generator->provide_id( 0 ) );
		$this->assertEquals( 0, $generator->unprovide_id( 0 ) );
	}
}
