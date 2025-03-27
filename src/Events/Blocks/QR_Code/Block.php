<?php
/**
 * QR Code Block
 *
 * @since TBD
 *
 * @package TEC\Events\Blocks\QR_Code
 */

namespace TEC\Events\Blocks\QR_Code;

/**
 * Class Block
 *
 * @since TBD
 *
 * @package TEC\Events\Blocks\QR_Code
 */
class Block extends \Tribe__Editor__Blocks__Abstract {
	/**
	 * @since TBD
	 *
	 * @var string The namespace of this template.
	 */
	protected $namespace = 'tec';

	/**
	 * Returns the name/slug of this block.
	 *
	 * @since TBD
	 *
	 * @return string The name/slug of this block.
	 */
	public function slug(): string {
		return 'event-qr-code';
	}

	/**
	 * Set the default attributes of this block.
	 *
	 * @since TBD
	 *
	 * @return array<string,mixed> The array of default attributes.
	 */
	public function default_attributes(): array {
		return [
			'qr_code_size'      => '4', // Default to small size (125x125).
			'redirection'       => 'current',
			'specific_event_id' => 0,
			'title'             => _x( 'QR Code', 'The default title of the QR Code Block.', 'the-events-calendar' ),
			'align'             => 'center',
		];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since TBD
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
