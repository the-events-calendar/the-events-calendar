<?php
namespace Tribe\Events\Pro\Recurrence;

use Prophecy\Argument;
use Tribe__Events__Main as Main;
use Tribe__Events__Pro__Recurrence__Admin_Notices as Admin_Notices;

class Admin_NoticesTest extends \Codeception\TestCase\WPTestCase {

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
	 * display_created_recurrences_notices should not display any notice if not pending event
	 */
	public function test_display_created_recurrences_notices_should_not_display_any_notice_if_not_pending_event() {
		global $post;
		$post   = $this->factory()->post->create_and_get( array( 'post_type' => Main::POSTTYPE ) );
		$notice = $this->prophesize( 'Tribe__Events__Admin__Notices__Notice_Interface' );
		$notice->render( Argument::any(), 'updated' )->shouldNotBeCalled();

		$sut = new Admin_Notices( $notice->reveal() );

		$sut->display_created_recurrences_notice();
	}

	/**
	 * display_created_recurrences_notices should display notice if event is pending
	 */
	public function test_display_created_recurrences_notices_should_display_notice_if_event_is_pending() {
		global $post;
		$post = $this->factory()->post->create_and_get( array( 'post_type' => Main::POSTTYPE ) );
		update_post_meta( $post->ID, '_EventNextPendingRecurrence', array( 'some' => 'recurrence_details' ) );
		$notice = $this->prophesize( 'Tribe__Events__Admin__Notices__Notice_Interface' );
		$notice->render( Argument::any(), 'updated' )->shouldBeCalled();

		$sut = new Admin_Notices( $notice->reveal() );

		$sut->display_created_recurrences_notice();
	}

	    /**
	         * display_editing_all_recurrences_page should print notice
	         */
	        public function test_display_editing_all_recurrences_page_should_print_notice()
	        {
		        $notice = $this->prophesize( 'Tribe__Events__Admin__Notices__Notice_Interface' );
		        $notice->render( Argument::any(), 'updated' )->shouldBeCalled();

		        $sut = new Admin_Notices( $notice->reveal() );

		        $sut->display_editing_all_recurrences_notice();
	        }

}