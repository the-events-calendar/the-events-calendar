<?php
namespace TEC\Tests\Utils;

class Id_GeneratorTest extends \Tribe__Events__WP_UnitTestCase {

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$this->assertInstanceOf( 'Tribe__Events__Utils__Id_Generator', new \Tribe__Events__Utils__Id_Generator() );
	}
}
