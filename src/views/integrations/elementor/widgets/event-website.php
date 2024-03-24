<?php
/**
 * View: Elementor Event Website widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/event-website.php
 *
 * @since TBD
 *
 * @var string        $align        The text alignment.
 * @var string        $show_heading Whether to show the heading.
 * @var string        $header_tag   The HTML tag for the heading.
 * @var int           $event_id     The event ID.
 * @var string        $label_class  The class for the link label.
 * @var string        $link_class   The class for the link.
 * @var string        $website      The event website link.
 * @var Event_Website $widget       The widget instance.
 */

use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Event_Website;

if ( empty( $website ) ) {
	return;
}

?>
<div <?php tribe_classes( $widget->get_element_classes() ); ?>>
	<?php if ( $show_heading === 'yes' ) : ?>
		<<?php echo tag_escape( $header_tag ); ?>
			<?php tribe_classes( $label_class ); ?>
		>
		<?php
			printf(
				/* translators: %s: Event (singular) */
				esc_html__( '%s Website:', 'tribe-events-calendar-pro' ),
				esc_html( tribe_get_event_label_singular() )
			);
		?>
		</<?php echo tag_escape( $header_tag ); ?>>
	<?php endif; ?>
	<div <?php tribe_classes( $link_class ); ?>>
		<?php echo wp_kses_post( $website ); ?>
	</div>
</div>
