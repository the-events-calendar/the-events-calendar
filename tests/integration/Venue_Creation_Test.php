<?php
/**
 * Tests venue creation functionality
 *
 * @since 4.6.19
 */
class Venue_Creation_Test extends \Codeception\TestCase\WPTestCase {
	protected $post_example_settings;

	function setUp() {
		parent::setUp();
		$this->post_example_settings = [
			'post_author'      => 2,
			'Venue'            => 'White House',
			'Description'      => 'Home and office of the United States president',
			'post_status'      => 'publish',
			'Address'          => '1600 Pennsylvania Ave NW',
			'City'             => 'Washington, DC',
			'Country'          => 'United States',
			'State'            => 'District of Columbia',
			'Zip'              => '20500',
			'Phone'            => '+1 202-456-1111',
		];

		$this->post_example_settings_intl = [
			'post_author'      => 2,
			'Venue'            => 'Parliament Hill',
			'Description'      => 'Home of the Parliament of Canada',
			'post_status'      => 'publish',
			'Address'          => 'Wellington St',
			'City'             => 'Ottawa',
			'Country'          => 'Canada',
			'Province'         => 'Ontario',
			'Zip'              => 'K1A 0A9',
			'Phone'            => '+1 613-992-4793',
		];
	}

	/**
	 * Check to make sure that the post object is created from a returned post ID.
	 *
	 * @uses $post_example_settings
	 * @since 4.6.19
	 */
	public function test_tribe_create_venue_template_tag_post_object_created() {
		$post = get_post( tribe_create_venue( $this->post_example_settings ) );

		$this->assertInternalType( 'object', $post );
	}

	/**
	 * Check to make sure that the venue data is saved properly.
	 *
	 * @uses $post_example_settings
	 * @since 4.6.19
	 */
	public function test_tribe_create_venue_template_tag_meta_information() {
		$post = get_post( tribe_create_venue( $this->post_example_settings ) );

		$this->assertEquals( 2, $post->post_author );
		$this->assertEquals( 'Home and office of the United States president', $post->post_content );

	}

	/**
	 * Check to make sure that the post object is created from a returned post ID.
	 *
	 * @uses $post_example_settings
	 * @since 4.6.19
	 */
	public function test_tribe_create_venue_API_post_object_created() {
		$post = get_post( Tribe__Events__API::createVenue( $this->post_example_settings ) );
		$this->assertInternalType( 'object', $post );
	}

	/**
	 * Check to make sure that the venue data is saved properly.
	 *
	 * @since 4.6.19
	 */
	public function test_tribe_create_venue_API_meta_information() {
		$post = get_post( Tribe__Events__API::createVenue( $this->post_example_settings ) );

		$this->assertEquals( 2, $post->post_author );
		$this->assertEquals( 'Home and office of the United States president', $post->post_content );
		$this->assertEquals( '1600 Pennsylvania Ave NW', get_post_meta( $post->ID, '_VenueAddress', true ) );
		$this->assertEquals( 'Washington, DC', get_post_meta( $post->ID, '_VenueCity', true ) );
		$this->assertEquals( 'United States', get_post_meta( $post->ID, '_VenueCountry', true ) );
		$this->assertEquals( 'District of Columbia', get_post_meta( $post->ID, '_VenueState', true ) );
		$this->assertEquals( '20500', get_post_meta( $post->ID, '_VenueZip', true ) );
		$this->assertEquals( '+1 202-456-1111', get_post_meta( $post->ID, '_VenuePhone', true ) );
	}

	/**
	 * Check to make sure that the venue data is saved properly.
	 *
	 * @since 4.6.19
	 */
	public function test_tribe_create_intl_venue_API_meta_information() {
		$post = get_post( Tribe__Events__API::createVenue( $this->post_example_settings_intl ) );

		$this->assertEquals( 2, $post->post_author );
		$this->assertEquals( 'Home of the Parliament of Canada', $post->post_content );
		$this->assertEquals( 'Wellington St', get_post_meta( $post->ID, '_VenueAddress', true ) );
		$this->assertEquals( 'Ottawa', get_post_meta( $post->ID, '_VenueCity', true ) );
		$this->assertEquals( 'Canada', get_post_meta( $post->ID, '_VenueCountry', true ) );
		$this->assertEquals( 'Ontario', get_post_meta( $post->ID, '_VenueState', true ) );
		$this->assertEquals( 'K1A 0A9', get_post_meta( $post->ID, '_VenueZip', true ) );
		$this->assertEquals( '+1 613-992-4793', get_post_meta( $post->ID, '_VenuePhone', true ) );
	}

}
