<?php
/**
 * View: Elementor Event Navigation widget.
 *
 * Contains links to next and previous (chronologically by event date) events, if they exist.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-navigation.php
 *
 * @since 6.4.0
 *
 * @var string           $label      The label for the event navigation.
 * @var ?WP_Post         $prev_event The previous event.
 * @var string           $prev_link  The HTML previous link.
 * @var ?WP_Post         $next_event The next event.
 * @var string           $next_link  The HTML next link.
 * @var int              $event_id   The event ID.
 * @var Event_Navigation $widget     The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Navigation;

// Bail if both links empty.
if ( empty( $prev_link ) && empty( $next_link ) ) {
	return;
}

// Bail if both events empty.
if ( empty( $prev_event ) && empty( $next_event ) ) {
	return;
}

?>
<nav <?php tec_classes( $widget->get_widget_class() ); ?> aria-label="<?php echo esc_attr( tribe_get_event_label_plural() ); ?>">
	<ul <?php tec_classes( $widget->get_list_class() ); ?>>
		<?php $this->template( 'integrations/elementor/widgets/event-navigation/previous' ); ?>

		<?php $this->template( 'integrations/elementor/widgets/event-navigation/next' ); ?>
	</ul>
</nav>
