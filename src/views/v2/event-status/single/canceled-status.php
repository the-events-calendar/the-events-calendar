<?php
/**
 * Status for a Canceled Event.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-control/single/canceled-status.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTICLE_LINK_HERE}
 *
 * @version TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */
namespace Tribe\Events\Event_Status;

use Tribe__Date_Utils as Dates;
use WP_Post;

// Don't print anything when status for this event is not
if ( 'canceled' !== $event->event_status ) {
	return;
}

?>
<div class="tribe-ext-events-control-single-notice tribe-ext-events-control-single-notice--canceled">
	<div class="tribe-ext-events-control-text">

		<div class="tribe-ext-events-control-single-notice-header tribe-ext-events-control-text--red tribe-ext-events-control-text--bold tribe-ext-events-control-text--alert-icon">
			<?php echo esc_html_x( 'Canceled', 'Text next to the date to display canceled', 'tribe-ext-events-control' ); ?>
		</div>

		<?php if ( $event->event_status_reason ) : ?>
			<div class="tribe-ext-events-control-single-notice-description">
				<?php echo wp_kses_post( $event->event_status_reason ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
