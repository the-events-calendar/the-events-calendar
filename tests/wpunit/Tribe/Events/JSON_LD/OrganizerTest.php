<?php
namespace Tribe\Events;

use Tribe__Events__Main as Main;
use Tribe__Events__JSON_LD__Organizer as JSON_LD__Organizer;

class JSON_LD__OrganizerTest extends \Codeception\TestCase\WPTestCase {

	protected $organizer;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->create_test_data();
	}

	public function tearDown() {
		// your tear down methods here

		JSON_LD__Organizer::unregister_all();

		// then
		parent::tearDown();
	}

	/**
	 * Create test data
	 *
	 * @since 4.9.2
	 * @return void
	*/
	public function create_test_data() {

		$this->organizer = $this->factory()->post->create_and_get( [
				'post_type' => Main::ORGANIZER_POST_TYPE,
				'post_title' => 'Leo Messi',
				'meta_input' => [
					'_OrganizerPhone'   => '+1 888 8888',
					'_OrganizerWebsite' => 'http://messi.com',
					'_OrganizerEmail'   => 'leo@messi.com',
				],
			] );
	}

	/**
	 * @test
	 * it should be instantiatable
	*/
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( 'Tribe__Events__JSON_LD__Organizer', $sut );
	}

	/**
	 * @test
	 * It should return an empty array if the input data is empty
	 *
	 * @since 4.9.2
	 */
	public function it_should_return_empty_array_when_passed_empty_values() {
		$this->assertEquals( [], $this->make_instance()->get_data( [], [] ) );
	}

	/**
	 * @test
	 * it should return array with one post in it if trying to get data for one organizer
	 *
	 * @since 4.9.2
	 */
	public function it_should_return_array_with_one_post_in_it_if_trying_to_get_data_for_one_post() {
		$post = $this->factory()->post->create();

		$sut  = $this->make_instance();
		$data = $sut->get_data( $post );

		$this->assertInternalType( 'array', $data );
		$this->assertCount( 1, $data );
		$this->assertContainsOnly( 'stdClass', $data );
	}

	/**
	 * @test
	 * Check that the data for the JSON_LD is populated correctly
	 *
	 * @since 4.9.2
	 */
	public function it_should_return_correct_data() {

		$sut          = $this->make_instance();
		$organizer_id = $this->organizer->ID;

		$data    = $sut->get_data( $organizer_id );
		$json_ld = $data[ $organizer_id ];

		// Organizer assertions
		$this->assertEquals( $json_ld->{ '@type' }, 'Person' );
		$this->assertEquals( $json_ld->name, get_the_title( $organizer_id ) );
		$this->assertEquals( $json_ld->telephone, tribe_get_organizer_phone( $organizer_id ) );
		$this->assertEquals( $json_ld->sameAs, tribe_get_organizer_website_url( $organizer_id ) );

	}

	/**
	 * @return Tribe__Events__JSON_LD__Organizer
	 *
	 * @since 4.9.2
	 */
	private function make_instance() {
		return new JSON_LD__Organizer();
	}
}