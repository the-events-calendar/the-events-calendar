<?php


class Tribe__Events__REST__V1__Endpoints__Single_Venue_Slug
	extends Tribe__Events__REST__V1__Endpoints__Single_Venue {
	use Tribe__Events__REST__V1__Endpoints__Slug_Endpoint;

	/**
	 * Provides the content of the `args` array to register the endpoint support for GET requests.
	 *
	 * @return array
	 */
	public function READ_args() {
		return [
			'slug' => [
				'in'                => 'path',
				'type'              => 'string',
				'description'       => __( 'the venue post name', 'the-events-calendar' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_venue_slug' ],
			],
		];
	}
}
