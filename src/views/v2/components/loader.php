<?php
/**
 * View: Loader
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/loader.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @version 5.2.0
 *
 */
?>
<div
	class="tribe-events-view-loader tribe-common-a11y-hidden"
	role="alert"
	aria-live="polite"
>
	<span class="tribe-events-view-loader__text tribe-common-a11y-visual-hide">
		<?php esc_html_e( 'Loading view.', 'the-events-calendar' ); ?>
	</span>
	<div class="tribe-events-view-loader__dots tribe-common-c-loader">
		<div class="tribe-common-c-loader__dot tribe-common-c-loader__dot--first"></div>
		<div class="tribe-common-c-loader__dot tribe-common-c-loader__dot--second"></div>
		<div class="tribe-common-c-loader__dot tribe-common-c-loader__dot--third"></div>
	</div>
</div>
