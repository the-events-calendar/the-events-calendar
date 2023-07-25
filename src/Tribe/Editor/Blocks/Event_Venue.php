<?php
class Tribe__Events__Editor__Blocks__Event_Venue
extends Tribe__Editor__Blocks__Abstract {
	/**
	 * The ID of the venue to display.
	 *
	 * @since TBD
	 *
	 * @var ?int
	 */
	protected ?int $venue_id = null;

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 4.7
	 *
	 * @return string
	 */
	public function slug() {
		return 'event-venue';
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

		$args['show_map_link'] = $this->should_show_map_link( $args['attributes'] );
		$args['show_map']      = $this->should_show_map( $args['attributes'] );

		if ( isset( $args['attributes']['venue'] ) ) {
			$args['venue_id'] = $args['attributes']['venue'];
		}

		// Add the rendering attributes into global context
		tribe( 'events.editor.template' )->add_template_globals( $args );

		return tribe( 'events.editor.template' )->template( [ 'blocks', $this->slug() ], $args, false );
	}

	/**
	 * Register the Assets for when this block is active
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function assets() {
		tribe_asset(
			tribe( 'tec.main' ),
			'tribe-events-block-' . $this->slug(),
			'app/' . $this->slug() . '/frontend.css',
			[],
			'wp_enqueue_scripts',
			[
				'conditionals' => [ $this, 'has_block' ],
			]
		);
	}

	/**
	 * Gets the venue ID from the block.
	 *
	 * @since TBD
	 *
	 * @param array $attributes Array of attributes for the block.
	 *
	 * @return ?int
	 */
	public function get_venue_id( array $attributes ): ?int {
		$venue_id = $this->venue_id;

		if ( $venue_id === null && isset( $attributes['venue'] ) ) {
			$venue_id = $attributes['venue'];
		}

		/**
		 * Filters the venue ID for the event venue block.
		 *
		 * @since TBD
		 *
		 * @param ?int  $venue_id  The venue ID.
		 * @param array $attributes Array of attributes for the block.
		 * @param Tribe__Events__Editor__Blocks__Event_Venue $block The block instance.
		 */
		return apply_filters( 'tec_events_blocks_event_venue_venue_id', $venue_id, $attributes, $this );
	}

	/**
	 * Should we show the map?
	 *
	 * @since TBD
	 *
	 * @param array $attributes Array of attributes for the block.
	 *
	 * @return bool
	 */
	public function should_show_map( array $attributes ): bool {
		if ( isset( $attributes['showMap'] ) ) {
			return (bool) $attributes['showMap'];
		} elseif ( ! tribe_embed_google_map() ) {
			return false;
		}

		/**
		 * Filters whether the map should be shown for the event venue block.
		 *
		 * @since TBD
		 *
		 * @param bool  $show_map   Whether the map should be shown.
		 * @param array $attributes Array of attributes for the block.
		 * @param Tribe__Events__Editor__Blocks__Event_Venue $block The block instance.
		 */
		return apply_filters( 'tec_events_blocks_event_venue_should_show_map', true, $attributes, $this );
	}

	/**
	 * Should we show the map link?
	 *
	 * @since TBD
	 *
	 * @param array $attributes Array of attributes for the block.
	 *
	 * @return bool
	 */
	public function should_show_map_link( array $attributes ): bool {
		if ( isset( $attributes['showMapLink'] ) ) {
			return (bool) $attributes['showMapLink'];
		} elseif ( ! tribe_embed_google_map() ) {
			return false;
		}

		/**
		 * Filters whether the map link should be shown for the event venue block.
		 *
		 * @since TBD
		 *
		 * @param bool  $show_map_link Whether the map link should be shown.
		 * @param array $attributes    Array of attributes for the block.
		 * @param Tribe__Events__Editor__Blocks__Event_Venue $block The block instance.
		 */
		return apply_filters( 'tec_events_blocks_event_venue_should_show_map_link', true, $attributes, $this );
	}
}
