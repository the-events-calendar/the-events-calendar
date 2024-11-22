<?php
/**
 * View: Elementor Event Export widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-export.php
 *
 * @since 6.4.0
 *
 * Guaranteed variables:
 * @var array        $settings               The widget settings.
 * @var bool         $show                   Whether to show the widget.
 * @var bool         $show_gcal_link         Whether to show the Google Calendar link.
 * @var bool         $show_ical_link         Whether to show the iCalendar link.
 * @var bool         $show_outlook_365_link  Whether to show the Outlook 365 link.
 * @var bool         $show_outlook_live_link Whether to show the Outlook Live link.
 * @var int          $event_id               The event ID.
 * @var Event_Export $widget                 The widget instance.
 *
 * Additional optional variables based on user-input.
 * Each of these variables is only guaranteed to be present if the user has chosen to display the respective link.
 * Each is in the format:
 * $gcal = [
 *     'label' => string,
 *     'link'  => string,
 *     'class' => array,
 * ]
 * @var array        $gcal                   The Google Calendar link and label.
 * @var array        $ical                   The iCalendar link and label.
 * @var array        $outlook_365            The Outlook 365 link and label.
 * @var array        $outlook_live           The Outlook Live link and label.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Export;

// Ensure the Links are avail.
$gcal         ??= null;
$ical         ??= null;
$outlook_365  ??= null;
$outlook_live ??= null;

// Ensure we don't get notices.
$show_gcal_link         ??= false;
$show_ical_link         ??= false;
$show_outlook_365_link  ??= false;
$show_outlook_live_link ??= false;

$links_list = [
	'gcal'         => [
		'link'           => $gcal,
		'should_display' => $show_gcal_link,
	],
	'ical'         => [
		'link'           => $ical,
		'should_display' => $show_ical_link,
	],
	'outlook_365'  => [
		'link'           => $outlook_365,
		'should_display' => $show_outlook_365_link,
	],
	'outlook_live' => [
		'link'           => $outlook_live,
		'should_display' => $show_outlook_live_link,
	],
];

if ( empty( $show ) ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_widget_class() ); ?>>
	<div <?php tribe_classes( $widget->get_dropdown_class() ); ?>>
		<?php $this->template( 'widgets/event-export/button' ); ?>
		<div
			<?php tribe_classes( $widget->get_content_class() ); ?>
			style="display: none;"
		>
			<ul <?php tribe_classes( $widget->get_list_class() ); ?>>
				<?php foreach ( $links_list as $item ) : ?>
					<?php $this->template( 'widgets/event-export/list-item', $item ); ?>
				<?php endforeach; ?>
			</ul>
		</div>

		<script>
			// Some JS to toggle the dropdown, todo: maybe move this to a separate file?
			var exportButton = document.querySelector( ".<?php echo esc_attr( $widget->get_button_class() ); ?>" );
			var exportDropdownContent = document.querySelector( ".<?php echo esc_attr( $widget->get_content_class() ); ?>" );
			var exportDropdownIcon = document.querySelector( ".<?php echo esc_attr( $widget->get_dropdown_icon_class() ); ?>" );

			exportButton.addEventListener(
				"click",
				function () {
					let isClosed = exportDropdownContent.style.display !== "block";
					exportButton.ariaExpanded = isClosed ? "true" : "false"; // Update aria-expanded.
					exportDropdownContent.style.display = isClosed ? "block" : "none"; // Toggle display.
					exportDropdownIcon.classList.toggle( "<?php echo esc_attr( $widget->get_dropdown_icon_class() ); ?>--active" ); // Toggle arrow direction.
				}
			);
		</script>
	</div>
</div>
