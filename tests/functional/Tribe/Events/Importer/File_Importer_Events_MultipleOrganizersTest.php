<?php
namespace Tribe\Events\Importer;

require_once "File_Importer_EventsTest.php";

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use org\bovigo\vfs\vfsStream;
use Tribe__Events__Main as Main;
use Tribe__Events__Organizer as Organizer;

class File_Importer_Events_MultipleOrganizersTest extends File_Importer_EventsTest {

	/**
	 * @test
	 * it should keep importing single organizers by name
	 */
	public function it_should_keep_importing_single_organizers_by_name() {
		$organizer_name = 'John Doe';
		$organizer_id   = $this->factory()->post->create( [ 'post_type' => Organizer::POSTTYPE, 'post_title' => $organizer_name ] );
		$this->data     = [
			'organizer_1' => $organizer_name,
			'organizer_2' => $organizer_name,
			'organizer_3' => $organizer_name,
		];

		$sut = $this->make_instance( 'multiple-organizers' );

		$post_id_1 = $sut->import_next_row();
		$post_id_2 = $sut->import_next_row();
		$post_id_3 = $sut->import_next_row();

		$this->assertEquals( $organizer_id, get_post_meta( $post_id_1, '_EventOrganizerID', true ) );
		$this->assertEquals( $organizer_id, get_post_meta( $post_id_2, '_EventOrganizerID', true ) );
		$this->assertEquals( $organizer_id, get_post_meta( $post_id_3, '_EventOrganizerID', true ) );
	}

	/**
	 * @test
	 * it should import a space separated list of organizers a the name of a single organizer
	 */
	public function it_should_import_a_space_separated_list_of_organizers_a_the_name_of_a_single_organizer() {
		$organizer_name = 'Zach Matt Gustavo';
		$organizer_id   = $this->factory()->post->create( [ 'post_type' => Organizer::POSTTYPE, 'post_title' => $organizer_name ] );
		$this->data     = [
			'organizer_1' => $organizer_name,
			'organizer_2' => $organizer_name,
			'organizer_3' => $organizer_name,
		];

		$sut = $this->make_instance( 'multiple-organizers' );

		$post_id_1 = $sut->import_next_row();
		$post_id_2 = $sut->import_next_row();
		$post_id_3 = $sut->import_next_row();

		$this->assertCount( 1, get_post_meta( $post_id_1, '_EventOrganizerID', false ) );
		$this->assertCount( 1, get_post_meta( $post_id_2, '_EventOrganizerID', false ) );
		$this->assertCount( 1, get_post_meta( $post_id_3, '_EventOrganizerID', false ) );

		$this->assertEquals( $organizer_id, get_post_meta( $post_id_1, '_EventOrganizerID', true ) );
		$this->assertEquals( $organizer_id, get_post_meta( $post_id_2, '_EventOrganizerID', true ) );
		$this->assertEquals( $organizer_id, get_post_meta( $post_id_3, '_EventOrganizerID', true ) );
	}

	/**
	 * @test
	 * it should import a single organizer ID
	 */
	public function it_should_import_a_single_organizer_id() {
		$organizer_id = $this->factory()->post->create( [ 'post_type' => Organizer::POSTTYPE, 'post_title' => 'Someone' ] );
		$this->data   = [
			'organizer_1' => $organizer_id,
		];

		$sut = $this->make_instance( 'multiple-organizers' );

		$post_id = $sut->import_next_row();

		$this->assertCount( 1, get_post_meta( $post_id, '_EventOrganizerID', false ) );
		$this->assertEquals( $organizer_id, get_post_meta( $post_id, '_EventOrganizerID', true ) );
	}

	/**
	 * @test
	 * it should import a space separated list of organizer IDs
	 */
	public function it_should_import_a_space_separated_list_of_organizer_ids() {
		$organizer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => Main::ORGANIZER_POST_TYPE ] );
		$this->data    = [
			'organizer_1' => implode( ' ', $organizer_ids ),
		];

		$sut = $this->make_instance( 'multiple-organizers' );

		$post_id = $sut->import_next_row();

		$stored_organizer_ids = get_post_meta( $post_id, '_EventOrganizerID', false );
		$this->assertCount( 3, $stored_organizer_ids );
		$this->assertEqualSets( $organizer_ids, $stored_organizer_ids );
	}

	/**
	 * @test
	 * it should import a space separated list of organizer IDs if not all are organizers
	 */
	public function it_should_not_import_a_space_separated_list_of_organizer_if_not_all_are_organizers() {
		$organizer_ids    = $this->factory()->post->create_many( 3, [ 'post_type' => Main::ORGANIZER_POST_TYPE ] );
		$non_organizer_id = $this->factory()->post->create();
		$organizer_ids[]  = $non_organizer_id;
		$this->data       = [
			'organizer_1' => implode( ' ', $organizer_ids ),
		];

		$sut = $this->make_instance( 'multiple-organizers' );

		$post_id = $sut->import_next_row();

		$this->assertEmpty( get_post_meta( $post_id, '_EventOrganizerID', false ) );
	}

	/**
	 * @test
	 * it should allow for organizers with comma in name to be imported as one
	 */
	public function it_should_allow_for_organizers_with_comma_in_name_to_be_imported_as_one() {
		$organizer_id = $this->factory()->post->create( [ 'post_type' => Main::ORGANIZER_POST_TYPE, 'post_title' => 'Bond, James' ] );
		$this->data   = [
			'organizer_1' => '"Bond, James"',
		];

		$sut = $this->make_instance( 'multiple-organizers' );

		$post_id = $sut->import_next_row();

		$this->assertCount( 1, get_post_meta( $post_id, '_EventOrganizerID', false ) );
		$this->assertEquals( $organizer_id, get_post_meta( $post_id, '_EventOrganizerID', true ) );
	}

	/**
	 * @test
	 * it should import a comma separated list of organizer IDs
	 */
	public function it_should_import_a_comma_separated_list_of_organizer_i_ds() {
		$organizer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => Main::ORGANIZER_POST_TYPE ] );
		$this->data    = [
			'organizer_1' => '"' . implode( ', ', $organizer_ids ) . '"',
		];

		$sut = $this->make_instance( 'multiple-organizers' );

		$post_id = $sut->import_next_row();

		$stored_organizer_ids = get_post_meta( $post_id, '_EventOrganizerID', false );
		$this->assertCount( 3, $stored_organizer_ids );
		$this->assertEqualSets( $organizer_ids, $stored_organizer_ids );
	}

	/**
	 * @test
	 * it should import a tight comma separated list for organizer ids
	 */
	public function it_should_import_a_tight_comma_separated_list_for_organizer_ids() {
		$organizer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => Main::ORGANIZER_POST_TYPE ] );
		$this->data    = [
			'organizer_1' => '"' . implode( ',', $organizer_ids ) . '"',
		];

		$sut = $this->make_instance( 'multiple-organizers' );

		$post_id = $sut->import_next_row();

		$stored_organizer_ids = get_post_meta( $post_id, '_EventOrganizerID', false );
		$this->assertCount( 3, $stored_organizer_ids );
		$this->assertEqualSets( $organizer_ids, $stored_organizer_ids );
	}
}
