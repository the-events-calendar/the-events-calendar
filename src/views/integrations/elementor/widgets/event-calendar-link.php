<?php
/**
 * View: Elementor Event Calendar Link widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-calendar_link.php
 *
 * @since 6.4.0
 *
 * @var string $calendar_link_tag   HTML tag for the backlink.
 * @var string $calendar_link_label Label for the link.
 * @var string $calendar_link_class CSS classes.
 * @var string $calendar_link       URL to the events page.
 * @var Event_Calendar_Link $widget The widget instance.
 */
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Calendar_Link;
?>
<<?php echo tag_escape( $calendar_link_tag ); ?> <?php tribe_classes( $calendar_link_class ); ?>>
	<a href="<?php echo esc_url( $calendar_link ); ?>" >&laquo; <?php echo wp_kses_post( $calendar_link_label ); ?></a>
</<?php echo tag_escape( $calendar_link_tag ) . '>'; ?>
