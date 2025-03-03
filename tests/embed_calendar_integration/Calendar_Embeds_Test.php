<?php

namespace TEC\Events\Calendar_Embeds;

use TEC\Common\Tests\Provider\Controller_Test_Case;

class Calendar_Embeds_Test extends Controller_Test_Case {
	protected string $controller_class = Calendar_Embeds::class;

	/**
	 * @test
	 */
	public function it_should_register_post_type() {
		global $wp_post_types;

		$this->assertArrayHasKey( Calendar_Embeds::POSTTYPE, $wp_post_types );
		$this->assertEquals( 'Calendar Embeds', $wp_post_types[ Calendar_Embeds::POSTTYPE ]->labels->name );
		$this->assertFalse( $wp_post_types[ Calendar_Embeds::POSTTYPE ]->public );
		$this->assertTrue( $wp_post_types[ Calendar_Embeds::POSTTYPE ]->show_ui );
	}
}
