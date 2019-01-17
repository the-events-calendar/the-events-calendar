<?php
/**
 * Single Recurring Description Template Part
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/single-event/recurring-description.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.2
 *
 */

$recurrence_data = get_post_meta( $post_id, '_EventRecurrence', true );
$recurrence_description = $recurrence_data['description'] ? $recurrence_data['description'] : esc_html__( 'Recurring event', 'the-events-calendar' );
?>

<div class="tribe-events-single-event-recurrence-description">
	<img src="<?php echo Tribe__Main::instance()->plugin_url  . 'src/modules/icons/recurrence.svg'; ?>" />

	<span><?php echo $recurrence_description ?></span>

	<a href="<?php echo esc_url( tribe_all_occurences_link( $post_id, false ) ) ?>">
		<?php echo __( 'see all', 'the-events-calendar' ) ?>
	</a>

</div>
