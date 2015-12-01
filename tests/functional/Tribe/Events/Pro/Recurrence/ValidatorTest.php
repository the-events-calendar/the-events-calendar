<?php
namespace Tribe\Events\Pro\Recurrence;

use Tribe__Events__Pro__Recurrence__Validator;

class ValidatorTest extends \WP_UnitTestCase {

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
	 * @test
	 * it should return false if post is not event
	 */
	public function it_should_return_false_if_post_is_not_event() {
		$post_id = $this->factory->post->create();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array() );

		$this->assertFalse( $valid );
	}

	/**
	 * @test
	 * it should return false for empty recurrence meta
	 */
	public function it_should_return_false_for_empty_recurrence_meta() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array() );

		$this->assertFalse( $valid );
	}

	/**
	 * @test
	 * it should return true for any recurrence that is not custom
	 */
	public function it_should_return_true_for_any_recurrence_that_is_not_custom() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array(
			'type' => 'Not Custom',
		) );

		$this->assertTrue( $valid );
	}

	/**
	 * @test
	 * it should return false if custom recurrence is missing type
	 */
	public function it_should_return_false_if_custom_recurrence_is_missing_type() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array(
			'type' => \Tribe__Events__Pro__Recurrence__Custom_Types::SLUG,
		) );

		$this->assertFalse( $valid );
	}

	public function recurrenceData() {
		return array_map( function ( $val ) {
			return [ $val ];
		}, \Tribe__Events__Pro__Recurrence__Custom_Types::data_keys() );
	}

	/**
	 * @test
	 * it should return false if custom recurrence is missing data
	 * @dataProvider recurrenceData
	 */
	public function it_should_return_false_if_custom_recurrence_is_missing_data( $key ) {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array(
			'type'   => \Tribe__Events__Pro__Recurrence__Custom_Types::SLUG,
			'custom' => array( 'type' => 'Foo' ),
		) );

		$this->assertFalse( $valid );
	}

	/**
	 * @test
	 * it should return false if monthly recurrence is missing day or number
	 */
	public function it_should_return_false_if_monthly_recurrence_is_missing_day_or_number() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array(
			'type'   => \Tribe__Events__Pro__Recurrence__Custom_Types::SLUG,
			'custom' => array(
				'type'  => \Tribe__Events__Pro__Recurrence__Custom_Types::MONTHLY_CUSTOM_TYPE,
				'month' => array()
			),
		) );

		$this->assertFalse( $valid );
	}

	/**
	 * @test
	 * it should return true if monthly recurrence has day and number
	 */
	public function it_should_return_true_if_monthly_recurrence_has_day_and_number() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$meta  = array(
			'type'   => \Tribe__Events__Pro__Recurrence__Custom_Types::SLUG,
			'custom' => array(
				'type'  => \Tribe__Events__Pro__Recurrence__Custom_Types::MONTHLY_CUSTOM_TYPE,
				'month' => array(
					'day'    => 23,
					'number' => '1'
				)
			),
		);
		$valid = $sut->is_valid( $post_id, $meta );

		$this->assertTrue( $valid );
	}

	/**
	 * @test
	 * it should return false if monthly recurrence has no number
	 */
	public function it_should_return_false_if_monthly_recurrence_has_no_number() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array(
			'type'   => \Tribe__Events__Pro__Recurrence__Custom_Types::SLUG,
			'custom' => array(
				'type'  => \Tribe__Events__Pro__Recurrence__Custom_Types::MONTHLY_CUSTOM_TYPE,
				'month' => array( 'day' => 23 )
			),
		) );

		$this->assertFalse( $valid );
	}

	/**
	 * @test
	 * it should return false if monthly recurrence has no day
	 */
	public function it_should_return_false_if_monthly_recurrence_has_no_day() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array(
			'type'   => \Tribe__Events__Pro__Recurrence__Custom_Types::SLUG,
			'custom' => array(
				'type'  => \Tribe__Events__Pro__Recurrence__Custom_Types::MONTHLY_CUSTOM_TYPE,
				'month' => array( 'number' => '11' )
			),
		) );

		$this->assertFalse( $valid );
	}

	/**
	 * @test
	 * it should return false if yearly recurrence is missing month day
	 */
	public function it_should_return_false_if_yearly_recurrence_is_missing_month_day() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array(
			'type'   => \Tribe__Events__Pro__Recurrence__Custom_Types::SLUG,
			'custom' => array(
				'type' => \Tribe__Events__Pro__Recurrence__Custom_Types::YEARLY_CUSTOM_TYPE,
				'year' => array( 'foo' => 'bar' )
			),
		) );

		$this->assertFalse( $valid );
	}

	/**
	 * @test
	 * it should return false if yearly recurrence has dash month day
	 */
	public function it_should_return_false_if_yearly_recurrence_has_dash_month_day() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array(
			'type'   => \Tribe__Events__Pro__Recurrence__Custom_Types::SLUG,
			'custom' => array(
				'type' => \Tribe__Events__Pro__Recurrence__Custom_Types::YEARLY_CUSTOM_TYPE,
				'year' => array( 'month-day' => '-' )
			),
		) );

		$this->assertFalse( $valid );
	}

	/**
	 * @test
	 * it should return true if yearly recurrence has month day
	 */
	public function it_should_return_true_if_yearly_recurrence_has_month_day() {
		$post_id = $this->get_event();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$meta  = array(
			'type'   => \Tribe__Events__Pro__Recurrence__Custom_Types::SLUG,
			'custom' => array(
				'type' => \Tribe__Events__Pro__Recurrence__Custom_Types::YEARLY_CUSTOM_TYPE,
				'year' => array( 'month-day' => 23 )
			),
		);
		$valid = $sut->is_valid( $post_id, $meta );

		$this->assertTrue( $valid );
	}

	/**
	 * @return mixed
	 */
	protected function get_event() {
		$post_id = $this->factory->post->create( [ 'post_type' => \Tribe__Events__Main::POSTTYPE ] );

		return $post_id;
	}
}