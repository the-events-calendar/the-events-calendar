<?php
/**
 * View: Elementor Event Image widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-image.php
 *
 * @since TBD
 *
 * @var string      $image    The image attachment HTML.
 * @var int         $event_id The event ID.
 * @var Event_Image $widget   The widget instance.
 */

use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Event_Image;

if ( ! $image ) {

	if ( $this->is_preview_mode() || $this->is_edit_mode() ) {
		return;
	}

	return;
}
?>
<div <?php tribe_classes( $widget->get_element_classes() ); ?>>
	<?php
	// Escape, allowing for expected image attributes.
	echo wp_kses(
		$image,
		[
			'img' => [
				'alt'           => true,
				'class'         => true,
				'decoding'      => true,
				'fetchpriority' => true,
				'height'        => true,
				'id'            => true,
				'loading'       => true,
				'sizes'         => true,
				'src'           => true,
				'srcset'        => true,
				'width'         => true,
			],
		],
	);
	?>
</div>
