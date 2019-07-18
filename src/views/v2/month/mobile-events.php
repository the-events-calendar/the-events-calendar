<?php
/**
 * View: Month View Mobile Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/mobile-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

/**
 * Adding this as a temprorary data structure.
 * @todo: This array should contain the month with real events.
 */
$month = apply_filters( 'tribe_events_views_v2_month_demo_data', [] );

?>

<section class="tribe-events-calendar-month-mobile-events">

	<?php foreach( $month as $day ) : ?>

		<?php $this->template( 'month/mobile-events/mobile-day', [ 'day' => $day ] ); ?>

	<?php endforeach; ?>

	<?php $this->template( 'month/nav', [ 'location' => 'mobile' ] ); ?>

</section>
