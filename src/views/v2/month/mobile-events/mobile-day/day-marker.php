<?php
/**
 * View: Month View Day marker
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/mobile-day/day-marker.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 4.9.4
 * @since 5.1.5 format_i18n method from Utilize\Tribe\Utils\Date_I18n_Immutable for date display.
 * @since 4.6.0   Add logic around date separator to allow for displaying multiple days in the list.
 *
 * @version 4.6.0
 *
 * @var string $request_date Date for the day displayed. May not be the same as the event date.
 *
 */
use Tribe__Date_Utils as Dates;


use Tribe\Events\Views\V2\Utils;

if ( empty( $is_past ) && ! empty ( $request_date ) ) {
	$should_have_day_separator = Utils\Separators::should_have_day( $this->get( 'events' ), $event, $request_date );
} else {
	$should_have_day_separator = Utils\Separators::should_have_day( $this->get( 'events' ), $event );
}

if ( ! $should_have_day_separator ) {
	return;
}

$day_date_obj = Dates::build_date_object( $event->dates->start_display );
?>
<div class="tribe-events-c-day-marker tribe-events-calendar-month-mobile-events__day-marker">
	<time
		class="tribe-events-c-day-marker__date tribe-common-h7 tribe-common-h--alt"
		datetime="<?php echo esc_attr( $day_date_obj->format_i18n( Dates::DBDATEFORMAT ) ); ?>"
	>
		<?php echo esc_html( $day_date_obj->format_i18n( tribe_get_date_format() ) ); ?>
	</time>
</div>
