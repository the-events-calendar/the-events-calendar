<?php
/**
 * Single Recurring Description Template Part
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/single-event/recurring-description.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version TBD
 *
 */

$recurrence_data        = get_post_meta( $post_id, '_EventRecurrence', true );
$recurrence_description = ! empty( $recurrence_data['description'] )
	? esc_html( $recurrence_data['description'] )
	: esc_html__( 'Recurring event', 'the-events-calendar' );
?>

<div class="tribe-events-single-event-recurrence-description">
	<img src="<?php echo esc_url( Tribe__Main::instance()->plugin_url . 'src/modules/icons/recurrence.svg' ); ?>" />

	<span><?php echo $recurrence_description; // phpcs:ignore StellarWP.XSS.EscapeOutput.OutputNotEscaped -- Escaped on assignment above. ?></span>

	<?php if ( function_exists( 'tribe_all_occurrences_link' ) ) : ?>
		<a href="<?php echo esc_url( tribe_all_occurrences_link( $post_id, false ) ); ?>">
			<?php echo esc_html__( 'see all', 'the-events-calendar' ); ?>
		</a>
	<?php endif; ?>

</div>
