<?php
/**
 * View: Elementor Event Navigation previous link.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-navigation/previous.php
 *
 * @since 6.4.0
 *
 * @var string   $prev_link  The URl to the previous event.
 * @var ?WP_Post $prev_event The previous event.
 * @var int      $event_id   The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Navigation $widget The widget instance.
 */

if ( empty( $prev_event ) || empty( $prev_link ) ) {
	return;
}
?>
<li <?php tec_classes( $widget->get_prev_class() ); ?>>
<a href="<?php echo esc_url( $prev_link ); ?>">
		<?php
		echo wp_kses(
			file_get_contents( Tribe__Main::instance()->plugin_path . '/src/resources/images/icons/caret-left.svg' ),
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
		<?php echo esc_html( $prev_event->post_title ); ?>
	</a>
</li>
