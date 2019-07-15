<?php
/**
 * View: Month View Nav Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $prev_label The label for the previous link.
 * @var string $next_url The URL to the next page, if any, or an empty string.
 * @var string $next_label The label for the next link.
 * @var string $today_url The URL to the today page, if any, or an empty string.
 * @var string $location The location of the nav.
 *
 * @version 4.9.4
 *
 */
?>
<nav class="tribe-events-calendar-month-nav tribe-events-calendar-month-nav--<?php echo esc_attr( $location ); ?> tribe-events-c-nav">
	<ul class="tribe-events-c-nav__list">
		<?php
		if ( ! empty( $prev_url ) ) {
			$this->template( 'month/nav/prev', [ 'label' => __( 'May', 'the-events-calendar' ), 'link' => $prev_url ] );
		} else {
			$this->template( 'month/nav/prev-disabled', [ 'label' => __( 'May', 'the-events-calendar' ) ] );
		}
		?>

		<?php $this->template( 'month/nav/today', [ 'link' => '#' ] ) ?>

		<?php
		if ( ! empty( $next_url ) ) {
			$this->template( 'month/nav/next', [ 'label' => __( 'July', 'the-events-calendar' ), 'link' => $next_url ] );
		} else {
			$this->template( 'month/nav/next-disabled', [ 'label' => __( 'July', 'the-events-calendar' ) ] );
		}
		?>
	</ul>
</nav>
