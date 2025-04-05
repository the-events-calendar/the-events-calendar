<?php
/**
 * View: Elementor Event Categories widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-categories.php
 *
 * @since 6.4.0
 *
 * @var bool             $show_header Whether to show the header.
 * @var array            $categories   The event categories.
 * @var string           $header_tag   The HTML tag to use for the header.
 * @var string           $header_text  The header text.
 * @var array            $settings     The widget settings.
 * @var int              $event_id     The event ID.
 * @var Event_Categories $widget       The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Categories;

if ( empty( $categories ) ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_element_classes() ); ?>>
	<?php $this->template( 'views/integrations/elementor/widgets/event-categories/header' ); ?>
	<div <?php tribe_classes( $widget->get_wrapper_class() ); ?>>
		<?php echo wp_kses_post( $categories ); ?>
	</div>
</div>
