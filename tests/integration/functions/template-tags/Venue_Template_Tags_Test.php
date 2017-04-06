<?php

namespace Tribe\Events;

use \Codeception\TestCase\WPTestCase;

class Venue_Template_Tags_Test extends \Codeception\TestCase\WPTestCase {
	protected $posts = [];
	protected $venue_url = 'http://power.of.greyskull/by-the';

	/**
	 * Create a set of test events and venues.
	 */
	public function setUp() {
		parent::setUp();
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
		$settings['EventStartDate'] = date( 'Y-m-d', strtotime( '+1 day' ) );
		$settings['EventEndDate'] = date( 'Y-m-d', strtotime( '+1 day' ) );
		$this->posts['no_venue'] = tribe_create_event( $settings );

		// Create an event with a venue
		$settings['EventVenueID'] = $this->posts['venue_with_url'];
		$this->posts['has_venue'] = tribe_create_event( $settings );
	}

	/**
	 * Remove our test events and venues.
	 */
	public function tearDown() {
		foreach ( $this->posts as $test_post_id ) {
			wp_delete_post( $test_post_id, true );
		}
	}

	/**
	 * Confirm that tribe_get_venue_website_url() retrieves the expected
	 * results for events with and without venues and for venues with and
	 * without URLs.
	 */
	public function test_get_venue_website_url() {
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
}