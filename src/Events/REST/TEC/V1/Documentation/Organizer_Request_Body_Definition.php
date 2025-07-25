<?php
/**
 * Organizer request body definition provider for the TEC REST API.
 *
 * @since TBD
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
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Organizer_Request_Body_Definition extends Definition {
	/**
	 * Returns the type of the definition.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Organizer_Request_Body';
	}

	/**
	 * Returns the priority of the definition.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 1;
	}

	/**
	 * Returns the documentation for the definition.
	 *
	 * @since TBD
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

		return [
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
		];
	}
}
