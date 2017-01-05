<?php

namespace Tribe\Events\Tests\Factories;

use Tribe__Events__Main as Main;

class Organizer extends \WP_UnitTest_Factory_For_Post {

	function create_object( $args ) {
		$args['post_type'] = Main::ORGANIZER_POST_TYPE;

		$title = 'Organizer' . uniqid();
		$lc_title = strtolower( $title );

		$defaults = [
			'meta_input' => [
				'_OrganizerPhone'   => $lc_title . ' phone',
				'_OrganizerWebsite' => $lc_title . ' website',
				'_OrganizerEmail'   => $lc_title . ' .email',
			],
		];

		return parent::create_object( array_merge( $defaults, $args ) );
	}
}