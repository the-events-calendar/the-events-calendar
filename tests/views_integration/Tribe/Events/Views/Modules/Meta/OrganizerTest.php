<?php

namespace Tribe\Events\Views\Modules\Meta;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

/**
 * Class OrganizerTest
 *
 * Tests the organizer meta module template, specifically password protection behavior.
 *
 * @package Tribe\Events\Views\Modules\Meta
 */
class OrganizerTest extends HtmlTestCase {
	use MatchesSnapshots;

	/**
	 * Data provider for organizer password protection test cases.
	 *
	 * @return array[] Test cases with organizer protection status.
	 */
	public function organizer_password_protection_data_provider(): array {
		return [
			'non_protected_organizer' => [
				'post_password' => '',
			],
			'protected_organizer'     => [
				'post_password' => 'secret123',
			],
		];
	}

	/**
	 * Test that organizer fields visibility respects password protection.
	 *
	 * When an organizer is password protected, the phone, email, and website
	 * fields should not be displayed. When not protected, they should be visible.
	 *
	 * @test
	 * @dataProvider organizer_password_protection_data_provider
	 *
	 * @param string $post_password The password to set on the organizer (empty for non-protected).
	 */
	public function it_should_correctly_display_organizer_fields_based_on_password_protection( string $post_password ): void {
		// Create an organizer with contact details.
		$organizer_args = [
			'title'   => 'Test Organizer',
			'status'  => 'publish',
			'email'   => 'test@example.com',
			'phone'   => '+1-555-123-4567',
			'website' => 'https://example.com',
		];

		if ( ! empty( $post_password ) ) {
			$organizer_args['post_password'] = $post_password;
		}

		$organizer = tribe_organizers()->set_args( $organizer_args )->create();

		// Create an event with this organizer.
		$event = tribe_events()->set_args( [
			'title'      => 'Test Event',
			'start_date' => '2024-01-15 10:00:00',
			'end_date'   => '2024-01-15 12:00:00',
			'status'     => 'publish',
			'organizer'  => [ $organizer->ID ],
		] )->create();

		// Set up the global post context as if we're viewing this event.
		$GLOBALS['post'] = get_post( $event->ID );
		setup_postdata( $GLOBALS['post'] );

		// Render the organizer meta template.
		ob_start();
		tribe_get_template_part( 'modules/meta/organizer' );
		$html = ob_get_clean();

		// Clean up.
		wp_reset_postdata();

		$this->assertMatchesSnapshot( $html );
	}
}
