<?php
/**
 * Component: Subscribe To Calendar
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/ical-link.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 1.0.0
 *
 * @var array $subscribe_links Array containing subscribe/export labels and links
 *
 * @package Tribe\Extensions\Subscribe_To_Calendar
 */

?>
<div class="subscribe-to-calendar-dropdown">
	<div class="subscribe-to-calendar-dropdown-selector">
		<button class="subscribe-to-calendar-dropdown-selector-button">
			<span class="subscribe-to-calendar-dropdown-selector-button-text">
				<?php echo esc_html__( 'Subscribe to calendar', 'the-events-calendar' ); ?>
			</span>
			<svg class="subscribe-to-calendar-dropdown-svgicon subscribe-to-calendar-dropdown-svgicon-caret-down" viewBox="0 0 10 7" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M1.008.609L5 4.6 8.992.61l.958.958L5 6.517.05 1.566l.958-.958z" class="subscribe-to-calendar-dropdown-svgicon-fill"/>
			</svg>
		</button>
		<div class="subscribe-to-calendar-dropdown-selector-content">
			<ul class="subscribe-to-calendar-dropdown-selector-list">
				<?php foreach ( $subscribe_links as $subscribe_link ) : ?>
					<li class="subscribe-to-calendar-dropdown-selector-list-item">
						<a href="<?php echo esc_url( $subscribe_link['uri'] ); ?>"class="subscribe-to-calendar-dropdown-selector-list-item-link">
							<span class="subscribe-to-calendar-dropdown-selector-list-item-text">
								<?php echo esc_html( $subscribe_link['label'] ); ?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
