<?php
/**
 * View: Elementor Event Description widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-description.php
 *
 * @since TBD
 *
 * @var int               $event_id The event ID.
 * @var string            $content  The event description content.
 * @var array             $settings The widget settings.
 * @var Event_Description $widget   The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Description;

// No content, no render.
if ( empty( $content ) ) {
	return;
}

?>
<div
	<?php tribe_classes( $widget->get_element_classes() ); ?>
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, StellarWP.XSS.EscapeOutput.OutputNotEscaped -- can't escape the HTML attribute string
	echo $widget->get_render_attribute_string( 'content' );
	?>
>
	<?php echo wp_kses_post( $content ); ?>
</div>
