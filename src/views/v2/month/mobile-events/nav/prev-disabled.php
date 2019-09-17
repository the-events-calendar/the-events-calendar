<?php
/**
 * View: Month View Nav Disabled Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/mobile-events/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $label The label for the previous link.
 *
 * @version TBD
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button class="tribe-events-c-nav__prev tribe-common-b2" disabled>
		<?php echo esc_html( $label ); ?>
	</button>
</li>
