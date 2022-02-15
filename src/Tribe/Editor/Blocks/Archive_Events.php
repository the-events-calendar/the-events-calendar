<?php
namespace Tribe\Events\Editor\Blocks;

class Archive_Events extends \Tribe__Editor__Blocks__Abstract {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return 'archive-events';
	}

	/**
	 * Set the default attributes of this block
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function default_attributes() {

		return [];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since TBD
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
	 * Register the Assets for when this block is active
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function assets() {
	}
}
