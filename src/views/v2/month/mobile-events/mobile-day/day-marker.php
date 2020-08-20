<?php
/**
 * View: Month View Day marker
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/mobile-day/day-marker.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @since 4.9.4
 * @since 5.1.5 Utilize\Tribe\Utils\Date_I18n_Immutable's format_i18n method for date display.
 *
 * @version 5.1.5
 *
 * @var string $day_date Date for this marker, following `Y-m-d` format.
 *
 */
use Tribe__Date_Utils as Dates;

if ( ! isset( $day_date ) ) {
	return;
}

$day_date_obj = Dates::build_date_object( $day_date );
?>
<div class="tribe-events-c-day-marker tribe-events-calendar-month-mobile-events__day-marker">
	<time
		class="tribe-events-c-day-marker__date tribe-common-h7 tribe-common-h--alt"
		datetime="<?php echo esc_attr( $day_date_obj->format_i18n( Dates::DBDATEFORMAT ) ); ?>"
	>
		<?php echo esc_html( $day_date_obj->format_i18n( tribe_get_date_format() ) ); ?>
	</time>
</div>
