<?php
/**
 * View: Elementor Event Navigation next link.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-navigation/next.php
 *
 * @since 6.4.0
 *
 * @var string   $next_link  The URl to the next event.
 * @var ?WP_Post $next_event The next event.
 * @var int      $event_id   The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Navigation $widget The widget instance.
 */

if ( empty( $next_event ) || empty( $next_link ) ) {
	return;
}

?>
<li <?php tribe_classes( $widget->get_next_class() ); ?>>
	<a href="<?php echo esc_url( $next_link ); ?>">
		<?php echo esc_html( $next_event->post_title ); ?>
		<?php
		echo wp_kses(
			file_get_contents( Tribe__Main::instance()->plugin_path . '/src/resources/images/icons/caret-right.svg' ),
			[
				'svg'  => [
					'fill'    => true,
					'viewBox' => true,
					'xmlns'   => true,
					'id'      => true,
					'title'   => true,

				],
				'g'    => [
					'id'              => true,
					'stroke-width'    => true,
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
				],
				'path' => [
					'd' => true,
				],
			]
		);
		?>
	</a>
</li>
