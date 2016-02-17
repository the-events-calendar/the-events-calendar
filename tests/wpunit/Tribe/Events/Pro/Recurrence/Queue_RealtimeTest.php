<?php
namespace Tribe\Events\Pro\Recurrence;

class Queue_RealtimeTest extends \Codeception\TestCase\WPTestCase {

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
	 * it should not init update loop if post is not event
	 */
	public function it_should_not_init_update_loop_if_post_is_not_event() {
		global $post;
		$post  = $this->factory->post->create_and_get();
		$queue = $this->getMockBuilder( 'Tribe__Events__Pro__Recurrence__Queue' )
		              ->disableOriginalConstructor()
		              ->getMock();
		$queue->expects( $this->never() )->method( 'is_empty' );

		$sut = new \Tribe__Events__Pro__Recurrence__Queue_Realtime( $queue );

		$this->assertFalse( $sut->post_editor() );
	}

	/**
	 * @test
	 * it should not init update loop if queue is empty
	 */
	public function it_should_not_init_update_loop_if_queue_is_empty() {
		global $post;
		$post  = $this->factory->post->create_and_get( array( 'post_type' => \Tribe__Events__Main::POSTTYPE ) );
		$queue = $this->getMockBuilder( 'Tribe__Events__Pro__Recurrence__Queue' )
		              ->disableOriginalConstructor()
		              ->getMock();
		$queue->expects( $this->once() )->method( 'is_empty' )->willReturn( true );

		$sut = new \Tribe__Events__Pro__Recurrence__Queue_Realtime( $queue );

		$this->assertFalse( $sut->post_editor() );
	}

	/**
	 * @test
	 * it should init update loop if event and queue not empty
	 */
	public function it_should_init_update_loop_if_event_and_queue_not_empty() {
		global $post;
		$post  = $this->factory->post->create_and_get( array( 'post_type' => \Tribe__Events__Main::POSTTYPE ) );
		$queue = $this->make_queue();
		$queue->expects( $this->once() )->method( 'is_empty' )->willReturn( false );

		$sut = new \Tribe__Events__Pro__Recurrence__Queue_Realtime( $queue );

		$this->assertTrue( $sut->post_editor() );
	}

	/**
	 * @test
	 * it should not localize if the post is not an event
	 */
	public function it_should_not_localize_if_the_post_is_not_an_event() {
		global $post;
		$post  = $this->factory->post->create_and_get();
		$queue = $this->make_queue();
		$sut   = new \Tribe__Events__Pro__Recurrence__Queue_Realtime( $queue );

		$data = $sut->update_loop_vars();

		$this->assertFalse( $data );
	}

	public function badPercentageValues() {
		return array_map( function ( $val ) {
			return [ $val ];
		}, [
			null, new \stdClass, 'foo', '', false
		] );
	}

	/**
	 * @test
	 * it should localize zero percentage for bad percentage values
	 * @dataProvider badPercentageValues
	 */
	public function it_should_localize_zero_percentage_for_bad_percentage_values( $bad_percentage ) {
		global $post;
		$post  = $this->factory->post->create_and_get( array( 'post_type' => \Tribe__Events__Main::POSTTYPE ) );
		$queue = $this->make_queue();
		$queue->expects( $this->once() )->method( 'progress_percentage' )->willReturn( $bad_percentage );
		$sut = new \Tribe__Events__Pro__Recurrence__Queue_Realtime( $queue );

		$data = $sut->update_loop_vars();

		$this->assertArrayHasKey( 'progress', $data );
		$this->assertEquals( '0', $data['progress'] );
	}

	/**
	 * @test
	 * it should localize 100 progress if percentage is true
	 */
	public function it_should_localize_100_progress_if_percentage_is_true() {
		global $post;
		$post  = $this->factory->post->create_and_get( array( 'post_type' => \Tribe__Events__Main::POSTTYPE ) );
		$queue = $this->make_queue();
		$queue->expects( $this->once() )->method( 'progress_percentage' )->willReturn( true );
		$sut = new \Tribe__Events__Pro__Recurrence__Queue_Realtime( $queue );

		$data = $sut->update_loop_vars();

		$this->assertArrayHasKey( 'progress', $data );
		$this->assertEquals( '100', $data['progress'] );
	}


	/**
	 * @test
	 * it should exit if nonce is not verified
	 */
	public function it_should_exit_if_nonce_is_not_verified() {
		$_POST['check'] = 'foo';
		$event_id       = $this->factory->post->create( array( 'post_type' => \Tribe__Events__Main::POSTTYPE ) );
		$_POST['event'] = $event_id;
		$ajax           = $this->getMock( 'Tribe__Events__Ajax__Operations' );

		$sut = new \Tribe__Events__Pro__Recurrence__Queue_Realtime( null, $ajax );

		$expected_nonce_action = $sut->get_ajax_nonce_action( $event_id );
		$ajax->expects( $this->once() )
		     ->method( 'verify_or_exit' )
		     ->with( 'foo', $expected_nonce_action, $sut->get_unable_to_continue_processing_data() );

		$sut->ajax();
	}

	/**
	 * @test
	 * it should process batch if valid nonce and not empty queue
	 */
	public function it_should_process_batch_if_valid_nonce_and_not_empty_queue() {
		$event_id       = $this->factory->post->create( array( 'post_type' => \Tribe__Events__Main::POSTTYPE ) );
		$_POST['event'] = $event_id;
		$ajax           = $this->getMock( 'Tribe__Events__Ajax__Operations' );
		$queue          = $this->make_queue();
		$processor      = $this->getMockBuilder( 'Tribe__Events__Pro__Recurrence__Queue_Processor' )
		                       ->disableOriginalConstructor()
		                       ->getMock();

		$sut = new \Tribe__Events__Pro__Recurrence__Queue_Realtime( $queue, $ajax, $processor );

		$_POST['check'] = $sut->get_ajax_nonce();
		$ajax->expects( $this->once() )->method( 'verify_or_exit' )->willReturn( true );
		$queue->expects( $this->any() )->method( 'is_empty' )->willReturn( false );
		$processor->expects( $this->once() )->method( 'process_batch' )->with( $event_id );

		$sut->ajax();
	}

	/**
	 * @test
	 * it should exit done if queue done
	 */
	public function it_should_exit_done_if_queue_done() {
		$event_id       = $this->factory->post->create( array( 'post_type' => \Tribe__Events__Main::POSTTYPE ) );
		$_POST['event'] = $event_id;
		$ajax           = $this->getMock( 'Tribe__Events__Ajax__Operations' );
		$queue          = $this->make_queue();
		$processor      = $this->getMockBuilder( 'Tribe__Events__Pro__Recurrence__Queue_Processor' )
		                       ->disableOriginalConstructor()
		                       ->getMock();

		$sut = new \Tribe__Events__Pro__Recurrence__Queue_Realtime( $queue, $ajax, $processor );

		$_POST['check'] = $sut->get_ajax_nonce();
		$ajax->expects( $this->once() )->method( 'verify_or_exit' )->willReturn( true );
		$percentage = 100;
		$ajax->expects( $this->once() )
		     ->method( 'exit_data' )
		     ->with( $sut->get_progress_message_data( $percentage, true ) );
		$queue->expects( $this->once() )->method( 'progress_percentage' )->willReturn( $percentage );
		$queue->expects( $this->any() )->method( 'is_empty' )->willReturn( true );

		$sut->ajax();
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	private function make_queue() {
		$queue = $this->getMockBuilder( 'Tribe__Events__Pro__Recurrence__Queue' )
		              ->disableOriginalConstructor()
		              ->getMock();

		return $queue;
	}
}