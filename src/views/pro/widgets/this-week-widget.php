<?php
/**
 * This Week Event Widget
 * This is the template for the output of the this week widget.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/widgets/this-week-widget.php
 *
 * @package TribeEventsCalendarPro
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<div class="tribe-this-week-widget-wrapper tribe-this-week-widget-<?php echo esc_attr( $this_week_template_vars['layout'] ); ?> <?php echo esc_attr( tribe_this_week_widget_class( $this_week_query_vars['tax_query'] ) ); ?>" <?php echo apply_filters( 'tribe_events_this_week_header_attributes', $this_week_data_attrs ); ?> >

	<!-- This Week Title -->
	<?php do_action( 'tribe_events_before_this_week_title' ) ?>
		<h2 class="tribe-events-page-title"><?php echo esc_html( tribe_events_get_this_week_title( $this_week_template_vars['start_date'] ) ); ?></h2>
	<?php do_action( 'tribe_events_after_this_week_title' ) ?>

	<!-- This Week Header Navigation -->
	<?php tribe_get_template_part( 'pro/widgets/this-week/nav', 'header', array( 'start_date' => $this_week_template_vars['start_date'], 'end_date' => $this_week_template_vars['end_date'] ) ); ?>

	<!-- This Week Grid -->
	<div class="tribe-this-week-widget-weekday-wrapper <?php echo esc_html( 'true' === $this_week_template_vars['hide_weekends'] ? 'tribe-this-week-widget-hide-weekends' : '' );  ?>" >

		<?php foreach ( $week_days as $day ) : ?>

			<!-- This Week Day -->
			<?php tribe_get_template_part( 'pro/widgets/this-week/loop-grid-day', 'grid-dau', array( 'day' => $day, 'this_week_template_vars' => $this_week_template_vars ) ); ?>

		<?php endforeach; ?>

	</div>

</div>

<?php
if ( ( isset( $args['widget_id'] ) || isset( $instance['widget_id'] ) ) && ( isset( $instance['highlight_color'] ) && $instance['highlight_color'] != '' ) ) {

	//Set Highlight Color for Widget or For Shortcode based on ID from Respective System
	$wrap_id = isset( $args['widget_id'] ) ? $args['widget_id'] : '';
    if ( is_numeric( $wrap_id ) ) {
        $wrap_id = isset( $instance['widget_id'] ) ? 'tribe-this-week-events-widget-100' . $instance['widget_id'] : $wrap_id;
    }
?>

	<style>
		#<?php echo esc_attr( $wrap_id );  ?> .tribe-this-week-event {
			border-color : <?php echo esc_attr( $instance['highlight_color'] ); ?>;
		}

		#<?php echo esc_attr( $wrap_id );  ?> .this-week-today .tribe-this-week-widget-header-date {
			background-color : <?php echo esc_attr( $instance['highlight_color'] ); ?>;
		}
	</style>

<?php
}