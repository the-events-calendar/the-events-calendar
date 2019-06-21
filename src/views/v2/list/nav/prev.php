<?php
/**
 * View: List View Nav Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/nav/prev.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the previous page, if any, or an empty string.
 *
 * @version 4.9.3
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<a
		href="<?php echo esc_url( $link ); ?>"
		rel="prev"
		class="tribe-events-c-nav__prev tribe-common-b2"
		data-js="tribe-events-view-link"
	>
		<?php echo esc_html( sprintf( __( 'Previous %s', 'the-events-calendar' ), tribe_get_event_label_plural() ) ); ?>
	</a>
</li>
