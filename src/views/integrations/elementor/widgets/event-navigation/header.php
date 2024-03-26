<?php
/**
 * View: Elementor Event Navigation widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-navigation/header.php
 *
 * @since TBD
 *
 * @var string $header_tag The HTML tag for the event title.
 * @var string $label      The label for the event navigation.
 * @var int    $event_id   The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Navigation $widget The widget instance.
 */

?>
<<?php echo tag_escape( $header_tag ); ?> <?php tribe_classes( $widget->get_header_classes() ); ?>>
	<?php echo esc_html( $label ); ?>
</<?php echo tag_escape( $header_tag ); ?>>
