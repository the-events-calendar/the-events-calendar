<?php
/**
 * View: Day View Nav Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $next_url The URL to the next page, if any, or an empty string.
 * @var string $today_url The URL to the today page, if any, or an empty string.
 *
 * @since 6.15.16 Add aria-label attribute to nav. [TEC-5732]
 *
 * @version 6.15.16
 */

$aria_label = sprintf(
	/* translators: %s: Event label plural */
	__( 'Bottom %s list pagination', 'the-events-calendar' ),
	tribe_get_event_label_plural_lowercase()
);
?>
<nav class="tribe-events-calendar-day-nav tribe-events-c-nav" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<ul class="tribe-events-c-nav__list">
		<?php
		if ( ! empty( $prev_url ) ) {
			$this->template( 'day/nav/prev', [ 'link' => $prev_url ] );
		} else {
			$this->template( 'day/nav/prev-disabled' );
		}
		?>

		<?php
		if ( ! empty( $next_url ) ) {
			$this->template( 'day/nav/next', [ 'link' => $next_url ] );
		} else {
			$this->template( 'day/nav/next-disabled' );
		}
		?>
	</ul>
</nav>
