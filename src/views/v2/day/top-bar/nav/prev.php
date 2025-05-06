<?php
/**
 * View: Top Bar Navigation Previous Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/top-bar/nav/prev.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 *
 * @version 5.3.0
 *
 */
$events_label_plural   = tribe_get_event_label_plural();

// Translators: %s: Events label plural.
$previous_day_label = sprintf( __( 'Previous day\'s %s', 'the-events-calendar' ), $events_label_plural );
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<a
		href="<?php echo esc_url( $prev_url ); ?>"
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--prev"
		aria-label="<?php echo esc_attr( $previous_day_label ) ?>"
		data-js="tribe-events-view-link"
		rel="<?php echo esc_attr( $prev_rel ); ?>"
	>
		<?php $this->template( 'components/icons/caret-left', [ 'classes' => [ 'tribe-common-c-btn-icon__icon-svg', 'tribe-events-c-top-bar__nav-link-icon-svg' ] ] ); ?>
	</a>
</li>
