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
 * @var string $next_url The URL to the next page, if any, or an empty string.
 *
 * @version 4.9.3
 *
 */
?>
<nav class="tribe-events-calendar-month-nav tribe-events-c-nav">
	<ul class="tribe-events-c-nav__list">
		<li class="tribe-events-c-nav__list-item">
			<?php
			if ( $prev_url ) {
				$this->template( 'month/nav/prev', [ 'label' => __( 'May', 'the-events-calendar' ), 'link' => $prev_url ] );
			} else {
				$this->template( 'month/nav/prev-disabled', [ 'label' => __( 'May', 'the-events-calendar' ) ] );
			}
			?>
		</li>

		<li class="tribe-events-c-nav__list-item">
			<?php
			if ( $next_url ) {
				$this->template( 'month/nav/next', [ 'label' => __( 'July', 'the-events-calendar' ), 'link' => $next_url ] );
			} else {
				$this->template( 'month/nav/next-disabled', [ 'label' => __( 'July', 'the-events-calendar' ) ] );
			}
			?>
		</li>
	</ul>
</nav>
