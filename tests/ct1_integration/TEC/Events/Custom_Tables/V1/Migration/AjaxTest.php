<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use Spatie\Snapshots\MatchesSnapshots;

class AjaxTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;

	/**
	 * It should send correct report when migration not required
	 *
	 * @test
	 */
	public function should_send_correct_report_when_migration_not_required(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );
		$state->save();
		// Create a user that would be able to see the report.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		// Create the nonce for the request.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$ajax = tribe( Ajax::class );

		$this->assertMatchesJsonSnapshot( $ajax->send_report( false ) );
	}
}
