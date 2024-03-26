<?php
/**
 * View: Elementor Event Categories widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-categories/header.php
 *
 * @since TBD
 *
 * @var bool   $show         Whether to show the heading.
 * @var string $heading_tag  The HTML tag to use for the heading.
 * @var array  $settings     The widget settings.
 * @var int    $event_id     The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Categories $widget The widget instance.
 */

if ( ! $show ) {
	return;
}
?>

<<?php echo tag_escape( $heading_tag ); ?> <?php tribe_classes( $widget->get_label_class() ); ?>><?php echo esc_html( $widget->get_label_text() ); ?></<?php echo tag_escape( $heading_tag ); ?>>
