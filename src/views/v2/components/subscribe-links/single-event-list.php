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
			<div class="tribe-common-c-btn-border tribe-events-c-subscribe-dropdown__button">
				<?php $this->template( 'components/icons/cal-export', [ 'classes' => [ 'tribe-events-c-subscribe-dropdown__export-icon' ] ] ); ?>
				<button
					class="tribe-events-c-subscribe-dropdown__button-text"
					aria-expanded="false"
					aria-controls="tribe-events-subscribe-dropdown-content"
					aria-label="<?php echo esc_attr__( 'View links to add events to your calendar', 'the-events-calendar' ); ?>"
				>
					<?php echo esc_html__( 'Add to calendar', 'the-events-calendar' ); ?>
				</button>
				<?php $this->template( 'components/icons/caret-down', [ 'classes' => [ 'tribe-events-c-subscribe-dropdown__button-icon' ] ] ); ?>
			</div>
			<div id="tribe-events-subscribe-dropdown-content" class="tribe-events-c-subscribe-dropdown__content">
				<ul class="tribe-events-c-subscribe-dropdown__list">
					<?php foreach ( $items as $item ) : ?>
						<?php $this->template( 'components/subscribe-links/item', [ 'item' => $item ] ); ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
