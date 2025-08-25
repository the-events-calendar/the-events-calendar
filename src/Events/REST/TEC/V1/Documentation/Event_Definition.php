<?php
/**
 * Event definition provider for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Abstracts\Definition;
use TEC\Common\REST\TEC\V1\Collections\PropertiesCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Array_Of_Type;
use TEC\Common\REST\TEC\V1\Parameter_Types\Boolean;
use TEC\Common\REST\TEC\V1\Parameter_Types\Date;
use TEC\Common\REST\TEC\V1\Parameter_Types\Date_Time;
use TEC\Common\REST\TEC\V1\Parameter_Types\Positive_Integer;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;
use TEC\Common\REST\TEC\V1\Parameter_Types\Definition_Parameter;

/**
 * Event definition provider for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Event_Definition extends Definition {
	/**
	 * Returns the type of the definition.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Event';
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
	 * Returns an array in the format used by Swagger.
	 *
	 * @since 6.15.0
	 *
	 * @return array An array description of a Swagger supported component.
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

		$properties[] = new Definition_Parameter( new \TEC\Common\REST\TEC\V1\Documentation\Date_Details_Definition(), 'dates' );

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
				'multiday',
				fn() => __( 'Whether the event is multiday', 'the-events-calendar' ),
			)
		)->set_example( false );

		$properties[] = (
			new Boolean(
				'is_past',
				fn() => __( 'Whether the event is in the past', 'the-events-calendar' ),
			)
		)->set_example( false );

		$properties[] = (
			new Boolean(
				'is_now',
				fn() => __( 'Whether the event is happening now', 'the-events-calendar' ),
			)
		)->set_example( false );

		$properties[] = (
			new Boolean(
				'all_day',
				fn() => __( 'Whether the event is all day', 'the-events-calendar' ),
			)
		)->set_example( false );

		$properties[] = (
			new Boolean(
				'starts_this_week',
				fn() => __( 'Whether the event starts this week', 'the-events-calendar' ),
			)
		)->set_example( false )->set_nullable( true );

		$properties[] = (
			new Boolean(
				'ends_this_week',
				fn() => __( 'Whether the event ends this week', 'the-events-calendar' ),
			)
		)->set_example( false )->set_nullable( true );

		$properties[] = (
			new Boolean(
				'happens_this_week',
				fn() => __( 'Whether the event happens this week', 'the-events-calendar' ),
			)
		)->set_example( false )->set_nullable( true );

		$properties[] = (
			new Positive_Integer(
				'this_week_duration',
				fn() => __( 'The duration of the event in the current week', 'the-events-calendar' ),
			)
		)->set_example( 3600 )->set_nullable( true );

		$properties[] = (
			new Array_Of_Type(
				'displays_on',
				fn() => __( 'The days of the week that the event displays on', 'the-events-calendar' ),
				Date::class,
			)
		)->set_pattern( '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' )->set_example( [ '2025-06-05' ] );

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
				'organizer_names',
				fn() => __( 'The names of the organizers of the event', 'the-events-calendar' ),
				Text::class,
			)
		)->set_example( [ 'John Doe', 'Jane Doe' ] );

		$properties[] = (
			new Array_Of_Type(
				'organizers',
				fn() => __( 'The organizers of the event', 'the-events-calendar' ),
				Organizer_Definition::class,
			)
		);

		$properties[] = (
			new Array_Of_Type(
				'venues',
				fn() => __( 'The venues of the event', 'the-events-calendar' ),
				Venue_Definition::class,
			)
		);

		$properties[] = (
			new Array_Of_Type(
				'ticketed',
				fn() => __( 'Array of ticket providers or false if the event is not ticketed.', 'the-events-calendar' ),
				Text::class
			)
		)->set_example( [ 'tc' ] );

		$properties[] = (
			new Text(
				'schedule_details',
				fn() => __( 'The schedule details of the event', 'the-events-calendar' ),
			)
		)->set_example( '10:00 - 12:00' );

		$properties[] = (
			new Text(
				'short_schedule_details',
				fn() => __( 'The schedule details of the event in HTML', 'the-events-calendar' ),
			)
		)->set_example( '<p>10:00 - 12:00</p>' );

		$documentation = [
			'allOf' => [
				[
					'$ref' => '#/components/schemas/TEC_Post_Entity',
				],
				[
					'type'        => 'object',
					'description' => __( 'An event', 'the-events-calendar' ),
					'title'       => 'Event',
					'properties'  => $properties,
				],
			],
		];

		$type = strtolower( $this->get_type() );

		/**
		 * Filters the Swagger documentation generated for an event in the TEC REST API.
		 *
		 * @since 6.15.0
		 *
		 * @param array            $documentation An associative PHP array in the format supported by Swagger.
		 * @param Event_Definition $this          The Event_Definition instance.
		 *
		 * @return array
		 */
		$documentation = (array) apply_filters( "tec_rest_swagger_{$type}_definition", $documentation, $this );

		/**
		 * Filters the Swagger documentation generated for a definition in the TEC REST API.
		 *
		 * @since 6.15.0
		 *
		 * @param array            $documentation An associative PHP array in the format supported by Swagger.
		 * @param Event_Definition $this          The Event_Definition instance.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tec_rest_swagger_definition', $documentation, $this );
	}
}
