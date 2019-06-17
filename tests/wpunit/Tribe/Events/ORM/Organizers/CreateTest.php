<?php

namespace Tribe\Events\ORM\Organizers;

use Tribe__Events__Main as Main;

class CreateTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * It should allow creating an organizer
	 *
	 * @test
	 */
	public function should_allow_creating_an_organizer() {
		$args      = [
			'title' => 'A test organizer',
		];
		$organizer = tribe_organizers()->set_args( $args )->create();

		$this->assertInstanceOf( \WP_Post::class, $organizer );
		$this->assertEquals( $args['title'], $organizer->post_title );
		$this->assertEquals( '', $organizer->post_content );
	}

	public function meta_key_aliases() {
		return [
			'organizer' => [ 'organizer', 'post_title', 'An organizer' ],
			'phone'     => [ 'phone', '_OrganizerPhone', '123-123-4567' ],
			'website'   => [ 'website', '_OrganizerWebsite', 'https://test.com' ],
			'email'     => [ 'email', '_OrganizerEmail', 'bigcheese@test.com' ],
		];
	}

	/**
	 * It should allow creating a organizer with aliases
	 *
	 * @test
	 * @dataProvider meta_key_aliases
	 */
	public function should_allow_creating_a_organizer_with_aliases( $alias, $meta_key, $value ) {
		$args = [
			'title' => 'A test organizer',
			$alias  => $value,
		];

		if ( 'post_title' === $meta_key ) {
			unset( $args['title'] );
		}

		$organizer = tribe_organizers()->set_args( $args )->create();

		$this->assertInstanceOf( \WP_Post::class, $organizer );
		$this->assertEquals( $value, $organizer->{$meta_key} );
	}

	/**
	 * It should allow creating an organizer with aliases
	 *
	 * @test
	 */
	public function should_allow_creating_an_organizer_with_aliases() {
		$args      = [
			'organizer' => 'A test organizer',
			'phone'     => '123-123-4567',
			'website'   => 'https://test.com',
			'email'     => 'bigcheese@test.com',
		];
		$organizer = tribe_organizers()->set_args( $args )->create();

		$this->assertInstanceOf( \WP_Post::class, $organizer );
		$this->assertEquals( $args['organizer'], $organizer->post_title );
		$this->assertEquals( $args['phone'], get_post_meta( $organizer->ID, '_OrganizerPhone', true ) );
		$this->assertEquals( $args['website'], get_post_meta( $organizer->ID, '_OrganizerWebsite', true ) );
		$this->assertEquals( $args['email'], get_post_meta( $organizer->ID, '_OrganizerEmail', true ) );
		$this->assertEquals( '', $organizer->post_content );
	}

	/**
	 * It should return false if trying to create organizer without min requirements
	 *
	 * @test
	 */
	public function should_return_false_if_trying_to_create_organizer_without_min_requirements() {
		$args      = [];
		$organizer = tribe_organizers()->set_args( $args )->create();

		$this->assertFalse( $organizer );
	}
}
