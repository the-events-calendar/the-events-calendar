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
 * @version TBD
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button class="tribe-events-c-nav__prev tribe-common-b2" disabled>
		<?php
			echo sprintf(
				wp_kses(
					/* translators: %s: Event (plural or singular). */
					__( 'Previous<span class="tribe-events-c-nav__prev-label-plural"> %s</span>', 'the-events-calendar' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				tribe_get_event_label_plural()
			);
		?>
	</button>
</li>
