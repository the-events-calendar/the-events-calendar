<?php
/**
 * View: Month Event Multiday
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/event-multiday.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */

$event    = $this->get( 'event' );
$event_id = $event->ID;

$classes = [ 'tribe-events-calendar-month__event-multiday' ];

if ( $is_featured = tribe( 'tec.featured_events' )->is_featured( $event_id ) ) {
	$classes[] = 'tribe-events-calendar-month__event-multiday--featured';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<time datetime="the-date-and-or-duration" class="tribe-common-a11y-visual-hide">The date and duration</time>
	<a class="tribe-events-calendar-month__event-multiday-inner tribe-events-calendar-month__event-multiday-inner--border-left tribe-events-calendar-month__event-multiday-inner--border-right">
		<?php if ( $is_featured ) : ?>
			<em
				class="tribe-events-calendar-month__event-multiday-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
				aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
				title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
			></em>
		<?php endif; ?>
		<h3 class="tribe-events-calendar-month__event-multiday-title tribe-common-h8">
			Lorem ipsum
		</h3>
	</a>
</div>