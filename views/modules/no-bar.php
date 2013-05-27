<?php
/**
 * Events Navigation Bar Module Template
 * Renders our events navigation bar used across our views
 *
 * $filters and $views variables are loaded in and coming from
 * the show funcion in: lib/tribe-events-bar.class.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php

$filters = tribe_events_get_filters();
$views = tribe_events_get_views();

 ?>

<?php do_action('tribe_events_bar_before_template') ?>
<div id="tribe-events-bar" class="tribe-bar-is-disabled">

	<form id="tribe-bar-form" class="tribe-clearfix" name="tribe-bar-form" method="post" action="<?php echo add_query_arg( array() ); ?>">

		<?php if ( !empty( $filters ) ) { ?>
		<div class="tribe-bar-datepicker">
				<?php foreach ( $filters as $filter ) : ?>
					<div class="<?php echo esc_attr( $filter['name'] ) ?>-filter">
						<label class="label-<?php echo esc_attr( $filter['name'] ) ?>" for="<?php echo esc_attr( $filter['name'] ) ?>"><?php echo $filter['caption'] ?></label>
						<?php echo $filter['html'] ?>
					</div>
				<?php endforeach; ?>
		</div><!-- .tribe-bar-filters -->
		<?php } // if ( !empty( $filters ) ) ?>
		
		<!-- Views -->
		<?php if ( count( $views ) > 1 ) { ?>
		<div id="tribe-bar-views">
			<div class="tribe-bar-views-inner tribe-clearfix">
				<h3 class="tribe-events-visuallyhidden"><?php _e( 'Event Views Navigation', 'tribe-events-calendar' ) ?></h3>
				<label><?php _e( 'View As', 'tribe-events-calendar' ); ?></label><select class="tribe-select2 tribe-no-param" name="tribe-bar-view">
					<?php foreach ( $views as $view ) : ?>
						<option <?php echo tribe_is_view($view['displaying']) ? 'selected' : 'tribe-inactive' ?> value="<?php echo $view['url'] ?>" data-view="<?php echo $view['displaying'] ?>">
							<?php echo $view['anchor'] ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div><!-- .tribe-bar-views-inner -->
		</div><!-- .tribe-bar-views -->
		<?php } // if ( count( $views ) > 1 ) ?>

	</form><!-- #tribe-bar-form -->

</div><!-- #tribe-events-bar -->
<?php do_action('tribe_events_bar_after_template') ?>
