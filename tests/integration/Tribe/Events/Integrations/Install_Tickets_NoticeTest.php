<?php

namespace Tribe\Events\Integrations;

use Tribe\Events\Admin\Notice\Install_Event_Tickets;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use tad\WP\Snapshots\WPHtmlOutputDriver;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for the Install_Event_Tickets class
 */
class Install_Tickets_NoticeTest extends HtmlPartialTestCase {
	use With_Post_Remapping;
	
	protected $partial_path = 'notice/install-event-tickets';

	/**
	 * @test
	 */
	public function it_should_render_notice_install() {
		$et_notice = tribe( Install_Event_Tickets::class );
		$html      = $et_notice->notice_install();
		$driver    = new WPHtmlOutputDriver( home_url(), 'http://tec.dev' );
		$driver->setTimeDependentAttributes( [ 'data-nonce' ] );

		$this->assertMatchesSnapshot( $html, $driver );
	}

	/**
	 * @test
	 */
	public function it_should_render_notice_activate() {
		$et_notice = tribe( Install_Event_Tickets::class );
		$html      = $et_notice->notice_activate();
		$driver    = new WPHtmlOutputDriver( home_url(), 'http://tec.dev' );
		$driver->setTimeDependentAttributes( [ 'data-nonce' ] );

		$this->assertMatchesSnapshot( $html, $driver );
	}

	/**
	 * @test
	 */
	public function test_is_tec_related_page_not_admin() {
		// Setup
		add_filter( 'is_admin', '__return_false' );
		$et_notice = tribe( Install_Event_Tickets::class );
		
		// Test
		$result = $et_notice->is_tec_related_page();
		
		// Clean up
		remove_filter( 'is_admin', '__return_false' );
		
		// Assert
		$this->assertEquals( false, $result );
	}

	/**
	 * @test
	 */
	public function test_is_tec_related_page_no_current_screen() {
		$et_notice = tribe( Install_Event_Tickets::class );
		
		// Save the current global
		$original_screen = isset( $GLOBALS['current_screen'] ) ? $GLOBALS['current_screen'] : null;
		
		// Empty the global
		$GLOBALS['current_screen'] = null;
		
		$result = $et_notice->is_tec_related_page();
		
		// Restore the global
		$GLOBALS['current_screen'] = $original_screen;
		
		$this->assertEquals( false, $result );
	}

	/**
	 * @test
	 * @dataProvider tec_post_type_provider
	 */
	public function test_is_tec_related_page_with_tec_post_types( $post_type ) {
		$et_notice = tribe( Install_Event_Tickets::class );
		
		// Create a mock screen object
		$screen = (object) [
			'post_type' => $post_type,
			'id' => 'edit-' . $post_type
		];
		
		// Save the current global
		$original_screen = isset( $GLOBALS['current_screen'] ) ? $GLOBALS['current_screen'] : null;
		
		// Set the global to our mock
		$GLOBALS['current_screen'] = $screen;
		
		$result = $et_notice->is_tec_related_page();
		
		// Restore the global
		$GLOBALS['current_screen'] = $original_screen;
		
		$this->assertEquals( true, $result );
	}

	/**
	 * @test
	 * @dataProvider tec_screen_id_provider
	 */
	public function test_is_tec_related_page_with_tec_screen_ids( $screen_id ) {
		$et_notice = tribe( Install_Event_Tickets::class );
		
		// Create a mock screen object
		$screen = (object) [
			'post_type' => '',
			'id' => $screen_id
		];
		
		// Save the current global
		$original_screen = isset( $GLOBALS['current_screen'] ) ? $GLOBALS['current_screen'] : null;
		
		// Set the global to our mock
		$GLOBALS['current_screen'] = $screen;
		
		$result = $et_notice->is_tec_related_page();
		
		// Restore the global
		$GLOBALS['current_screen'] = $original_screen;
		
		$this->assertEquals( true, $result );
	}

	/**
	 * @test
	 */
	public function test_is_tec_related_page_with_non_tec_screen() {
		$et_notice = tribe( Install_Event_Tickets::class );
		
		// Create a mock screen object
		$screen = (object) [
			'post_type' => 'post',
			'id' => 'edit-post'
		];
		
		// Save the current global
		$original_screen = isset( $GLOBALS['current_screen'] ) ? $GLOBALS['current_screen'] : null;
		
		// Set the global to our mock
		$GLOBALS['current_screen'] = $screen;
		
		$result = $et_notice->is_tec_related_page();
		
		// Restore the global
		$GLOBALS['current_screen'] = $original_screen;
		
		$this->assertEquals( false, $result );
	}

	/**
	 * Data provider for TEC post types
	 */
	public function tec_post_type_provider() {
		return [
			[ \Tribe__Events__Main::POSTTYPE ],
			[ \Tribe__Events__Organizer::POSTTYPE ],
			[ \Tribe__Events__Venue::POSTTYPE ],
		];
	}

	/**
	 * Data provider for TEC related screen IDs
	 */
	public function tec_screen_id_provider() {
		return [
			[ 'edit-tribe_events' ],
			[ 'tribe_events_page_tec-events-settings' ],
			[ 'tec-tickets-page' ],
			[ 'tribe-common-page' ],
		];
	}
}
