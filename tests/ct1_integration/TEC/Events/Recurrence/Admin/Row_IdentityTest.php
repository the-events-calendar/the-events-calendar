<?php

namespace TEC\Events\Recurrence\Admin;

use Codeception\TestCase\WPTestCase;

class Row_IdentityTest extends WPTestCase {
	/** @test */
	public function should_combine_all_identity_information_into_one_accessible_icon(): void {
		$data = [
			'postId' => 10001056,
			'schedule' => 'rules',
			'scheduleLabel' => 'Recurring event',
			'scheduled' => true,
			'isOccurrence' => true,
			'locked' => true,
			'series' => [ [ 'title' => 'Music <script>alert(1)</script>' ] ],
		];
		ob_start();
		( new Row_Identity() )->render( $data, [ 'title' => 'Events Calendar Pro is inactive', 'message' => 'Existing dates are preserved.' ] );
		$html = ob_get_clean();
		$this->assertSame( 1, substr_count( $html, '<button ' ) );
		$this->assertSame( 1, substr_count( $html, 'role="tooltip"' ) );
		$this->assertStringNotContainsString( '<a ', $html );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringNotContainsString( 'dashicons', $html );
		preg_match_all( '/data-icon="([^"]+)"/', $html, $icons );
		$this->assertSame( [ 'map-marker' ], $icons[1] );
		preg_match_all( '/aria-describedby="([^"]+)"/', $html, $ids );
		$this->assertCount( 1, array_unique( $ids[1] ) );
		foreach ( $ids[1] as $id ) {
			$this->assertStringContainsString( 'id="' . $id . '"', $html );
		}
		$this->assertStringContainsString( 'Occurrence · Recurring event', $html );
		$this->assertStringContainsString( 'tec-occurrence-admin__indicator--locked', $html );
		$this->assertStringContainsString( 'Part of Series:', $html );
		$this->assertStringContainsString( 'Events Calendar Pro is inactive.', $html );
	}

	/** @test */
	public function should_show_only_the_schedule_for_single_events_and_drafts(): void {
		foreach ( [ true, false ] as $scheduled ) {
			ob_start();
			( new Row_Identity() )->render( [ 'postId' => 42, 'schedule' => 'single', 'scheduleLabel' => 'Single event', 'scheduled' => $scheduled, 'isOccurrence' => false, 'locked' => false, 'series' => [] ], [] );
			$html = ob_get_clean();
			$this->assertSame( 1, substr_count( $html, '<button ' ) );
			$this->assertStringContainsString( 'data-icon="calendar"', $html );
			$this->assertStringContainsString( $scheduled ? 'Single event · One scheduled date' : 'Not scheduled', $html );
			if ( ! $scheduled ) {
				$this->assertStringNotContainsString( 'One scheduled date', $html );
			}
		}
	}
}
