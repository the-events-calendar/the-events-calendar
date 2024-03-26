<?php
/**
 * View: Elementor Event Organizer widget website section header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/details/website/header.php
 *
 * @since TBD
 *
 * @var bool   $show        Whether to show the organizer website heading.
 * @var string $header_tag  The HTML tag to use for the heading.
 * @var string $header_text The header text.
 * @var array  $organizer   The organizer ID.
 * @var array  $settings    The widget settings.
 * @var int    $event_id    The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

if ( ! $show ) {
	return;
}
?>

<<?php echo tag_escape( $header_tag ); ?> <?php tribe_classes( $widget->get_website_label_class() ); ?>>
	<?php echo wp_kses_post( $header_text ); ?>
</<?php echo tag_escape( $header_tag ); ?>>
