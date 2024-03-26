<?php
/**
 * View: Elementor Event Tags widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-tags.php
 *
 * @since TBD
 *
 * @var bool       $show_heading Whether to show the heading.
 * @var string     $heading_tag  The HTML tag for the heading.
 * @var string     $label_text   The label text.
 * @var array      $tags         The event tags.
 * @var array      $settings     The widget settings.
 * @var int        $event_id     The event ID.
 * @var Event_Tags $widget       The widget instance.
 */

use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Event_Tags;

// No tags, no render.
if ( empty( $tags ) ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_element_classes() ); ?>>
	<?php
	$this->template(
		'views/integrations/elementor/widgets/event-tags/header',
		[
			'show'        => $show_heading,
			'heading_tag' => $heading_tag,
			'label_text'  => $label_text,
			'event_id'    => $event_id,
			'settings'    => $settings,
			'widget'      => $widget,
		]
	);
	?>
	<div <?php tribe_classes( $widget->get_links_class() ); ?>>
		<?php
		foreach ( $tags as $tag_name => $tag_link ) {
			$this->template(
				'views/integrations/elementor/widgets/event-tags/content',
				[
					'tag_name' => $tag_name,
					'tag_link' => $tag_link,
					'event_id' => $event_id,
					'settings' => $settings,
					'widget'   => $widget,
				]
			);
		}
		?>
	</div>
</div>
