<?php
/**
 * View: List View Nav Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $next_url The URL to the next page, if any, or an empty string.
 * @var string $today_url The URL to the today page, if any, or an empty string.
 *
 * @since 4.9.10
 * @since 6.15.7 Added aria-label attribute to nav.
 *
 * @version 6.15.7
 */

$aria_label = sprintf(
	/* translators: %s: Event label plural */
	__( 'Bottom %s list pagination', 'the-events-calendar' ),
	tribe_get_event_label_plural_lowercase()
);

?>
<nav class="tribe-events-calendar-list-nav tribe-events-c-nav" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<ul class="tribe-events-c-nav__list">
		<?php
		if ( ! empty( $prev_url ) ) {
			$this->template( 'list/nav/prev', [ 'link' => $prev_url ] );
		} else {
			$this->template( 'list/nav/prev-disabled' );
		}
		?>

		<?php $this->template( 'list/nav/today' ); ?>

		<?php
		if ( ! empty( $next_url ) ) {
			$this->template( 'list/nav/next', [ 'link' => $next_url ] );
		} else {
			$this->template( 'list/nav/next-disabled' );
		}
		?>
	</ul>
</nav>
