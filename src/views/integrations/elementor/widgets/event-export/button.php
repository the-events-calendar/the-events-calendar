<?php
/**
 * View: Elementor Event Export widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-export/button.php
 *
 * @since 6.4.0
 *
 * @var Event_Export $widget   The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Export;

?>

<button
	<?php tribe_classes( $widget->get_button_class(), 'tribe-common-c-btn-border', 'tribe-events-c-subscribe-dropdown__button' ); ?>
	aria-expanded="false"
	aria-controls="<?php $widget->get_content_class(); ?>"
	aria-label="<?php esc_attr_e( 'View links to add events to your calendar', 'the-events-calendar' ); ?>"
>
	<i
		<?php
		tribe_classes(
			[
				'eicon',
				'eicon-export-kit',
				$widget->get_export_icon_class(),

			]
		);
		?>
		aria-hidden="true"
	></i>
	<?php esc_html_e( 'Add to calendar', 'the-events-calendar' ); ?>
	<svg
		<?php tribe_classes( $widget->get_dropdown_icon_class() ); ?>
		viewBox="0 0 12 8"
		xmlns="http://www.w3.org/2000/svg"
	>
		<path d="M1.21.85L6 5.64 10.79.85 11.94 2 6 7.94.06 2z" fill="currentColor" fill-rule="nonzero"/>
	</svg>
</button>
