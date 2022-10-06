<?php
namespace Tribe\Events\functions\templateTags;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe\Test\PHPUnit\Traits\With_Filter_Manipulation;


class venueTest extends Events_TestCase {
	use With_Filter_Manipulation;

	protected $posts = [];
	protected $venue_url = 'http://power.of.greyskull/by-the';

	/**
	 * Create a set of test events and venues.
	 */
	public function setUp() {
		parent::setUp();
		static::factory()->event     = new Event();
		static::factory()->organizer = new Organizer();
		static::factory()->venue     = new Venue();
	}

	/**
	 * Confirm that tribe_get_venue_website_url() retrieves the expected
	 * results for events with and without venues and for venues with and
	 * without URLs.
	 */
	public function test_get_venue_website_url() {
		$this->setup_venues();
		$venue_website_url = tribe_get_venue_website_url( $this->posts['no_venue'] );
		$this->assertEmpty( $venue_website_url,
			'Events without a venue should return an empty string'
		);

		$venue_website_url = tribe_get_venue_website_url( $this->posts['venue_without_url'] );
		$this->assertEmpty( $venue_website_url,
			'Venue without a URL should return an empty string'
		);

		$venue_website_url = tribe_get_venue_website_url( $this->posts['has_venue'] );
		$this->assertEquals( $this->venue_url, $venue_website_url,
			'An event with a venue (that has a URL) should return the venue URL'
		);

		$venue_website_url = tribe_get_venue_website_url( $this->posts['venue_with_url'] );
		$this->assertEquals( $this->venue_url, $venue_website_url,
			'Venue (that has a URL) should return that URL'
		);
	}

	/**
	 * Confirm that tribe_get_venue_website_link() retrieves the expected
	 * results for events with and without venues and for venues with and
	 * without URLs.
	 */
	public function test_get_venue_website_link() {
		$this->setup_venues();
		$venue_website_link = tribe_get_venue_website_link( $this->posts['no_venue'] );
		$this->assertEmpty( $venue_website_link,
			'Events without a venue should return an empty string'
		);

		$venue_website_link = tribe_get_venue_website_link( $this->posts['venue_without_url'] );
		$this->assertEmpty( $venue_website_link,
			'Venue without a URL should return an empty string'
		);

		$venue_website_link = tribe_get_venue_website_link( $this->posts['has_venue'] );
		$this->assertTrue( $this->is_link_containing( $venue_website_link, $this->venue_url ),
			'An event with a venue (that has a URL) should return the venue URL as an HTML link'
		);

		$venue_website_link = tribe_get_venue_website_link( $this->posts['venue_with_url'] );
		$this->assertTrue( $this->is_link_containing( $venue_website_link, $this->venue_url ),
			'Venue (that has a URL) should return that URL as an HTML link'
		);
	}

	/**
	 * Helper that tries to determine if the provided HTML fragment is an HTML
	 * link containing the specified URL.
	 *
	 * @param string $html_fragment
	 * @param string $url
	 *
	 * @return bool
	 */
	protected function is_link_containing( $html_fragment, $url ) {
		$html_fragment = trim( $html_fragment );

		// Check it opens and closes as expected and contains the URL
		return (
			0 === strpos( $html_fragment, '<a ' )
			&& strpos( $html_fragment, '</a>' ) === strlen( $html_fragment ) - 4
			&& 2 <= strpos( $html_fragment, $url )
		);
	}

	/**
	 * It should allow getting found posts when querying venues
	 *
	 * @test
	 */
	public function should_allow_getting_found_posts_when_querying_venues() {
		$this->factory()->venue->create_many( 5 );

		$this->assertEquals( 5, tribe_get_venues( false, - 1, true, [ 'found_posts' => true ] ) );
	}

	public function truthy_and_falsy_values() {
		return [
			[ 'true', true ],
			[ 'true', true ],
			[ '0', false ],
			[ '1', true ],
			[ 0, false ],
			[ 1, true ],
		];

	}

	/**
	 * It should allow truthy and falsy values for the found_posts argument
	 *
	 * @test
	 * @dataProvider truthy_and_falsy_values
	 */
	public function should_allow_truthy_and_falsy_values_for_the_found_posts_argument( $found_posts, $bool ) {
		$this->factory()->venue->create_many( 5 );

		$this->assertEquals(
			tribe_get_venues( false, - 1, true, [ 'found_posts' => $found_posts ] ),
			tribe_get_venues( false, - 1, true, [ 'found_posts' => $bool ] )
		);
	}

	/**
	 * It should override posts_per_page and paged arguments when using found_posts
	 *
	 * @test
	 */
	public function should_override_posts_per_page_and_paged_arguments_when_using_found_posts() {
		$this->factory()->venue->create_many( 5 );

		$found_posts = tribe_get_venues( false, - 1, true, [ 'found_posts' => true, 'paged' => 2 ] );

		$this->assertEquals( 5, $found_posts );
	}

	/**
	 * It should return 0 when no posts are found and found_posts is set
	 *
	 * @test
	 */
	public function should_return_0_when_no_posts_are_found_and_found_posts_is_set() {
		$found_posts = tribe_get_venues( false, - 1, true, [ 'found_posts' => true] );

		$this->assertEquals( 0, $found_posts );
	}

	public function setup_venues() {
		$settings = array(
			'post_author'           => 3,
			'post_title'            => 'Test event',
			'post_content'          => 'This is event content!',
			'post_status'           => 'publish',
			'EventAllDay'           => false,
			'EventHideFromUpcoming' => true,
			'EventOrganizerID'      => 5,
			'EventVenueID'          => 8,
			'EventShowMapLink'      => true,
			'EventShowMap'          => true,
			'EventStartDate'        => '2012-01-01',
			'EventEndDate'          => '2012-01-03',
			'EventStartHour'        => '01',
			'EventStartMinute'      => '15',
			'EventStartMeridian'    => 'am',
			'EventEndHour'          => '03',
			'EventEndMinute'        => '25',
			'EventEndMeridian'      => 'pm',
		);
		unset( $settings['EventHideFromUpcoming'] );

		// Create a test venue with a URL
		$this->posts['venue_with_url'] = tribe_create_venue( [
			'Venue' => 'Castle Greyskull',
			'URL'   => $this->venue_url
		] );

		// Create a test venue without a URL
		$this->posts['venue_without_url'] = tribe_create_venue( [
			'Venue' => 'Deathstar Loading Dock'
		] );

		// Create a venueless event
		$settings['EventVenueID']   = $this->posts['venue_without_url'];
		$settings['EventStartDate'] = date( 'Y-m-d', strtotime( '+1 day' ) );
		$settings['EventEndDate']   = date( 'Y-m-d', strtotime( '+1 day' ) );
		$this->posts['no_venue']    = tribe_create_event( $settings );

		// Create an event with a venue
		$settings['EventVenueID'] = $this->posts['venue_with_url'];
		$this->posts['has_venue'] = tribe_create_event( $settings );
	}

	/**
	 * Test tribe_get_venue_object returns null for non-existing venue
	 */
	public function test_tribe_get_venue_object_returns_null_for_non_existing_venue() {
		// Sanity check: let's make sure this does not exist.
		$this->assertNull( get_post( 23 ) );

		$this->assertNull( tribe_get_venue_object( 23 ) );
	}

	/**
	 * Test tribe_get_venue_object allows filtering the post before any request is made
	 */
	public function test_tribe_get_venue_object_allows_filtering_the_post_before_any_request_is_made() {
		$venue = static::factory()->venue->create_and_get();

		$count = $this->queries()->countQueries();

		// Delete the cache to make sure a new fetch would be triggered by `get_post` calls.
		wp_cache_delete( $venue->ID, 'posts' );

		add_filter( 'tribe_get_venue_object_before', static function () use ( $venue ) {
			return $venue;
		} );

		// Pass the ID to force a `get_post` call if not filtered.
		tribe_get_venue_object( $venue->ID );

		$this->assertEquals( $count, $this->queries()->countQueries() );
	}

	/**
	 * Test tribe_get_venue_object attaches a default set of properties to the post
	 */
	public function test_tribe_get_venue_object_attaches_a_default_set_of_properties_to_the_post() {
		$venue_id = static::factory()->venue->create();

		$venue = tribe_get_venue_object( $venue_id );

		$expected = [
			'address'         => get_post_meta( $venue_id, '_VenueAddress', true ),
			'country'         => get_post_meta( $venue_id, '_VenueCountry', true ),
			'city'            => get_post_meta( $venue_id, '_VenueCity', true ),
			'state_province'  => get_post_meta( $venue_id, '_VenueStateProvince', true ),
			'state'           => get_post_meta( $venue_id, '_VenueState', true ),
			'province'        => get_post_meta( $venue_id, '_VenueProvince', true ),
			'zip'             => get_post_meta( $venue_id, '_VenueZip', true ),
			'phone'           => tribe_get_phone( $venue_id ),
			'permalink'       => get_the_permalink( $venue_id ),
			'directions_link' => tribe_get_map_link( $venue_id ),
		];

		foreach ( $expected as $key => $value ) {
			$isset_message = "Property '{$key}' is not set on the venue object.";
			$this->assertTrue( isset( $venue->{$key} ), $isset_message );
			$value_message = "Property '{$key}' has wrong value.";
			$this->assertEquals( $value, $venue->{$key}, $value_message );
		}
	}
	/**
	 * Test tribe_get_venue_object returns a WP_Post object
	 */
	public function test_tribe_get_venue_object_returns_a_wp_post_object() {
		$venue = static::factory()->venue->create_and_get();

		$result = tribe_get_venue_object( $venue );

		$this->assertInstanceOf( \WP_Post::class, $result );
	}

	/**
	 * @test tribe_get_venue_object results are cached until post save
	 */
	public function test_tribe_get_venue_object_results_are_cached_until_post_save() {
		$venue_id = static::factory()->venue->create();

		$first_fetch = tribe_get_venue_object( $venue_id );

		$first_fetch_count = $this->queries()->countQueries();

		// Sanity check.
		$this->assertInstanceOf( \WP_Post::class, $first_fetch );
		$this->assertEquals( $venue_id, $first_fetch->ID );

		$second_fetch = tribe_get_venue_object( $venue_id );

		$second_fetch_count = $this->queries()->countQueries();

		$this->assertInstanceOf( \WP_Post::class, $second_fetch );
		$this->assertEquals( $venue_id, $second_fetch->ID );
		$this->assertEquals( $first_fetch_count, $second_fetch_count );

		// Update the venue thus triggering a cache invalidation.
		wp_update_post( [ 'ID' => $venue_id, 'post_title' => 'Updated' ] );

		$third_fetch = tribe_get_venue_object( $venue_id );

		$third_fetch_count = $this->queries()->countQueries();

		$this->assertInstanceOf( \WP_Post::class, $third_fetch );
		$this->assertEquals( $venue_id, $third_fetch->ID );
		$this->assertGreaterThan( $first_fetch_count, $third_fetch_count );
	}

	/**
	 * Test tribe_get_venue_object result is filterable
	 */
	public function test_tribe_get_venue_object_result_is_filterable() {
		$venue_id = static::factory()->venue->create();

		add_filter( 'tribe_get_venue_object', static function ( \WP_Post $venue ) {
			$venue->foo = 'bar';

			return $venue;
		} );

		$venue = tribe_get_venue_object( $venue_id );

		$this->assertTrue( isset( $venue->foo ) );
		$this->assertEquals( 'bar', $venue->foo );
	}

	/**
	 * Test tribe_get_venue_object allows specifying the output format.
	 */
	public function test_tribe_get_venue_object_allows_specifying_the_output_format() {
		$venue_id = static::factory()->venue->create();

		$venue = tribe_get_venue_object( $venue_id );

		$queries_count = $this->queries()->countQueries();

		$this->assertEquals( (array) $venue, tribe_get_venue_object( $venue_id, ARRAY_A ) );
		$this->assertEquals( $queries_count, $this->queries()->countQueries() );
		$this->assertEquals( array_values( (array) $venue ), tribe_get_venue_object( $venue_id, ARRAY_N ) );
		$this->assertEquals( $queries_count, $this->queries()->countQueries() );
	}

	/**
	 * Test tribe_get_region
	 */
	public function test_tribe_get_region() {
		$venue_one_region = 'Alsace';
		$venue_two_region = 'Puglia';
		$venue_one        = static::factory()->venue->create([ 'meta_input' => [
			'_VenueCountry' => 'France',
			'_VenueProvince' => $venue_one_region,
		]]);
		$venue_two     = static::factory()->venue->create(['meta_input' => [
			'_VenueCountry' => 'Italy',
			'_VenueProvince' => $venue_two_region,
		]]);
		// Delete the meta to force the use of the `tribe_get_province` check.
		delete_post_meta($venue_one, '_VenueStateProvince');
		delete_post_meta($venue_two, '_VenueStateProvince');

		global $post;
		$post = $venue_one;

		$this->assertEquals(
			$venue_one_region,
			tribe_get_region() ,
			'When no Venue ID is passed the region should be that of the Venue global post object.'
		);
		$this->assertEquals(
			$venue_two_region,
			tribe_get_region($venue_two) ,
			'When a Venue ID is passed the region should be that of the requested Venue.'
		);
	}


	public function test_tribe_get_full_address() {
		$address_string        = '%%ADDRESS_TEST%%';
		$city_string           = '%%CITY_TEST%%';
		$state_province_string = '%%STATE_PROVINCE_TEST%%';
		$state_string          = '%%STATE_TEST%%';
		$province_string       = '%%PROVINCE_TEST%%';
		$zip_string            = '%%ZIP_TEST%%';
		$country_string        = '%%COUNTRY_TEST%%';

		$venue_with_state_province_id = static::factory()->venue->create( [
			'meta_input' => [
				'_VenueAddress' => $address_string,
				'_VenueCity' => $city_string,
				'_VenueStateProvince' => $state_province_string,
				'_VenueZip' => $zip_string,
				'_VenueCountry' => $country_string,
			]
		] );
		$venue_in_usa_id             = static::factory()->venue->create( [
			'meta_input' => [
				'_VenueAddress' => $address_string,
				'_VenueCity' => $city_string,
				'_VenueState' => $state_string,
				'_VenueStateProvince' => null,
				'_VenueZip' => $zip_string,
				'_VenueCountry' => 'United States',
			]
		] );
		$venue_with_province_id     = static::factory()->venue->create( [
			'meta_input' => [
				'_VenueAddress' => $address_string,
				'_VenueCity' => $city_string,
				'_VenueProvince' => $province_string,
				'_VenueStateProvince' => null,
				'_VenueZip' => $zip_string,
				'_VenueCountry' => $country_string,
			]
		] );

		$event_with_state_province_id = static::factory()->event->create( [
			'meta_input' => [
				'_EventVenueID' => $venue_with_state_province_id,
			]
		] );
		$event_in_usa_id              = static::factory()->event->create( [
			'meta_input' => [
				'_EventVenueID' => $venue_in_usa_id,
			]
		] );
		$event_with_province_id       = static::factory()->event->create( [
			'meta_input' => [
				'_EventVenueID' => $venue_with_province_id,
			]
		] );

		$full_address_html_with_state_province_id = tribe_get_full_address( $event_with_state_province_id );
		$full_address_html_in_usa_id              = tribe_get_full_address( $event_in_usa_id );
		$full_address_html_with_province_id       = tribe_get_full_address( $event_with_province_id );

		$this->assertContains( $address_string, $full_address_html_with_state_province_id, 'Full Address should contain the address' );
		$this->assertContains( $city_string, $full_address_html_with_state_province_id, 'Full Address should contain the city' );
		$this->assertContains( $zip_string, $full_address_html_with_state_province_id, 'Full Address should contain the zip' );
		$this->assertContains( $country_string, $full_address_html_with_state_province_id, 'Full Address should contain the country' );


		$this->assertContains( $state_province_string, $full_address_html_with_state_province_id, 'Full Address for a Venue with StateProvince set should contain the the StateProvince value' );
		$this->assertNotContains( $state_string, $full_address_html_with_state_province_id, 'Full Address for a Venue with StateProvince set should NOT contain the the State value' );
		$this->assertNotContains( $province_string, $full_address_html_with_state_province_id, 'Full Address for a Venue with StateProvince set should NOT contain the the Province value' );

		$this->assertContains( $state_string, $full_address_html_in_usa_id, 'Full Address for a Venue in the US without StateProvince should contain the State' );
		$this->assertNotContains( $state_province_string, $full_address_html_in_usa_id, 'Full Address for a Venue in the US without StateProvince should contain the StateProvince' );
		$this->assertNotContains( $province_string, $full_address_html_in_usa_id, 'Full Address for a Venue in the US without StateProvince should NOT contain the Province' );

		$this->assertContains( $province_string, $full_address_html_with_province_id, 'Full Address for a Venue not the US without StateProvince should contain the Province' );
		$this->assertNotContains( $state_province_string, $full_address_html_with_province_id, 'Full Address for a Venue not the US without StateProvince should NOT contain the StateProvince' );
		$this->assertNotContains( $state_string, $full_address_html_with_province_id, 'Full Address for a Venue not the US without StateProvince should NOT contain the State' );
	}
}
