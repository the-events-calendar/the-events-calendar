<?php


class Tribe__Events__REST__V1__Endpoints__Single_Event_Slug
	extends Tribe__Events__REST__V1__Endpoints__Single_Event {
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
				'description'       => __( 'the event post name', 'the-events-calendar' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_event_slug' ],
			],
		];
	}

	/**
	 * Returns the post type handled by the endpoint.
	 *
	 * @since 6.17.5
	 *
	 * @return string
	 */
	protected function get_post_type() {
		return Tribe__Events__Main::POSTTYPE;
	}
}
