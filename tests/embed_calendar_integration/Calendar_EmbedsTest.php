<?php

namespace TEC\Events\Calendar_Embeds;

use TEC\Events\Calendar_Embeds\Calendar_Embeds;

class Calendar_EmbedsTest extends \Codeception\TestCase\WPTestCase {
	public function testRegisterPostType() {
		// Mock the WordPress functions
		global $wp_post_types;

		tribe( Calendar_Embeds::class )->register_post_type();

		$this->assertArrayHasKey( Calendar_Embeds::POSTTYPE, $wp_post_types );
		$this->assertEquals( 'Calendar Embeds', $wp_post_types[ Calendar_Embeds::POSTTYPE ]->labels->name );
		$this->assertFalse( $wp_post_types[ Calendar_Embeds::POSTTYPE ]->public );
		$this->assertTrue( $wp_post_types[ Calendar_Embeds::POSTTYPE ]->show_ui );
	}
}
