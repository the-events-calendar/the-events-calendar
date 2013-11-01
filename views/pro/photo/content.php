<?php
/**
 * Photo View Content
 * The content template for the photo view of events. This template is also used for
 * the response that is returned on photo view ajax requests.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/photo/content.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<div id="tribe-events-content" class="tribe-events-list tribe-events-photo">

	<!-- Photo View Title -->
	<?php do_action( 'tribe_events_before_the_title' ); ?>
	<h2 class="tribe-events-page-title"><?php echo tribe_get_events_title(); ?></h2>
	<?php do_action( 'tribe_events_after_the_title' ); ?>

	<!-- Notices -->
	<?php tribe_events_the_notices(); ?>

	<!-- Photo View Header -->
	<?php do_action( 'tribe_events_before_header' ); ?>
	<div id="tribe-events-header" <?php tribe_events_the_header_attributes(); ?>>

		<!-- Header Navigation -->
		<?php do_action( 'tribe_events_before_header_nav' ); ?>
		<?php tribe_get_template_part( 'pro/photo/nav', 'header' ); ?>
		<?php do_action( 'tribe_events_after_header_nav' ); ?>

	</div><!-- #tribe-events-header -->
	<?php do_action( 'tribe_events_after_header' ); ?>

	<!-- Events Loop -->
	<?php if ( have_posts() ) : ?>
		<?php do_action( 'tribe_events_before_loop' ); ?>
		<?php tribe_get_template_part( 'pro/photo/loop' ); ?>
		<?php do_action( 'tribe_events_after_loop' ); ?>
	<?php endif; ?>

	<!-- List Footer -->
	<?php do_action( 'tribe_events_before_footer' ); ?>
	<div id="tribe-events-footer">

		<!-- Footer Navigation -->
		<?php do_action( 'tribe_events_before_footer_nav' ); ?>
		<?php tribe_get_template_part( 'pro/photo/nav', 'footer' ); ?>
		<?php do_action( 'tribe_events_after_footer_nav' ); ?>

	</div><!-- #tribe-events-footer -->
	<?php do_action( 'tribe_events_after_footer' ); ?>

</div><!-- #tribe-events-content -->