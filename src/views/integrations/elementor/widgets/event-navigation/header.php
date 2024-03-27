<?php
/**
 * View: Elementor Event Navigation widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-navigation/header.php
 *
 * @since TBD
 *
 * @var bool   $show_nav_header Whether to visually show/hide the event navigation header.
 * @var string $header_tag      The HTML tag for the event title.
 * @var string $header_text     The header_text for the event navigation.
 * @var int    $event_id        The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Navigation $widget The widget instance.
 */

if ( ! $show_nav_header ) {
	return;
}

?>
<<?php echo tag_escape( $header_tag ); ?> <?php tribe_classes( $widget->get_header_class(), $class ); ?>>
	<?php echo esc_html( $header_text ); ?>
</<?php echo tag_escape( $header_tag ); ?>>
