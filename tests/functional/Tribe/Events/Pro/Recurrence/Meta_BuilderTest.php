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

	/**
	 * @test
	 * it should set the recurrence end if empty
	 */
	public function it_should_set_the_recurrence_end_if_empty() {
		$data     = array(
			'EventStartDate' => 'today 5pm',
			'EventEndDate'   => 'today 8pm',
			'recurrence'     => array(
				'rules' => array(
					array(
						'type' => 'yearly',
						'end'  => 'some day'
					),
					'custom' => 'foo bar'
				)
			)
		);
		$utils    = $this->getMock( 'Tribe__Events__Pro__Recurrence__Utils' );
		$end_date = 'end_date';
		$utils->expects( $this->once() )->method( 'datetime_from_format' )->willReturn( $end_date );
		$utils->expects( $this->once() )->method( 'is_valid' )->willReturn( true );

		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data, $utils );
		$meta = $sut->build_meta();

		$this->assertNotEmpty( $meta['rules'][0]['end'] );
		$this->assertEquals( 'end_date', $meta['rules'][0]['end'] );
	}

	/**
	 * @test
	 * it should unset the custom key if present
	 */
	public function it_should_unset_the_custom_key_if_present() {
		$data     = array(
			'EventStartDate' => 'today 5pm',
			'EventEndDate'   => 'today 8pm',
			'recurrence'     => array(
				'rules' => array(
					array(
						'type' => 'yearly',
						'end'  => 'some day'
					),
					'custom' => 'foo bar'
				)
			)
		);
		$utils    = $this->getMock( 'Tribe__Events__Pro__Recurrence__Utils' );
		$end_date = 'end_date';
		$utils->expects( $this->once() )->method( 'datetime_from_format' )->willReturn( $end_date );
		$utils->expects( $this->once() )->method( 'is_valid' )->willReturn( true );

		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data, $utils );
		$meta = $sut->build_meta();

		$this->assertArrayNotHasKey( 'custom', $meta['rules'][0] );
	}

	public function customTypes() {
		return [
			[ 'date' ],
			[ 'day' ],
			[ 'week' ],
			[ 'month' ],
			[ 'year' ]
		];
	}

	/**
	 * @test
	 * it should prune custom types
	 * @dataProvider customTypes
	 */
	public function it_should_prune_custom_types( $type ) {
		$custom_types = array(
			'date',
			'day',
			'week',
			'month',
			'year',
		);
		$data         = array(
			'EventStartDate' => 'today 5pm',
			'EventEndDate'   => 'today 8pm',
			'recurrence'     => array(
				'rules' => array(
					array(
						'type'   => 'Custom',
						'custom' => array( 'type' => $type ),
						'end'    => 'some day'
					)
				)
			)
		);
		foreach ( $custom_types as $t ) {
			$data['recurrence']['rules'][0]['cusom'][ $t ] = 'foo';
		}
		$utils    = $this->getMock( 'Tribe__Events__Pro__Recurrence__Utils' );
		$end_date = 'end_date';
		$utils->expects( $this->once() )->method( 'datetime_from_format' )->willReturn( $end_date );
		$utils->expects( $this->once() )->method( 'is_valid' )->willReturn( true );

		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data, $utils );
		$meta = $sut->build_meta();

		foreach ( array_diff( $custom_types, (array) $type ) as $to_prune ) {
			$this->assertArrayNotHasKey( $to_prune, $meta['rules'][0]['custom'] );
		}
	}

	/**
	 * @test
	 * it should set the event start date on the recurrence rule
	 */
	public function it_should_set_the_event_start_date_on_the_recurrence_rule() {
		$data     = array(
			'EventStartDate' => 'today 5pm',
			'EventEndDate'   => 'today 8pm',
			'recurrence'     => array(
				'rules' => array(
					array(
						'type' => 'yearly',
						'end'  => 'some day'
					)
				)
			)
		);
		$utils    = $this->getMock( 'Tribe__Events__Pro__Recurrence__Utils' );
		$end_date = 'end_date';
		$utils->expects( $this->once() )->method( 'datetime_from_format' )->willReturn( $end_date );
		$utils->expects( $this->once() )->method( 'is_valid' )->willReturn( true );

		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data, $utils );
		$meta = $sut->build_meta();

		$this->assertNotEmpty( 'EventStartDate', $meta['rules'][0]['EventStartDate'] );
		$this->assertEquals( 'today 5pm', $meta['rules'][0]['EventStartDate'] );
	}

	/**
	 * @test
	 * it should set the event end date on the recurrence rule
	 */
	public function it_should_set_the_event_end_date_on_the_recurrence_rule() {
		$data     = array(
			'EventStartDate' => 'today 5pm',
			'EventEndDate'   => 'today 8pm',
			'recurrence'     => array(
				'rules' => array(
					array(
						'type' => 'yearly',
						'end'  => 'some day'
					)
				)
			)
		);
		$utils    = $this->getMock( 'Tribe__Events__Pro__Recurrence__Utils' );
		$end_date = 'end_date';
		$utils->expects( $this->once() )->method( 'datetime_from_format' )->willReturn( $end_date );
		$utils->expects( $this->once() )->method( 'is_valid' )->willReturn( true );

		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data, $utils );
		$meta = $sut->build_meta();

		$this->assertNotEmpty( 'EventEndDate', $meta['rules'][0]['EventEndDate'] );
		$this->assertEquals( 'today 8pm', $meta['rules'][0]['EventEndDate'] );
	}

	/**
	 * @test
	 * it should not add invalid rules to the recurrence meta
	 */
	public function it_should_not_add_invalid_rules_to_the_recurrence_meta() {
		$data     = array(
				'EventStartDate' => 'today 5pm',
				'EventEndDate'   => 'today 8pm',
				'recurrence'     => array(
						'rules' => array(
								array(
										'type' => 'yearly',
										'end'  => 'some day'
								)
						)
				)
		);
		$utils    = $this->getMock( 'Tribe__Events__Pro__Recurrence__Utils' );
		$end_date = 'end_date';
		$utils->expects( $this->once() )->method( 'datetime_from_format' )->willReturn( $end_date );
		$utils->expects( $this->once() )->method( 'is_valid' )->willReturn( false );

		$sut  = new \Tribe__Events__Pro__Recurrence__Meta_Builder( 10, $data, $utils );
		$meta = $sut->build_meta();

		$this->assertEmpty( $meta['rules'] );
	}
}