<?php
/**
 * View: Events Bar Search Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/events-bar/search-button.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.3.0
 *
 */
?>
<button
	class="tribe-events-c-events-bar__search-button"
	aria-controls="tribe-events-search-container"
	aria-expanded="false"
	data-js="tribe-events-search-button"
>
	<?php $this->template( 'components/icons/search', [ 'classes' => [ 'tribe-events-c-events-bar__search-button-icon-svg' ] ] ); ?>
	<span class="tribe-events-c-events-bar__search-button-text tribe-common-a11y-visual-hide">
		<?php esc_html_e( 'Search', 'the-events-calendar' ); ?>
	</span>
</button>
