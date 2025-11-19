<?php
/**
 * View: Day View Time separator
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/time-separator.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.15.12
 *
 * @since 4.9.11
 * @since 6.15.12 Wrap time element in an H2 to improve accessibility. [TEC-5627]
 *
 */

use Tribe\Events\Views\V2\Utils;
use Tribe__Date_Utils as Dates;

$should_have_time_separator = Utils\Separators::should_have_time( $this->get( 'events' ), $event );

if ( ! $should_have_time_separator || ! empty( $event->timeslot ) ) {
	return;
}

$event_start_hour = strtotime( Dates::round_nearest_half_hour( $event->dates->start_display->format( Dates::DBDATETIMEFORMAT ) ) );

// Format to WP format.
$separator_text = date_i18n( tribe_get_time_format(), $event_start_hour );
$time_attribute = date_i18n( 'H:i', $event_start_hour );

?>
<div class="tribe-events-calendar-day__time-separator">
	<h2 class="tribe-events-calendar-day__time-separator-heading tribe-common-h7 tribe-common-h6--min-medium tribe-common-h--alt">
		<time datetime="<?php echo esc_attr( $time_attribute ); ?>">
			<?php echo esc_html( $separator_text ); ?>
		</time>
	</h2>
</div>
