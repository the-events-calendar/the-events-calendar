<?php
/**
 * View: Month View - Single Multiday Event Hidden Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/multiday-events/multiday-event/hidden/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @since 5.1.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 * @version 5.1.1
 */

use Tribe__Date_Utils as Dates;
?>
<time
	datetime="<?php echo esc_attr( $event->dates->start->format( Dates::DBDATEFORMAT ) ); ?>"
	class="tribe-common-a11y-visual-hide"
>
	<?php echo esc_attr( $event->dates->start->format( Dates::DBDATEFORMAT ) ); ?>
</time>
