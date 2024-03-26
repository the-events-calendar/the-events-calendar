<?php
/**
 * View: Elementor Event Categories widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-categories.php
 *
 * @since TBD
 *
 * @var bool             $show_heading Whether to show the heading.
 * @var array            $categories   The event categories.
 * @var string           $heading_tag  The HTML tag to use for the heading.
 * @var array            $settings     The widget settings.
 * @var int              $event_id     The event ID.
 * @var Event_Categories $widget       The widget instance.
 */

use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Event_Categories;

if ( empty( $categories ) ) {
	return;
}

?>

<div <?php tribe_classes( $widget->get_element_classes() ); ?>>
	<?php
	$this->template(
		'views/integrations/elementor/widgets/event-categories/header',
		[
			'show'        => $show_heading,
			'heading_tag' => $heading_tag,
			'settings'    => $settings,
			'event_id'    => $event_id,
			'widget'      => $widget,
		]
	);
	?>
	<div <?php tribe_classes( $widget->get_wrapper_class() ); ?>>
	<?php
		echo wp_kses_post( $widget->do_categories() );
	?>
	</div>
</div>
