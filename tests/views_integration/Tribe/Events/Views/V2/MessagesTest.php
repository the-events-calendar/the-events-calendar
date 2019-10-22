<?php
namespace Tribe\Events\Views\V2;


use Codeception\TestCase\WPTestCase;

class MessagesTest extends WPTestCase {

	public function render_strategy_priority_data_set() {
		return [
			'priority_last' => [
				Messages::RENDER_STRATEGY_PRIORITY_LAST,
				[
					Messages::TYPE_NOTICE => [ 'Notice 10.2' ],
				],
			],
			'priority_first' => [
				Messages::RENDER_STRATEGY_PRIORITY_FIRST,
				[
					Messages::TYPE_NOTICE => [ 'Notice 10.1' ],
				],
			],
		];
	}

	/**
	 * It should correctly render with priority policy.
	 *
	 * @test
	 * @dataProvider render_strategy_priority_data_set
	 */
	public function should_correctly_render_with_priority_policy( $render_strategy, $expected ) {
		$messages = new Messages( $render_strategy );
		$messages->insert( Messages::TYPE_NOTICE, 'Notice 12.1', 12 );
		$messages->insert( Messages::TYPE_NOTICE, 'Notice 12.2', 12 );
		$messages->insert( MESSAGES::TYPE_NOTICE, 'Notice 10.1', 10 );
		$messages->insert( MESSAGES::TYPE_NOTICE, 'Notice 10.2', 10 );
		$messages->insert( MESSAGES::TYPE_NOTICE, 'Notice 14.1', 14 );
		$messages->insert( MESSAGES::TYPE_NOTICE, 'Notice 14.2', 14 );

		$this->assertEquals( $expected, $messages->to_array() );
	}

	public function placeholder_test_data_set() {
		return [
			'test_wo_positions' => [ 'Test %s is %d.', [ 'foo', 23 ], 'Test foo is 23.' ],
			'test_w_positions'  => [
				'Test %1$s, %1$s, %1$s is %2$d,%3$d.',
				[ 'foo', 23, 89 ],
				'Test foo, foo, foo is 23,89.'
			],
		];
	}

	/**
	 * It should allow passing values for placeholder
	 *
	 * @test
	 * @dataProvider placeholder_test_data_set
	 */
	public function should_allow_passing_values_for_placeholder( $message, $values, $expected ) {
		add_filter( 'tribe_events_views_v2_messages_map', static function () use ( $message ) {
			return [
				'test' => $message,
			];
		} );

		$this->assertEquals( $expected, Messages::for_key( 'test', ...$values ) );
	}

	/**
	 * It should allow resetting messages
	 *
	 * @test
	 */
	public function should_allow_resetting_messages() {
		$messages = new Messages( Messages::RENDER_STRATEGY_LIST );
		$messages->insert( Messages::TYPE_NOTICE, 'Notice 1', 10 );
		$messages->insert( Messages::TYPE_NOTICE, 'Notice 2', 12 );
		$messages->insert( 'some_other_type', 'Message 1', 10 );
		$messages->insert( 'some_other_type', 'Message 2', 12 );

		$expected = [
			Messages::TYPE_NOTICE => [
				10 => [ 'Notice 1' ],
				12 => [ 'Notice 2' ],
			],
			'some_other_type'     => [
				10 => [ 'Message 1' ],
				12 => [ 'Message 2' ],
			],
		];
		$this->assertEquals( $expected, $messages->to_array() );

		$messages->reset( Messages::TYPE_NOTICE, 10 );

		$expected = [
			Messages::TYPE_NOTICE => [
				12 => [ 'Notice 2' ],
			],
			'some_other_type'     => [
				10 => [ 'Message 1' ],
				12 => [ 'Message 2' ],
			],
		];
		$this->assertEquals( $expected, $messages->to_array() );

		$messages->reset( Messages::TYPE_NOTICE );

		$expected = [
			'some_other_type' => [
				10 => [ 'Message 1' ],
				12 => [ 'Message 2' ],
			],
		];
		$this->assertEquals( $expected, $messages->to_array() );

		$messages->reset();

		$this->assertEquals( [], $messages->to_array() );
	}
}