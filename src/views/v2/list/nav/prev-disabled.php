<?php
/**
 * View: List View Nav Disabled Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the previous page, if any, or an empty string.
 *
 * @version 4.9.4
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button class="tribe-events-c-nav__prev tribe-common-b2" disabled>
		<?php echo esc_html( sprintf( __( 'Previous %s', 'the-events-calendar' ), tribe_get_event_label_plural() ) ); ?>
	</button>
</li>
