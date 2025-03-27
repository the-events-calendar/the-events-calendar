<?php
/**
 * View: Loader
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/loader.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.2.0
 *
 * @var array $events List of events found.
 */

$events ??= [];
?>
<div
	class="tribe-events-view-loader tribe-common-a11y-hidden"
	role="alert"
	aria-live="polite"
>
	<span class="tribe-events-view-loader__text tribe-common-a11y-visual-hide">
		<?php
		$count = count( $events );
		// @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, StellarWP.XSS.EscapeOutput.OutputNotEscaped
		printf(
			/* translators: 1: number of events found, 2: lowercased events text */
			esc_html__( '%1$d %2$s found.', 'the-events-calendar' ),
			$count,
			$count === 1 ? tribe_get_event_label_singular_lowercase() : tribe_get_event_label_plural_lowercase()
		);
		// @phpcs:enable
		?>
	</span>
	<div class="tribe-events-view-loader__dots tribe-common-c-loader">
		<?php $this->template( 'components/icons/dot', [ 'classes' => [ 'tribe-common-c-loader__dot', 'tribe-common-c-loader__dot--first' ] ] ); ?>
		<?php $this->template( 'components/icons/dot', [ 'classes' => [ 'tribe-common-c-loader__dot', 'tribe-common-c-loader__dot--second' ] ] ); ?>
		<?php $this->template( 'components/icons/dot', [ 'classes' => [ 'tribe-common-c-loader__dot', 'tribe-common-c-loader__dot--third' ] ] ); ?>
	</div>
</div>
