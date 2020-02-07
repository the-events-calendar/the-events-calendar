<?php
/**
 * View: Month View Day marker
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/mobile-day/day-marker.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 * @var string $day_date Date for this marker, following `Y-m-d` format.
 *
 */
use Tribe__Date_Utils as Dates;

if ( ! isset( $day_date ) ) {
	return;
}

$day_date_datetime = Dates::build_date_object( $day_date )->getTimestamp()
?>
<div class="tribe-events-c-day-marker tribe-events-calendar-month-mobile-events__day-marker">
	<time
		class="tribe-events-c-day-marker__date tribe-common-h7 tribe-common-h--alt"
		datetime="<?php echo esc_attr( date_i18n( Dates::DBDATEFORMAT, $day_date_datetime ) ); ?>"
	>
		<?php echo esc_html( date_i18n( tribe_get_date_format(), $day_date_datetime ) ); ?>
	</time>
</div>
