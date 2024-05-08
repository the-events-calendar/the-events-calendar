<?php
/**
 * View: Elementor Event Datetime widget - time separator.
 * Used to separate the time and date. As the "@" in  "January 1, 2020 @ 12:00 pm".
 * Corresponds to the TEC dateTimeSeparator setting.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-datetime/range-separator.php
 *
 * @since 6.4.0
 *
 * @var bool            $is_all_day        Whether the event is all day on a single day.
 * @var bool            $is_same_start_end Whether the start and end date and time are the same.
 * @var bool            $show_date         Whether to show the date.
 * @var bool            $show_header       Whether to show the header.
 * @var bool            $show_time         Whether to show the time.
 * @var bool            $show_year         Whether to show the year.
 * @var bool            $show_time_zone    Whether to show the time zone.
 * @var string          $all_day_text      The all day text.
 * @var string          $end_date          The formatted end date. (hidden if show_date is false)
 * @var string          $end_time          The formatted end time. (hidden if show_time is false)
 * @var string          $header_tag        The HTML tag for the header.
 * @var string          $header_text       The header text.
 * @var string          $html_tag          The HTML tag for the date content.
 * @var string          $is_same_day       Whether the start and end date are the same.
 * @var string          $start_date        The formatted start date. (hidden if show_date is false)
 * @var string          $start_time        The formatted start time. (hidden if show_time is false)
 * @var Template_Engine $this              The template engine.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Template_Engine;
$symbol = tribe_get_option( 'dateTimeSeparator', '@' );
?>

<span <?php tribe_classes( $widget->get_separator_class() ); ?>>
	<?php
	printf(
		/* translators: Separator between the date and time. */
		esc_html_x( ' %s ', 'Datetime separator with a space on either side', 'the-events-calendar' ),
		esc_html( $symbol )
	);
	?>
</span>
