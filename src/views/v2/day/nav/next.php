<?php
/**
 * View: Day View Nav Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/nav/next.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $link The URL to the next page.
 *
 * @version 5.3.0
 */

$events_label_plural = tribe_get_event_label_plural();

// Translators: %s: Events label plural.
$next_day_label = sprintf( __( 'Next day\'s %s', 'the-events-calendar' ), $events_label_plural );

// Use the same text format for the visible button text.
$next_day_text = $next_day_label;
?>

<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--next">
	<a
		href="<?php echo esc_url( $link ); ?>"
		rel="next"
		class="tribe-events-c-nav__next tribe-common-b2 tribe-common-b1--min-medium"
		data-js="tribe-events-view-link"
		aria-label="<?php echo esc_attr( $next_day_text ); ?>"
		rel="<?php echo esc_attr( $next_rel ); ?>"
	>
		<?php esc_html_e( 'Next Day', 'the-events-calendar' ); ?>
		<?php $this->template( 'components/icons/caret-right', [ 'classes' => [ 'tribe-events-c-nav__next-icon-svg' ] ] ); ?>
	</a>
</li>
