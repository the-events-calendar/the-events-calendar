<?php

namespace Tribe\Events;

use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe__Events__Organizer as Organizer;

class OrganizerTest extends Events_TestCase {

	/**
	 * @test
	 */
	public function it_should_create_a_new_organizer_when_the_matching_organizer_is_not_editable_by_the_user() {
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		$existing_data  = [
			'Organizer' => 'Shared Organizer',
			'Phone'     => '555-0100',
			'Email'     => 'original@example.test',
			'Website'   => 'https://original.example.test',
		];

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$existing_id = $this->make_instance()->create( $existing_data, 'publish' );

		wp_set_current_user( $contributor_id );
		$submitted_data = [
			'Organizer' => $existing_data['Organizer'],
			'Phone'     => '555-0199',
			'Email'     => 'submitted@example.test',
			'Website'   => 'https://submitted.example.test',
		];
		$organizer_id = $this->make_instance()->create( $submitted_data, 'draft', true );

		$this->assertNotEquals( $existing_id, $organizer_id );
		$this->assertEquals( $submitted_data['Phone'], tribe_get_organizer_phone( $organizer_id ) );
		$this->assertEquals( $existing_data['Phone'], tribe_get_organizer_phone( $existing_id ) );
		$this->assertEquals( $existing_data['Email'], get_post_meta( $existing_id, '_OrganizerEmail', true ) );
		$this->assertEquals( $existing_data['Website'], get_post_meta( $existing_id, '_OrganizerWebsite', true ) );
	}

	/**
	 * @test
	 */
	public function it_should_reuse_the_matching_organizer_when_the_user_can_edit_it() {
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );

		wp_set_current_user( $contributor_id );
		$existing_id    = $this->make_instance()->create(
			[
				'Organizer'   => 'Own Organizer',
				'Phone'       => '555-0100',
				'post_author' => $contributor_id,
			],
			'draft'
		);
		$submitted_data = [
			'Organizer' => get_the_title( $existing_id ),
			'Phone'     => '555-0199',
		];
		$organizer_id   = $this->make_instance()->create( $submitted_data, 'draft', true );

		$this->assertEquals( $existing_id, $organizer_id );
		$this->assertEquals( $submitted_data['Phone'], tribe_get_organizer_phone( $existing_id ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_update_a_linked_organizer_the_user_cannot_edit() {
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		$phone          = '555-0100';

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$existing_id = $this->make_instance()->create(
			[
				'Organizer' => 'Linked Organizer',
				'Phone'     => $phone,
			],
			'publish'
		);

		wp_set_current_user( $contributor_id );
		$organizer_id = $this->make_instance()->save(
			null,
			[
				'OrganizerID' => $existing_id,
				'Phone'       => '555-0199',
			],
			Organizer::POSTTYPE,
			'draft'
		);

		$this->assertEquals( $existing_id, $organizer_id );
		$this->assertEquals( $phone, tribe_get_organizer_phone( $existing_id ) );
	}

	/**
	 * Returns the Organizer instance registered in the container.
	 *
	 * @return Organizer The Organizer instance.
	 */
	private function make_instance(): Organizer {
		return tribe( 'tec.linked-posts.organizer' );
	}
}
