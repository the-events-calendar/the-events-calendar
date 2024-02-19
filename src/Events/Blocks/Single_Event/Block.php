<?php

namespace TEC\Events\Blocks\Single_Event;

/**
 * Class Block
 *
 * @since 6.3.3
 *
 * @package TEC\Events\Blocks\Single_Event
 */
class Block extends \Tribe__Editor__Blocks__Abstract {
	/**
	 * @since 6.3.3
	 *
	 * @var string The namespace of this template.
	 */
	protected $namespace = 'tec';

	/**
	 * Returns the name/slug of this block.
	 *
	 * @since 6.3.3
	 *
	 * @return string The name/slug of this block.
	 */
	public function slug(): string {
		return 'single-event';
	}

	/**
	 * Set the default attributes of this block.
	 *
	 * @since 6.3.3
	 *
	 * @return array<string,mixed> The array of default attributes.
	 */
	public function default_attributes(): array {
		return [];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since 6.3.3
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string The block HTML.
	 */
	public function render( $attributes = [] ): string {
		$args['attributes'] = $this->attributes( $attributes );

		// Add the rendering attributes into global context.
		tribe( 'events.editor.template' )->add_template_globals( $args );

		return tribe( 'events.editor.template' )->template( [ 'blocks', $this->slug() ], $args, false );
	}
}
