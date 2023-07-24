<?php
class Tribe__Events__Editor__Blocks__Event_Venue
extends Tribe__Editor__Blocks__Abstract {

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

		$args['show_map_link'] = true;
		if ( isset( $args['attributes']['showMapLink'] ) ) {
			$args['show_map_link'] = (bool) $args['attributes']['showMapLink'];
		} elseif ( ! tribe_show_google_map_link() ) {
			$args['show_map_link'] = false;
		}

		$args['show_map'] = true;
		if ( isset( $args['attributes']['showMap'] ) ) {
			$args['show_map'] = (bool) $args['attributes']['showMap'];
		} elseif ( ! tribe_embed_google_map() ) {
			$args['show_map'] = false;
		}

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
}
