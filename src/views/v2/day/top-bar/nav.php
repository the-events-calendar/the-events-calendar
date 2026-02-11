<?php
/**
 * View: Top Bar - Navigation
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/top-bar/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.15.16
 *
 * @since 6.15.16 Add aria-label attribute to nav. [TEC-5732]
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $next_url The URL to the next page, if any, or an empty string.
 */

$aria_label = sprintf(
	/* translators: %s: Event label plural */
	__( 'Top %s list pagination', 'the-events-calendar' ),
	tribe_get_event_label_plural_lowercase()
);
?>
<nav class="tribe-events-c-top-bar__nav tribe-common-a11y-hidden" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<ul class="tribe-events-c-top-bar__nav-list">
		<?php
		if ( ! empty( $prev_url ) ) {
			$this->template( 'day/top-bar/nav/prev' );
		} else {
			$this->template( 'day/top-bar/nav/prev-disabled' );
		}
		?>

		<?php
		if ( ! empty( $next_url ) ) {
			$this->template( 'day/top-bar/nav/next' );
		} else {
			$this->template( 'day/top-bar/nav/next-disabled' );
		}
		?>
	</ul>
</nav>
