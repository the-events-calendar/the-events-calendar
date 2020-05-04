<?php
/**
 * View: Month View - Multiday Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/calendar-events/calendar-event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 * @var obj     $date_formats Object containing the date formats.
 *
 * @see tribe_get_event() For the format of the event object.
 */

use Tribe__Date_Utils as Dates;

?>
<time
	datetime="<?php echo esc_attr( $event->dates->start->format( Dates::DBDATEFORMAT ) ); ?>"
	class="tribe-common-a11y-visual-hide"
>
	<?php echo esc_attr( $event->dates->start->format( Dates::DBDATEFORMAT ) ); ?>
</time>
