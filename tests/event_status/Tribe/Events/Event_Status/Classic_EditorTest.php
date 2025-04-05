<?php

namespace Tribe\Events\Event_Status;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Test\Factories\Event;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;

class Classic_EditorTest extends \Codeception\TestCase\WPTestCase {
	use With_Post_Remapping;
	use MatchesSnapshots;
	use With_Uopz;

	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
		$this->set_fn_return( 'wp_create_nonce', '123123' );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	/**
	 * @return Classic_Editor
	 */
	protected function make_instance( ...$inject ) {
		$instance = new Classic_Editor( new Admin_Template(), null );

		return $instance;
	}

	/**
	 * @test
	 */
	public function should_test_render_empty_w_o_event() {
		$editor = $this->make_instance();

		$html = $editor->render( null );

		$this->assertEmpty( $html );
	}

	/**
	 * @test
	 */
	public function should_test_render_with_event() {
		$event = $this->get_mock_event( 'events/single/1.json' );

		$editor = $this->make_instance();
		$html   = $editor->render( $event );

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 */
	public function should_test_render_with_event_and_status_data() {
		$event  = $this->get_mock_event( 'events/single/1.json' );
		$editor = $this->make_instance();
		$data   = [
			'status'        => 'canceled',
			'status-reason' => 'Because',
		];
		$editor->update_fields( $event->ID, $data );
		$html = $editor->render( $event );

		$this->assertMatchesSnapshot( $html );
	}
}
