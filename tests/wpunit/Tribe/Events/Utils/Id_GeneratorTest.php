<?php
namespace TEC\Tests\Utils;

class Id_GeneratorTest extends \\Codeception\TestCase\WPTestCase {

	public function tearDown() {
		\Tribe__Events__Utils__Id_Generator::reset();
		parent::tearDown();
	}

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

	public function nonStringArguments() {
		return [
			[ array() ],
			[ 12.9 ],
			[ new \stdClass() ],
			[ array( 'foo' ) ],
			[ null ],
			[ false ]
		];
	}

	/**
	 * @test
	 * it should throw when passing a non string argument
	 * @dataProvider nonStringArguments
	 */
	public function it_should_throw_when_passing_a_non_string_argument( $non_string_argument ) {
		$this->setExpectedException( 'InvalidArgumentException' );

		\Tribe__Events__Utils__Id_Generator::generate_id( $non_string_argument );
	}

	/**
	 * @test
	 * it should throw when passing a non string argument for group
	 * @dataProvider nonStringArguments
	 */
	public function it_should_throw_when_passing_a_non_string_argument_for_group( $non_string_argument ) {
		$this->setExpectedException( 'InvalidArgumentException' );

		\Tribe__Events__Utils__Id_Generator::generate_id( 'foo', $non_string_argument );
	}

	/**
	 * @test
	 * it should accept an int as string arg
	 */
	public function it_should_accept_an_int_as_string_arg() {
		$out = \Tribe__Events__Utils__Id_Generator::generate_id( 23 );

		$this->assertEquals( '23-0', $out );
	}

	/**
	 * @test
	 * it should accept an int as group arg
	 */
	public function it_should_accept_an_int_as_group_arg() {
		$out = \Tribe__Events__Utils__Id_Generator::generate_id( 'foo', 23 );

		$this->assertEquals( 'foo-0', $out );
	}

	/**
	 * @test
	 * it should allow for group resets
	 */
	public function it_should_allow_for_group_resets() {
		\Tribe__Events__Utils__Id_Generator::generate_id( 'foo', 'baz' );
		\Tribe__Events__Utils__Id_Generator::generate_id( 'foo', 'baz' );
		\Tribe__Events__Utils__Id_Generator::generate_id( 'foo', 'baz' );
		\Tribe__Events__Utils__Id_Generator::generate_id( 'tec', 'bar' );
		\Tribe__Events__Utils__Id_Generator::generate_id( 'tec', 'bar' );
		\Tribe__Events__Utils__Id_Generator::generate_id( 'tec', 'bar' );

		\Tribe__Events__Utils__Id_Generator::reset( 'baz' );

		$baz_out = \Tribe__Events__Utils__Id_Generator::generate_id( 'foo', 'baz' );
		$bar_out = \Tribe__Events__Utils__Id_Generator::generate_id( 'tec', 'bar' );
		$this->assertEquals( 'foo-0', $baz_out );
		$this->assertEquals( 'tec-3', $bar_out );
	}
}
