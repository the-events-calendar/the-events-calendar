<?php
/**
 * View: Month View Nav Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/nav/prev.php
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
<a
	href="<?php echo esc_url( $link ); ?>"
	rel="prev"
	class="tribe-events-c-nav__prev tribe-common-b2"
	data-js="tribe-events-view-link"
>
	<?php echo esc_html( $label ); ?>
</a>
