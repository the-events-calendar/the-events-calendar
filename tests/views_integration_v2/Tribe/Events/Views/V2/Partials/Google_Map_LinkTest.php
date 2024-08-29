<?php

namespace Tribe\Events\Views\V2\Partials;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class Google_Map_LinkTest extends HtmlTestCase {
	use With_Post_Remapping;

	public function test_render() {
		$venue = $this->factory()->venue->create_object( [ 'location' => 'new_york' ] );
		$event = tribe_get_event(
			tribe_events()->set_args(
				[
					'start_date' => '2018-07-01 10am',
					'timezone'   => 'Europe/Paris',
					'duration'   => 3 * HOUR_IN_SECONDS,
					'title'      => 'Test Event - 2018-07-01 11am',
					'status'     => 'publish',
					'venue'      => $venue,
				]
			)->create(),
			OBJECT,
			'2019-07-01'
		);

		$html = tribe_get_map_link_html( $event->ID );

		$this->assertMatchesSnapshot( $html );
	}
}
