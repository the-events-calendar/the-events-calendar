<?php
/**
 * View: Elementor Event Organizer widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/details/email/content.php
 *
 * @since TBD
 *
 * @var string $organizer The organizer ID.
 * @var array  $settings  The widget settings.
 * @var int    $event_id  The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

?>
<p <?php tribe_classes( $widget->get_email_base_class() ); ?>><?php echo esc_html( tribe_get_organizer_email( $organizer, false ) ); ?></p>
