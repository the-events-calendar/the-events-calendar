<?php
/**
 * View: Day View Nav Disabled Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.3.0
 */

$events_label_plural = tribe_get_event_label_plural();

// Translators: %s: Events label plural.
$previous_day_text = sprintf( __( 'Previous day\'s %s', 'the-events-calendar' ), $events_label_plural );
?>

<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button
		class="tribe-events-c-nav__prev tribe-common-b2 tribe-common-b1--min-medium"
		aria-label="<?php echo esc_attr( $previous_day_text ); ?>"
		disabled
	>
		<?php $this->template( 'components/icons/caret-left', [ 'classes' => [ 'tribe-events-c-nav__prev-icon-svg' ] ] ); ?>
		<?php esc_html_e( 'Previous Day', 'the-events-calendar' ); ?>
	</button>
</li>
