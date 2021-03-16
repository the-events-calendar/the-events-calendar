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