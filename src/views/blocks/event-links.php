<?php
/**
 * Block: Event Links
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-links.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 *
 */

use Tribe\Events\Views\V2\iCalendar\Links\Link_Abstract;

// don't show on password protected posts
if ( post_password_required() ) {
	return;
}

$has_google_cal = $this->attr( 'hasGoogleCalendar' );
$has_ical       = $this->attr( 'hasiCal' );


remove_filter( 'the_content', 'do_blocks', 9 );
$subscribe_links = empty( $this->get( ['subscribe_links'] ) ) ? false : $this->get( ['subscribe_links'] );

$should_render  = $subscribe_links && ( $has_google_cal || $has_ical );


if ( $has_google_cal ) {
	if ( $this->get( [ 'subscribe_links', 'gcal' ] ) instanceof Link_Abstract ) {
		$google_cal_link = $subscribe_links['gcal']->get_uri( null );
	} else {
		$google_cal_link = Tribe__Events__Main::instance()->esc_gcal_url( tribe_get_gcal_link() );
	}
}

if ( $has_ical ) {
	if ( $this->get( [ 'subscribe_links', 'ical' ] ) instanceof Link_Abstract ) {
		$ical_link = $subscribe_links['ical']->get_uri( null );
	} else {
		$ical_link = tribe_get_single_ical_link();
	}
}

if ( empty( $google_cal_link ) && empty( $ical_link ) ) {
	return;
}

?>
<div class="tribe-block tribe-block__events-link">

	<?php if ( ! empty( $google_cal_link ) ) : ?>
		<div class="tribe-block__btn--link tribe-block__events-gcal">
			<a
				href="<?php echo esc_url( $google_cal_link ); ?>"
				target="_blank"
				rel="noopener noreferrer nofollow"
				title="<?php esc_attr_e( 'Add to Google Calendar', 'the-events-calendar' ); ?>"
			>
				<img src="<?php echo Tribe__Main::instance()->plugin_url  . 'src/modules/icons/link.svg'; ?>" />
				<?php echo esc_html( $this->attr( 'googleCalendarLabel' ) ) ?>
			</a>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $ical_link ) ) : ?>
		<div class="tribe-block__btn--link tribe-block__events-ical">
			<a
				href="<?php echo esc_url( $ical_link ); ?>"
				rel="noopener noreferrer nofollow"
				title="<?php esc_attr_e( 'Add to iCalendar', 'the-events-calendar' ); ?>"
			>
				<img src="<?php echo Tribe__Main::instance()->plugin_url  . 'src/modules/icons/link.svg'; ?>" />
				<?php echo esc_html( $this->attr( 'iCalLabel' ) ) ?>
			</a>
		</div>
	<?php endif; ?>

</div>

<?php add_filter( 'the_content', 'do_blocks', 9 );
