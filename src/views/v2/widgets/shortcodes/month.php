<?php
/**
 * Shortcodes: Month View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/shortcodes/month.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @since 5.5.0
 */

$widget_view = tribe( Widget_View::class );
$widget_template =
$compatibility_classes = $widget_view->get_compatibility_classes();
$container_classes = $widget_view->get_html_classes();


ob_start(); ?>
<div <?php tribe_classes( $compatibility_classes ); ?>>
	<div
		<?php tribe_classes( $container_classes ); ?>
	>
		<div class="tribe-events-widget-month">

			<?php //$widget_view->template( 'widgets/widget-month/widget-title' ); ?>

			<?php echo $shortcode_html; ?>

			<?php //$widget_view->template( 'widgets/widget-month/view-more' ); ?>
		</div>
	</div>
</div>
