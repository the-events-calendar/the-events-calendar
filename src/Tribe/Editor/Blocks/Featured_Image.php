<?php
class Tribe__Events__Editor__Blocks__Featured_Image
extends Tribe__Editor__Blocks__Abstract {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 4.7
	 *
	 * @return string
	 */
	public function slug() {
		return 'featured-image';
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since 4.7
	 * @since TBD - Added $classes variable to allow for the addition of custom CSS classes to the block output.
	 *
	 * @param  array $attributes
	 * 
	 * @var    string $classes The custom CSS classes to be added to the block output.
	 *
	 * @return string The rendered block output.
	 */
	public function render( $attributes = [] ) {
		$args['attributes'] = $this->attributes( $attributes );

		// Retrieve custom CSS classes.
		$classes = isset( $args['attributes']['className'] ) ? $args['attributes']['className'] : '';

		// Add the rendering attributes into global context
		tribe( 'events.editor.template' )->add_template_globals( $args );

		$output = tribe( 'events.editor.template' )->template( [ 'blocks', $this->slug() ], $args, false );

		// Conditionally wrap the output in a <div> element when a user includes custom CSS classes in the block settings.
		if ( $classes ) {
			$output = '<div class="' . esc_attr( $classes ) . '">' . $output . '</div>';
		}

		return $output;
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
