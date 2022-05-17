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

$has_google_cal   = $this->attr( 'hasGoogleCalendar' );
$has_ical         = $this->attr( 'hasiCal' );
$has_outlook_365  = $this->attr( 'hasOutlook365' );
$has_outlook_live = $this->attr( 'hasOutlookLive' );


remove_filter( 'the_content', 'do_blocks', 9 );
$subscribe_links = empty( $this->get( ['subscribe_links'] ) ) ? false : $this->get( ['subscribe_links'] );

$should_render  = $subscribe_links && ( $has_google_cal || $has_ical || $has_outlook_365 || $has_outlook_live );

$items = [];

if ( $has_google_cal && $this->get( [ 'subscribe_links', 'gcal' ] ) instanceof Link_Abstract ) {
	$items[] = $this->get( [ 'subscribe_links', 'gcal' ] );
}

if ( $has_ical && $this->get( [ 'subscribe_links', 'ical' ] ) instanceof Link_Abstract ) {
	$items[] = $this->get( [ 'subscribe_links', 'ical' ] );
}

if ( $has_outlook_365 && $this->get( [ 'subscribe_links', 'outlook-365' ] ) instanceof Link_Abstract ) {
	$items[] = $this->get( [ 'subscribe_links', 'outlook-365' ] );
}

if ( $has_outlook_live && $this->get( [ 'subscribe_links', 'outlook-live' ] ) instanceof Link_Abstract ) {
	$items[] = $this->get( [ 'subscribe_links', 'outlook-live' ] );
}

if ( empty( $items ) ) {
	return;
}

remove_filter( 'the_content', 'do_blocks', 9 );
?>
<div class="tribe-block tribe-block__events-link">
	<div class="tribe-events tribe-common">
		<div class="tribe-events-c-subscribe-dropdown__container">
			<div class="tribe-events-c-subscribe-dropdown">
				<div class="tribe-common-c-btn-border tribe-events-c-subscribe-dropdown__button" tabindex="0">
					<?php $this->template( 'v2/components/icons/cal-export', [ 'classes' => [ 'tribe-events-c-subscribe-dropdown__export-icon' ] ] ); ?>
					<button class="tribe-events-c-subscribe-dropdown__button-text">
						<?php echo esc_html__( 'Add to calendar', 'the-events-calendar' ); ?>
					</button>
					<?php $this->template( 'v2/components/icons/caret-down', [ 'classes' => [ 'tribe-events-c-subscribe-dropdown__button-icon' ] ] ); ?>
				</div>
				<div class="tribe-events-c-subscribe-dropdown__content">
					<ul class="tribe-events-c-subscribe-dropdown__list" tabindex="0">
						<?php foreach ( $items as $item ) : ?>
							<li class="tribe-events-c-subscribe-dropdown__list-item">
								<a
									href="<?php echo esc_url( $item->get_uri( null ) ); ?>"
									class="tribe-events-c-subscribe-dropdown__list-item-link"
									tabindex="0"
									target="_blank"
									rel="noopener noreferrer nofollow"
								>
									<?php echo esc_html( $item->get_label( null ) ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<?php add_filter( 'the_content', 'do_blocks', 9 );
