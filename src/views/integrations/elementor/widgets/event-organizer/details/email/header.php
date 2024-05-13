<?php
/**
 * View: Elementor Event Organizer widget website section header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/details/email/header.php
 *
 * @since 6.4.0
 *
 * @var bool   $show        Whether to show the organizer email header.
 * @var string $header_tag  The HTML tag to use for the header.
 * @var string $header_text The header text.
 * @var array  $settings    The widget settings.
 * @var int    $event_id    The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

if ( ! $show_organizer_email_header ) {
	return;
}
?>
<<?php echo tag_escape( $organizer_email_header_tag ); ?> <?php tribe_classes( $widget->get_email_header_class() ); ?>>
	<?php echo wp_kses_post( $organizer_email_header_text ); ?>
<?php echo '</' . tag_escape( $organizer_email_header_tag ) . '>'; ?>
