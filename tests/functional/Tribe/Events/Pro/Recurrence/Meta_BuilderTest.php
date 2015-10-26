<?php
namespace Tribe\Events\Pro\Recurrence;

class Meta_BuilderTest extends \Tribe__Events__WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * is instantiatable
	 */
	public function test_is_instantiatable() {
		$this->assertInstanceOf( 'Tribe__Events__Pro__Recurrence__Meta_Builder', new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10 ) );
	}

	/**
	 * @test
	 * it should return zero array for empty input
	 */
	public function it_should_return_empty_array_for_empty_input() {
		$data = array();
		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data );
		$this->assertEquals( $this->get_zero_array(), $sut->build_meta() );
	}

	/**
	 * @test
	 * it should return array with just description if only description set
	 */
	public function it_should_return_array_with_just_description_if_only_description_set() {
		$str                       = 'Description';
		$data                      = array(
			'description' => $str,
			'recurrence'  => array()
		);
		$sut                       = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data );
		$zero_array                = $this->get_zero_array();
		$zero_array['description'] = $str;
		$this->assertEquals( $zero_array, $sut->build_meta() );
	}

	/**
	 * @test
	 * it should return zero array if rules and exclusions are empty
	 */
	public function it_should_return_zero_array_if_rules_and_exclusions_are_empty() {
		$data       = array(
			'recurrence' => array(
				'rules'      => array(),
				'exclusions' => array()
			)
		);
		$sut        = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data );
		$zero_array = $this->get_zero_array();
		$this->assertEquals( $zero_array, $sut->build_meta() );
	}

	/**
	 * @test
	 * it should return empty rules if type is empty
	 */
	public function it_should_return_empty_rules_if_type_is_empty() {
		$data     = array(
			'recurrence' => array(
				'rules' => array( 'type' => '' )
			)
		);
		$sut      = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data );
		$expected = $this->get_zero_array();
		$this->assertEquals( $expected, $sut->build_meta() );
	}

	/**
	 * @test
	 * it should return same if type is None
	 */
	public function it_should_return_same_if_type_is_none() {
		$data     = array(
			'recurrence' => array(
				'rules' => array( 'type' => 'None' )
			)
		);
		$sut      = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data );
		$expected = $this->get_zero_array();
		$this->assertEquals( $expected, $sut->build_meta() );
	}

	/**
	 * @test
	 * it should empty exclusions
	 */
	public function it_should_empty_exclusions() {
		$data = array(
			'recurrence' => array(
				'exclusions' => array( 'key' => 'value' )
			)
		);
		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data );
		$this->assertEquals( $this->get_zero_array(), $sut->build_meta() );
	}

	/**
	 * @test
	 * it should unset custom type text
	 */
	public function it_should_unset_custom_type_text() {
		$data = array(
			'recurrence' => array(
				'custom' => array( 'type-text' => array( 'key' => 'value' ) )
			)
		);
		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data );
		$this->assertEquals( $this->get_zero_array(), $sut->build_meta() );
	}

	/**
	 * @test
	 * it should unset recurrent count text
	 */
	public function it_should_unset_recurrent_count_text() {
		$data = array(
			'recurrence' => array(
				'occurrence-count-text' => array( 'key' => 'value' )
			)
		);
		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data );
		$this->assertEquals( $this->get_zero_array(), $sut->build_meta() );
	}

	protected function get_zero_array() {
		return array(
			'rules'       => array(),
			'exclusions'  => array(),
			'description' => null
		);
	}

}