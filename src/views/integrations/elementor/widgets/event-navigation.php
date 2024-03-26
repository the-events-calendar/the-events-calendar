<?php
/**
 * View: Elementor Event Navigation widget.
 *
 * Contains links to next and previous (chronologically by event date) events, if they exist.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-navigation.php
 *
 * @since TBD
 *
 * @var string           $header_tag The HTML tag for the event title.
 * @var string           $label      The label for the event navigation.
 * @var ?WP_Post         $prev_event The previous event.
 * @var string           $prev_link  The HTML previous link.
 * @var ?WP_Post         $next_event The next event.
 * @var string           $next_link  The HTML next link.
 * @var int              $event_id   The event ID.
 * @var Event_Navigation $widget     The widget instance.
 */

use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Event_Navigation;

// Bail if both links empty.
if ( empty( $prev_link ) && empty( $next_link ) ) {
	return;
}
// Bail if both events empty.

if ( empty( $prev_event ) && empty( $next_event ) ) {
	return;
}

?>
<nav <?php tribe_classes( $widget->get_widget_class() ); ?>>
	<?php if ( ! empty( $label ) ) : ?>
		<?php
		$this->template(
			'integrations/elementor/widgets/event-navigation/header',
			[
				'label'      => $label,
				'header_tag' => $header_tag,
				'widget'     => $widget,
			]
		);
		?>
	<?php endif; ?>
	<ul <?php tribe_classes( $widget->get_list_class() ); ?>>
		<?php
		$this->template(
			'integrations/elementor/widgets/event-navigation/previous',
			[
				'prev_link'  => $prev_link,
				'prev_event' => $prev_event,
				'event_id'   => $event_id,
				'widget'     => $widget,
			]
		);
		?>

		<?php
		$this->template(
			'integrations/elementor/widgets/event-navigation/next',
			[
				'next_link'  => $next_link,
				'next_event' => $next_event,
				'event_id'   => $event_id,
				'widget'     => $widget,
			]
		);
		?>
	</ul>
</nav>
