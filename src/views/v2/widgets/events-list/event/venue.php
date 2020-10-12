<?php
/**
 * Widget: Events List Event Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/events-list/event/venue.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @version TBD
 *
 * @var WP_Post            $event   The event post object with properties added by the `tribe_get_event` function.
 * @var array<string,bool> $display Associative array of display settings for event meta.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( ! $event->venues->count() ) {
	return;
}

if (
	empty( $display['venue'] )
	&& empty( $display['street'] )
	&& empty( $display['city'] )
	&& empty( $display['region'] )
	&& empty( $display['zip'] )
) {
	return;
}
?>
<div class="tribe-events-widget-events-list__event-venue tribe-common-b2">

	<?php if ( ! empty( $display['venue'] ) ) : ?>
		<span class="tribe-events-widget-events-list__event-venue-name tribe-common-b2--bold">
			<?php echo wp_kses_post( $venue->post_title ); ?>
		</span>
	<?php endif; ?>

	<?php
	if (
		! empty( $display['street'] )
		|| ! empty( $display['city'] )
		|| ! empty( $display['region'] )
		|| ! empty( $display['zip'] )
	) :
	?>
		<address class="tribe-events-widget-events-list__event-venue-address">

			<?php if ( ! empty( $display['street'] ) ) : ?>
				<div class="tribe-events-widget-events-list__event-venue-address-street">
					<?php echo esc_html( $venue->address ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $display['city'] ) || ! empty( $display['region'] ) || ! empty( $display['zip'] ) ) : ?>
				<div class="tribe-events-widget-events-list__event-venue-address-">
					<?php if ( ! empty( $display['city'] ) ) : ?>
						<span class="tribe-events-widget-events-list__event-venue-address-city">
							<?php echo esc_html( $venue->city ); ?>
						</span>
					<?php endif; ?>
					<?php if ( ! empty( $display['region'] ) ) : ?>
						<span class="tribe-events-widget-events-list__event-venue-address-region">
							<?php echo esc_html( $venue->state_province ); ?>
						</span>
					<?php endif; ?>
					<?php if ( ! empty( $display['zip'] ) ) : ?>
						<span class="tribe-events-widget-events-list__event-venue-address-zip">
							<?php echo esc_html( $venue->zip ); ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</address>
	<?php endif; ?>

</div>
