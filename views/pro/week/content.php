<?php
/**
 * Week View Content
 * The content template for the week view. This template is also used for
 * the response that is returned on week view ajax requests.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/content.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<div id="tribe-events-content" class="tribe-events-week-grid tribe-clearfix" data-mobilebreak="768">
	
	<!-- Calendar Title -->
	<?php do_action( 'tribe_events_before_the_title') ?>
	<h2 class="tribe-events-page-title"><?php tribe_events_title() ?></h2>
	<?php do_action( 'tribe_events_after_the_title') ?>

	<!-- Notices -->
	<?php tribe_events_the_notices() ?>

	<!-- Calendar Header -->
	<?php do_action( 'tribe_events_before_header') ?>
	<div id="tribe-events-header" <?php tribe_events_the_header_attributes('week-header') ?>>

		<!-- Header Navigation -->
		<?php tribe_get_template_part( 'pro/week/nav', 'header' ); ?>

	</div><!-- #tribe-events-header -->
	<?php do_action( 'tribe_events_after_header') ?>

	<!-- Calendar Grid -->
	<?php tribe_get_template_part( 'pro/week/loop', 'grid' ) ?>

	<!-- Calendar Footer -->
	<?php do_action( 'tribe_events_before_footer') ?>
	<div id="tribe-events-footer">

		<!-- Footer Navigation -->
		<?php do_action( 'tribe_events_before_footer_nav' ); ?>
		<?php tribe_get_template_part( 'pro/week/nav', 'footer' ); ?>
		<?php do_action( 'tribe_events_after_footer_nav' ); ?>
	</div><!-- #tribe-events-footer -->
	<?php do_action( 'tribe_events_after_footer') ?>

	<script type="text/html" id="tribe_tmpl_week_mobile">
		<div class="tribe-events-mobile [[=eventClass]]">
			<h4 class="summary">
				<a class="url" href="[[=permalink]]" title="[[=title]]" rel="bookmark">[[=title]]</a>
			</h4>
			<div class="tribe-events-event-body">
				<div class="updated published time-details">
					<span class="date-start dtstart">[[=startTime]] </span>
					[[ if(endTime.length) { ]]
					-<span class="date-end dtend"> [[=endTime]]</span>
					[[ } ]]
				</div>
				[[ if(imageSrc.length) { ]]
				<div class="tribe-events-event-image">
					<a href="[[=permalink]]" title="[[=title]]">
						<img src="[[=imageSrc]]" alt="[[=title]]" title="[[=title]]">
					</a>
				</div>
				[[ } ]]
				<p class="entry-summary description"></p>
				<a href="[[=permalink]]" class="tribe-events-read-more" rel="bookmark">Find out more »</a>
			</div>
		</div>
	</script>
	
</div><!-- #tribe-events-content -->