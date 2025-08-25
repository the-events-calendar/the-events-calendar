<?php
/**
 * Organizer request body definition provider for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Abstracts\Definition;
use TEC\Common\REST\TEC\V1\Collections\PropertiesCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Email;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;
use TEC\Common\REST\TEC\V1\Parameter_Types\URI;

/**
 * Organizer request body definition provider for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Organizer_Request_Body_Definition extends Definition {
	/**
	 * Returns the type of the definition.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Organizer_Request_Body';
	}

	/**
	 * Returns the priority of the definition.
	 *
	 * @since 6.15.0
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 1;
	}

	/**
	 * Returns the documentation for the definition.
	 *
	 * @since 6.15.0
	 *
	 * @return array
	 */
	public function get_documentation(): array {
		$properties = new PropertiesCollection();

		$properties[] = (
			new Text(
				'phone',
				fn() => __( 'The organizer\'s phone number', 'the-events-calendar' ),
			)
		)->set_format( 'tel' )->set_example( '123-456-7890' );

		$properties[] = (
			new URI(
				'website',
				fn() => __( 'The organizer\'s website', 'the-events-calendar' ),
			)
		);

		$properties[] = (
			new Email(
				'email',
				fn() => __( 'The organizer\'s email address', 'the-events-calendar' ),
			)
		);

		$type = strtolower( $this->get_type() );

		/**
		 * Filters the Swagger documentation generated for an organizer request body in the TEC REST API.
		 *
		 * @since 6.15.0
		 *
		 * @param array                             $documentation An associative PHP array in the format supported by Swagger.
		 * @param Organizer_Request_Body_Definition $this          The Organizer_Request_Body_Definition instance.
		 *
		 * @return array
		 */
		$documentation = (array) apply_filters(
			"tec_rest_swagger_{$type}_definition",
			[
				'allOf' => [
					[
						'$ref' => '#/components/schemas/TEC_Post_Entity_Request_Body',
					],
					[
						'type'        => 'object',
						'title'       => 'Organizer Request Body',
						'description' => __( 'The request body for the organizer endpoint', 'the-events-calendar' ),
						'properties'  => $properties,
					],
				],
			],
			$this
		);

		/**
		 * Filters the Swagger documentation generated for a definition in the TEC REST API.
		 *
		 * @since 6.15.0
		 *
		 * @param array                             $documentation An associative PHP array in the format supported by Swagger.
		 * @param Organizer_Request_Body_Definition $this          The Organizer_Request_Body_Definition instance.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tec_rest_swagger_definition', $documentation, $this );
	}
}
