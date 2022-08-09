<?php
namespace Tribe\Events\Editor\Blocks;

class Archive_Events extends \Tribe__Editor__Blocks__Abstract {

	/**
	 * Returns the name/slug of this block.
	 *
	 * @since 5.14.2
	 *
	 * @return string The name/slug of this block.
	 */
	public function slug() {
		return 'archive-events';
	}

	/**
	 * Set the default attributes of this block.
	 *
	 * @since 5.14.2
	 *
	 * @return array<string,mixed> The array of default attributes.
	 */
	public function default_attributes() {
		return [];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since 5.14.2
	 *
	 * @param  array $attributes The block attributes.
	 *
	 * @return string The block HTML.
	 */
	public function render( $attributes = [] ) {
		$args['attributes'] = $this->attributes( $attributes );

		// Add the rendering attributes into global context.
		tribe( 'events.editor.template' )->add_template_globals( $args );

		return tribe( 'events.editor.template' )->template( [ 'blocks', $this->slug() ], $args, false );
	}
}
