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

<div id="tribe-events-bar">

	<form id="tribe-bar-form" class="tribe-clearfix" name="tribe-bar-form" method="post" action="<?php echo add_query_arg( array() ); ?>">

			<?php do_action( 'tribe-events-bar-show-filters', $filters ); ?>

			<?php do_action( 'tribe-events-bar-show-views', $views ); ?>

	</form><!-- #tribe-bar-form -->

</div><!-- #tribe-events-bar -->
