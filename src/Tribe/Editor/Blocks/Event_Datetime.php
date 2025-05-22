<?php
class Tribe__Events__Editor__Blocks__Event_Datetime
extends Tribe__Editor__Blocks__Abstract {
	use TEC\Events\Traits\Block_Trait;

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 4.7
	 *
	 * @return string
	 */
	public function slug() {
		return 'event-datetime';
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since 4.7
	 *
	 * @param  array $attributes
	 *
	 * @return string
	 */
	public function render( $attributes = [] ) {
		$args['attributes'] = $this->attributes( $attributes );

		// Add the rendering attributes into global context
		tribe( 'events.editor.template' )->add_template_globals( $args );

		return tribe( 'events.editor.template' )->template( [ 'blocks', $this->slug() ], $args, false );
	}

	/**
	 * Returns the block data for the block editor.
	 *
	 * @since 5.1.1
	 *
	 * @return array<string,mixed> The block data for the editor.
	 */
	public function block_data() {
		$block_data = [
			'id'         => $this->slug(),
			'attributes' => [
				'start'         => [
					'type'   => 'string',
					'source' => 'meta',
					'meta'   => '_EventStartDate',
				],
				'end'           => [
					'type'   => 'string',
					'source' => 'meta',
					'meta'   => '_EventEndDate',
				],
				'allDay'        => [
					'type'   => 'boolean',
					'source' => 'meta',
					'meta'   => '_EventAllDay',
				],
				'timeZone'      => [
					'type'   => 'string',
					'source' => 'meta',
					'meta'   => '_EventTimezone',
				],
				'separatorDate' => [
					'type'   => 'string',
					'source' => 'meta',
					'meta'   => '_EventDateTimeSeparator',
				],
				'separatorTime' => [
					'type'   => 'string',
					'source' => 'meta',
					'meta'   => '_EventTimeRangeSeparator',
				],
				'showTimeZone'  => [
					'type'    => 'boolean',
					'default' => tribe_get_option( 'tribe_events_timezones_show_zone', false ),
				],
				'timeZoneLabel' => [
					'type'    => 'string',
					'default' => class_exists( 'Tribe__Timezones' ) ? Tribe__Timezones::wp_timezone_string() : get_option( 'timezone_string', 'UTC' ),
				],
				// Only available for classic users.
				'cost'          => [
					'type'   => 'string',
					'source' => 'meta',
					'meta'   => '_EventCost',
				],
			],
		];

		/**
		 * Filters the block data.
		 *
		 * @param array<string,mixed> $block_data The block data.
		 * @param object              $this       The current object.
		 *
		 * @return array<string,mixed> The block data.
		 */
		$block_data = apply_filters( 'tribe_block_block_data', $block_data, $this );

		/**
		 * Filters the block data for the block.
		 *
		 * @param array<string,mixed> $block_data The block data.
		 * @param object              $this       The current object.
		 *
		 * @return array<string,mixed> The block data.
		 */
		$block_data = apply_filters( 'tribe_block_block_data_' . $this->slug(), $block_data, $this );

		return $block_data;
	}
}
