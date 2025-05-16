<?php
class Tribe__Events__Editor__Blocks__Event_Links
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
	 *
	 * @param  array $attributes
	 *
	 * @return string
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

		return $html;
	}
}
