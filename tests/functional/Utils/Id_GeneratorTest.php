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

	/**
	 * @test
	 * it should generate unique ids
	 */
	public function it_should_generate_unique_ids() {
		$count = 5;
		for ( $i = 0; $i < $count; $i ++ ) {
			$generated_ids[] = \Tribe__Events__Utils__Id_Generator::generate_id( 'tec' );
		}
		$this->assertCount( $count, array_unique( $generated_ids ) );
	}

	/**
	 * @test
	 * it should allow for the definition of an id group
	 */
	public function it_should_allow_for_the_definition_of_an_id_group() {
		$this->assertEquals( 'foo-0', \Tribe__Events__Utils__Id_Generator::generate_id( 'foo', 'group-1' ) );
		$this->assertEquals( 'baz-0', \Tribe__Events__Utils__Id_Generator::generate_id( 'baz', 'group-2' ) );
	}
}
