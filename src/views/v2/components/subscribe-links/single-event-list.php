<?php
/**
 * Component: Subscribe To Calendar List for Single Events.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/subscribe-links/single-event-list.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.16.0
 *
 * @var array<Tribe\Events\Views\V2\iCalendar\Links\Link_Abstract> $items Array containing subscribe/export objects.
 *
 */
if ( empty( $items ) ) {
	return;
}
?>
<div class="tribe-events tribe-common">
	<div class="tribe-events-c-subscribe-dropdown__container">
		<div class="tribe-events-c-subscribe-dropdown">
			<div class="tribe-common-c-btn-border tribe-events-c-subscribe-dropdown__button" tabindex="0">
				<?php $this->template( 'components/icons/cal-export', [ 'classes' => [ 'tribe-events-c-subscribe-dropdown__export-icon' ] ] ); ?>
				<button class="tribe-events-c-subscribe-dropdown__button-text">
					<?php echo esc_html__( 'Add to calendar', 'the-events-calendar' ); ?>
				</button>
				<?php $this->template( 'components/icons/caret-down', [ 'classes' => [ 'tribe-events-c-subscribe-dropdown__button-icon' ] ] ); ?>
			</div>
			<div class="tribe-events-c-subscribe-dropdown__content">
				<ul class="tribe-events-c-subscribe-dropdown__list" tabindex="0">
					<?php foreach ( $items as $item ) : ?>
						<?php $this->template( 'components/subscribe-links/item', [ 'item' => $item ] ); ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
