<?php
/**
 * View: Top Bar - Navigation
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/top-bar/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $next_url The URL to the next page, if any, or an empty string.
 *
 * @since 5.0.1
 * @since TBD Added aria-label attribute to nav.
 *
 * @version TBD
 */

$arial_label = sprintf(
	/* translators: %s: Event label singular */
	__( '%s list top navigation', 'the-events-calendar' ),
	tribe_get_event_label_singular()
);

?>
<nav class="tribe-events-c-top-bar__nav tribe-common-a11y-hidden" aria-label="<?php echo esc_attr( $arial_label ); ?>">
	<ul class="tribe-events-c-top-bar__nav-list">
		<?php
		if ( ! empty( $prev_url ) ) {
			$this->template( 'list/top-bar/nav/prev' );
		} else {
			$this->template( 'list/top-bar/nav/prev-disabled' );
		}
		?>

		<?php
		if ( ! empty( $next_url ) ) {
			$this->template( 'list/top-bar/nav/next' );
		} else {
			$this->template( 'list/top-bar/nav/next-disabled' );
		}
		?>
	</ul>
</nav>
