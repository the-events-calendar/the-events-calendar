<?php
class Tribe__Events__Editor__Blocks__Event_Links
extends Tribe__Editor__Blocks__Abstract {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 4.7
	 *
	 * @return string
	 */
	public function slug() {
		return 'event-links';
	}

	/**
	 * Set the default attributes of this block
	 *
	 * @since 4.7
	 *
	 * @return array
	 */
	public function default_attributes() {
		return [
			'googleCalendarLabel' => esc_html__( 'Google Calendar', 'the-events-calendar' ),
			'iCalLabel'           => esc_html__( 'iCalendar', 'the-events-calendar' ),
			'outlook365Label'     => esc_html__( 'Outlook 365', 'the-events-calendar' ),
			'outlookLiveLabel'    => esc_html__( 'Outlook Live', 'the-events-calendar' ),
			'hasiCal'             => true,
			'hasGoogleCalendar'   => true,
			'hasOutlook365'       => true,
			'hasOutlookLive'      => true,
		];
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
		$has_filter = function_exists( 'strip_dynamic_blocks' ) && has_filter( 'the_content', 'strip_dynamic_blocks' );
		if ( $has_filter ) {
			add_filter( 'the_content', 'strip_dynamic_blocks', 1 );
		}

		$args['attributes'] = $this->attributes( $attributes );

		// Add the rendering attributes into global context
		tribe( 'events.editor.template' )->add_template_globals( $args );

		$html = tribe( 'events.editor.template' )->template( [ 'blocks', $this->slug() ], $args, false );

		if ( $has_filter ) {
			remove_filter( 'the_content', 'strip_dynamic_blocks', 1 );
		}

		// Retrieve custom CSS classes.
		$classes = isset( $args['attributes']['className'] ) ? $args['attributes']['className'] : '';

		// Conditionally wrap the output in a <div> element when a user includes custom CSS classes in the block settings.
		if ( $classes ) {
			$html = '<div class="' . esc_attr( $classes ) . '">' . $html . '</div>';
		}

		return $html;
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
