<?php
/**
 * View: Elementor Event Datetime widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/event-datetime.php
 *
 * @since TBD
 *
 * @var Template_Engine $this              The template engine.
 * @var bool            $show_header       Whether to show the header.
 * @var string          $html_tag          The HTML tag for the date content.
 * @var bool            $show_date         Whether to show the date.
 * @var bool            $show_time         Whether to show the time.
 * @var bool            $show_year         Whether to show the year.
 * @var string          $start_date        The formatted start date. (hidden if show_date is false)
 * @var string          $end_date          The formatted end date. (hidden if show_date is false)
 * @var string          $start_time        The formatted start time. (hidden if show_time is false)
 * @var string          $end_time          The formatted end time. (hidden if show_time is false)
 * @var string          $is_same_day       Whether the start and end date are the same.
 * @var bool            $is_all_day        Whether the event is all day on a single day.
 * @var bool            $is_same_start_end Whether the start and end date and time are the same.
 *
 * @var Event_Datetime  $this-            The widget instance.
 */

use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Event_Datetime;
use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Template_Engine;

if ( ! $this->has_event() || ! $show ) {
	return;
}

$widget = $this->get_widget();
?>
<?php
$this->template(
	'views/integrations/elementor/widgets/event-datetime/header',
	[ 'show' => $show_header ]
);
?>
<<?php echo tag_escape( $html_tag ); ?> <?php tribe_classes( $widget->get_widget_class() ); ?>>
<?php if ( $show_date && $start_date ) : ?>
	<span <?php tribe_classes( $widget->get_date_class(), $widget->get_start_date_class() ); ?>><?php echo esc_html( $start_date ); ?></span>
	<?php if ( $show_time && $start_time ) : ?>
		<span <?php tribe_classes( $widget->get_separator_class() ); ?>> - </span>
	<?php endif; ?>
<?php endif; ?>
<?php if ( $show_time && $start_time ) : ?>
	<span <?php tribe_classes( $widget->get_time_class(), $widget->get_start_time_class() ); ?>><?php echo esc_html( $start_time ); ?></span>
<?php endif; ?>
<?php if ( $is_all_day ) : ?>
	<span <?php tribe_classes( $widget->get_all_day_class() ); ?>><?php esc_html_e( 'All day', 'the-events-calendar' ); ?></span>
<?php endif; ?>
<?php if ( ! $is_same_start_end && ( $show_date || $show_time ) ) : ?>
	<?php if ( $show_date && $show_time ) : ?>
		<span <?php tribe_classes( $widget->get_separator_class() ); ?>> - </span>
	<?php endif; ?>
	<?php if ( $show_date && ! $is_same_day && $end_date ) : ?>
		<span <?php tribe_classes( $widget->get_date_class(), $widget->get_end_date_class() ); ?>><?php echo esc_html( $end_date ); ?></span>
		<?php if ( $show_time && $end_time ) : ?>
			<span <?php tribe_classes( $widget->get_separator_class() ); ?>> - </span>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ( $show_time && $end_time ) : ?>
		<span <?php tribe_classes( $widget->get_separator_class() ); ?>> - </span>
		<span <?php tribe_classes( $widget->get_time_class(), $widget->get_end_time_class() ); ?>><?php echo esc_html( $end_time ); ?></span>
	<?php endif; ?>
<?php endif; ?>
</<?php echo tag_escape( $html_tag ); ?>>
