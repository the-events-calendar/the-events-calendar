<?php
/**
 * Tests organizer creation functionality
 *
 * @since TBD
 */
class Organizer_Creation_Test extends \Codeception\TestCase\WPTestCase {
	protected $post_example_settings;

	function setUp() {
		parent::setUp();
		$this->post_example_settings = array(
			'post_author'      => 5,
			'Organizer'        => 'Steve Jobs',
			'Description'      => 'Chairman, CEO, and a co-founder of Apple Inc',
			'Email'            => 'steve@apple.com',
			'Website'          => 'http://apple.com',
			'Phone'            => '+1 800 676 2775',
		);
	}

	/**
	 * Check to make sure that the post object is created from a returned post ID.
	 *
	 * @uses $post_example_settings
	 * @since TBD
	 */
	public function test_tribe_create_organizer_template_tag_post_object_created() {
		$post = get_post( tribe_create_organizer( $this->post_example_settings ) );

		$this->assertInternalType( 'object', $post );
	}

	/**
	 * Check to make sure that the organizer data is saved properly.
	 *
	 * @uses $post_example_settings
	 * @since TBD
	 */
	public function test_tribe_create_organizer_template_tag_meta_information() {
		$post = get_post( tribe_create_organizer( $this->post_example_settings ) );

		$this->assertEquals( 5, $post->post_author );
		$this->assertEquals( 'Chairman, CEO, and a co-founder of Apple Inc', $post->post_content );

	}

	/**
	 * Check to make sure that the post object is created from a returned post ID.
	 *
	 * @uses $post_example_settings
	 * @since TBD
	 */
	public function test_tribe_create_organizer_API_post_object_created() {
		$post = get_post( Tribe__Events__API::createOrganizer( $this->post_example_settings ) );
		$this->assertInternalType( 'object', $post );
	}

	/**
	 * Check to make sure that the organizer data is saved properly.
	 *
	 * @since TBD
	 */
	public function test_tribe_create_organizer_API_meta_information() {
		$post = get_post( Tribe__Events__API::createOrganizer( $this->post_example_settings ) );

		$this->assertEquals( 5, $post->post_author );
		$this->assertEquals( 'Chairman, CEO, and a co-founder of Apple Inc', $post->post_content );
		$this->assertEquals( '+1 800 676 2775', get_post_meta( $post->ID, '_OrganizerPhone', true ) );
		$this->assertEquals( 'http://apple.com', get_post_meta( $post->ID, '_OrganizerWebsite', true ) );
		$this->assertEquals( 'steve@apple.com', get_post_meta( $post->ID, '_OrganizerEmail', true ) );
	}
}