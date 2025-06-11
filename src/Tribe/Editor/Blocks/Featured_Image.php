<?php
class Tribe__Events__Editor__Blocks__Featured_Image
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
		return 'featured-image';
	}

	/**
	 * Whether the block should register assets
	 *
	 * @since 6.13.0
	 *
	 * @return bool
	 */
	public function should_register_assets(): bool {
		return false;
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
}
