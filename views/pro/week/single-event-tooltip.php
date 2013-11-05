<?php
/**
 * Week View Single Event Tooltip
 * This file sets up the content for the week view single event tooltip
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/single-event-tooltip.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php $event = tribe_events_week_get_event(); ?>
<div id="tribe-events-tooltip-<?php echo $event->ID; ?>" class="tribe-events-tooltip">
	<h4 class="entry-title summary"><?php echo $event->post_title; ?></h4>
	<div class="tribe-events-event-body">
		<div class="duration">
			<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime( $event->EventStartDate ) ); ?>">
				<?php

				if ( !empty( $event->EventStartDate ) )
					echo date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventStartDate ) );
				if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
					echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventStartDate ) );

				?>
			</abbr><!-- .dtstart -->
			<abbr class="tribe-events-abbr dtend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime( $event->EventEndDate ) ); ?>">
				<?php

				if ( !empty( $event->EventEndDate ) && $event->EventStartDate !== $event->EventEndDate ) {
					if ( date_i18n( 'Y-m-d', strtotime( $event->EventStartDate ) ) == date_i18n( 'Y-m-d', strtotime( $event->EventEndDate ) ) ) {
						$time_format = get_option( 'time_format', 'g:i a' );
						if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
							echo " – " . date_i18n( $time_format, strtotime( $event->EventEndDate ) );
					} else {
						echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventEndDate ) );
						if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
							echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventEndDate ) ) . '<br />';
					}
				}

				?>
			</abbr><!-- .dtend -->
		</div><!-- .duration -->

		<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $event->ID ) ) { ?>
			<div class="tribe-events-event-thumb "><?php echo get_the_post_thumbnail( $event->ID, array( 75, 75 ) );?></div>
		<?php } ?>

		<p class="entry-summary description">
		<?php if ( has_excerpt( $event->ID ) ) {
			echo TribeEvents::truncate( $event->post_excerpt, 30 );
		} else {
			echo TribeEvents::truncate( $event->post_content, 30 );
		} ?>
		</p><!-- .entry-summary -->

	</div><!-- .tribe-events-event-body -->
	<span class="tribe-events-arrow"></span>
</div><!-- .tribe-events-tooltip -->
