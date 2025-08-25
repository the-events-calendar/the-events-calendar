<?php
/**
 * Event request body definition provider for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Abstracts\Definition;
use TEC\Common\REST\TEC\V1\Collections\PropertiesCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Array_Of_Type;
use TEC\Common\REST\TEC\V1\Parameter_Types\Boolean;
use TEC\Common\REST\TEC\V1\Parameter_Types\Date_Time;
use TEC\Common\REST\TEC\V1\Parameter_Types\Positive_Integer;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;

/**
 * Event request body definition provider for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Event_Request_Body_Definition extends Definition {
	/**
	 * Returns the type of the definition.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Event_Request_Body';
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
			new Array_Of_Type(
				'tribe_events_cat',
				fn() => __( 'The terms assigned to the entity in the tribe_events_cat taxonomy', 'the-events-calendar' ),
				Positive_Integer::class,
			)
		)->set_example( [ 1, 5, 12 ] );

		$properties[] = (
			new Date_Time(
				'start_date',
				fn() => __( 'The start date of the event', 'the-events-calendar' ),
			)
		)->set_example( '2025-06-05 12:00:00' )->set_pattern( '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$' );

		$properties[] = (
			new Date_Time(
				'start_date_utc',
				fn() => __( 'The start date of the event in UTC', 'the-events-calendar' ),
			)
		)->set_example( '2025-06-05 09:00:00' )->set_pattern( '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$' );

		$properties[] = (
			new Date_Time(
				'end_date',
				fn() => __( 'The end date of the event', 'the-events-calendar' ),
			)
		)->set_example( '2025-06-05 16:00:00' )->set_pattern( '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$' );

		$properties[] = (
			new Date_Time(
				'end_date_utc',
				fn() => __( 'The end date of the event in UTC', 'the-events-calendar' ),
			)
		)->set_example( '2025-06-05 13:00:00' )->set_pattern( '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$' );

		$properties[] = (
			new Text(
				'timezone',
				fn() => __( 'The timezone of the event', 'the-events-calendar' ),
			)
		)->set_example( 'Europe/Athens' );

		$properties[] = (
			new Positive_Integer(
				'duration',
				fn() => __( 'The duration of the event in seconds', 'the-events-calendar' ),
			)
		)->set_example( 14400 );

		$properties[] = (
			new Boolean(
				'all_day',
				fn() => __( 'Whether the event is all day', 'the-events-calendar' ),
			)
		)->set_example( false );

		$properties[] = (
			new Boolean(
				'featured',
				fn() => __( 'Whether the event is featured', 'the-events-calendar' ),
			)
		)->set_example( false );

		$properties[] = (
			new Boolean(
				'sticky',
				fn() => __( 'Whether the event is sticky', 'the-events-calendar' ),
			)
		)->set_example( false );

		$properties[] = (
			new Text(
				'cost',
				fn() => __( 'The cost of the event', 'the-events-calendar' ),
			)
		)->set_example( '$10' );

		$properties[] = (
			new Array_Of_Type(
				'organizers',
				fn() => __( 'The organizers of the event', 'the-events-calendar' ),
				Positive_Integer::class,
			)
		)->set_example( [ 1, 2 ] );

		$properties[] = (
			new Array_Of_Type(
				'venues',
				fn() => __( 'The venues of the event', 'the-events-calendar' ),
				Positive_Integer::class,
			)
		)->set_example( [ 7 ] );

		$type = strtolower( $this->get_type() );

		/**
		 * Filters the Swagger documentation generated for an event request body in the TEC REST API.
		 *
		 * @since 6.15.0
		 *
		 * @param array                         $documentation An associative PHP array in the format supported by Swagger.
		 * @param Event_Request_Body_Definition $this          The Event_Request_Body_Definition instance.
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
						'title'       => 'Event Request Body',
						'description' => __( 'The request body for the event endpoint', 'the-events-calendar' ),
						'type'        => 'object',
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
		 * @param array                         $documentation An associative PHP array in the format supported by Swagger.
		 * @param Event_Request_Body_Definition $this          The Event_Request_Body_Definition instance.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tec_rest_swagger_definition', $documentation, $this );
	}
}
