<?php
/**
 * Eventbrite Upsell Banner
 *
 * @since 4.6.19
 *
 * @see Tribe__Events__Aggregator__Tabs__New::maybe_display_eventbrite_upsell()
 */
?>
<div class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="eventbrite">
	<div class="tribe-banner tribe-banner-eventbrite-tickets">
		<img src="<?php echo esc_url( tribe_events_resource_url( 'images/aggregator/eventbrite-tickets.svg' ) ) ; ?>">

		<h3><?php esc_html_e( 'Do more with Eventbrite Tickets', 'the-events-calendar' ); ?></h3>

		<a href="https://evnt.is/1a4d" class="tribe-license-link tribe-button tribe-button-primary" target="_blank">
			<?php esc_html_e( 'Learn more', 'the-events-calendar' ); ?>
			<span class="screen-reader-text">
				<?php esc_html_e( 'opens in a new window', 'the-events-calendar' ); ?>
			</span>
		</a>
	</div>
</div>
