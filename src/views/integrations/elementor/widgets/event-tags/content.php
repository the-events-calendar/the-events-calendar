<?php
/**
 * View: Elementor Event Tags widget content.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/event-tags/content.php
 *
 * @since TBD
 *
 * @var string $tag_name The tag name.
 * @var string $tag_link The tag url.
 * @var array  $settings The widget settings.
 * @var int    $event_id The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Tags $widget The widget instance.
 */

?>
<a <?php tribe_classes( $widget->get_link_class() ); ?> href="<?php echo esc_url( $tag_link ); ?>"><?php echo esc_html( $tag_name ); ?></a>
