<?php

namespace Tribe\Events\Pro\Recurrence;


use Tribe__Events__Main as Main;
use Tribe__Events__Pro__Recurrence__Meta as Meta;

class MetaTest extends \Codeception\TestCase\WPTestCase {

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
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Meta::class, $sut );
	}

	/**
	 * @test
	 * it should skip malformed recurrence rules meta when getting an event recurrence meta
	 */
	public function it_should_skip_malformed_recurrence_rules_meta_when_getting_an_event_recurrence_meta() {
		$sut        = $this->make_instance();
		$meta_input = [
			'_EventRecurrence' => [
				'rules'      => [
					0 => [
						'type'      => 'Every Week',
						'end-type'  => 'After',
						'end'       => null,
						'end-count' => 5,
					],
					1 => [
						'type'      => 'Unsupported',
						'end-type'  => null,
						'end'       => null,
						'end-count' => null,
					],
				],
				'exclusions' => [ ]
			]
		];
		$event_id   = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'meta_input' => $meta_input ] );

		$recurrence_for_event = $sut->get_recurrence_for_event( $event_id );
		$this->assertArrayHasKey( 'rules', $recurrence_for_event );
		$this->assertArrayHasKey( 'exclusions', $recurrence_for_event );
		$this->assertCount( 1, $recurrence_for_event['rules'] );
	}

	/**
	 * @test
	 * it should return all recurrence rules if none is malformed when getting an event recurrence meta
	 */
	public function it_should_return_all_recurrence_rules_if_none_is_malformed_when_getting_an_event_recurrence_meta() {
		$sut        = $this->make_instance();
		$meta_input = [
			'_EventRecurrence' => [
				'rules'      => [
					0 => [
						'type'      => 'Every Week',
						'end-type'  => 'After',
						'end'       => null,
						'end-count' => 5,
					],
					1 => [
						'type'      => 'Every Day',
						'end-type'  => 'After',
						'end'       => null,
						'end-count' => 5,
					],
				],
				'exclusions' => [ ]
			]
		];
		$event_id   = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'meta_input' => $meta_input ] );

		$recurrence_for_event = $sut->get_recurrence_for_event( $event_id );
		$this->assertArrayHasKey( 'rules', $recurrence_for_event );
		$this->assertArrayHasKey( 'exclusions', $recurrence_for_event );
		$this->assertCount( 2, $recurrence_for_event['rules'] );
	}

	/**
	 * @return Meta
	 */
	private function make_instance() {
		return new Meta();
	}
}
