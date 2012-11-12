<?php
/**
* The TEC template for a list of events. This includes the Past Events and Upcoming Events views 
* as well as those same views filtered to a specific category.
*
* You can customize this view by putting a replacement file of the same name (list.php) in the events/ directory of your theme.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<div id="tribe-events-content" class="upcoming">

	<?php if(!tribe_is_day()): // day view doesn't have a grid ?>
		<div id='tribe-events-calendar-header' class="clearfix">
		<span class='tribe-events-calendar-buttons'> 
			<a class='tribe-events-button-on' href='<?php echo tribe_get_listview_link(); ?>'><?php _e('Event List', 'tribe-events-calendar'); ?></a>
			<a class='tribe-events-button-off' href='<?php echo tribe_get_gridview_link(); ?>'><?php _e('Calendar', 'tribe-events-calendar'); ?></a>
		</span>

		</div><!--tribe-events-calendar-header-->
	<?php endif; ?>
	<div id="tribe-events-loop" class="tribe-events-events post-list clearfix">
	
	<?php if (have_posts()) : ?>
	<?php $hasPosts = true; $first = true; ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php global $more; $more = false; ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class('tribe-events-event clearfix'); ?> itemscope itemtype="http://schema.org/Event">
			<?php if ( tribe_is_new_event_day() && !tribe_is_day() && !tribe_is_multiday() ) : ?>
				<h4 class="event-day"><?php echo tribe_get_start_date( null, false ); ?></h4>
			<?php endif; ?>
			<?php if( !tribe_is_day() && tribe_is_multiday() ) : ?>
				<h4 class="event-day"><?php echo tribe_get_start_date( null, false ); ?> – <?php echo tribe_get_end_date( null, false ); ?></h4>
			<?php endif; ?>
			<?php if ( tribe_is_day() && $first ) : $first = false; ?>
				<h4 class="event-day"><?php echo tribe_event_format_date(strtotime(get_query_var('eventDate')), false); ?></h4>
			<?php endif; ?>
			<?php the_title('<h2 class="entry-title" itemprop="name"><a href="' . tribe_get_event_link() . '" title="' . the_title_attribute('echo=0') . '" rel="bookmark">', '</a></h2>'); ?>
			<div class="entry-content tribe-events-event-entry" itemprop="description">
				<?php if (has_excerpt ()): ?>
					<?php the_excerpt(); ?>
				<?php else: ?>
					<?php the_content(); ?>
				<?php endif; ?>
			</div> <!-- End tribe-events-event-entry -->

			<div class="tribe-events-event-list-meta" itemprop="location" itemscope itemtype="http://schema.org/Place">
				<table cellspacing="0">
					<?php if (tribe_is_multiday() || !tribe_get_all_day()): ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e('Start:', 'tribe-events-calendar'); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="startDate" content="<?php echo tribe_get_start_date(); ?>"><?php echo tribe_get_start_date(); ?></td>
					</tr>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e('End:', 'tribe-events-calendar'); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="endDate" content="<?php echo tribe_get_end_date(); ?>"><?php echo tribe_get_end_date(); ?></td>
					</tr>
					<?php else: ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e('Date:', 'tribe-events-calendar'); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="startDate" content="<?php echo tribe_get_start_date(); ?>"><?php echo tribe_get_start_date(); ?></td>
					</tr>
					<?php endif; ?>

					<?php
						$venue = tribe_get_venue();
						if ( !empty( $venue ) ) :
					?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e('Venue:', 'tribe-events-calendar'); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="name">
							<?php if( class_exists( 'TribeEventsPro' ) ): ?>
								<?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
							<?php else: ?>
								<?php echo tribe_get_venue( get_the_ID() ); ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php endif; ?>
					<?php
						$phone = tribe_get_phone();
						if ( !empty( $phone ) ) :
					?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e('Phone:', 'tribe-events-calendar'); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="telephone"><?php echo $phone; ?></td>
					</tr>
					<?php endif; ?>
					<?php if (tribe_address_exists( get_the_ID() ) ) : ?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e('Address:', 'tribe-events-calendar'); ?><br />
						<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
							<a class="gmap" itemprop="maps" href="<?php echo tribe_get_map_link(); ?>" title="Click to view a Google Map" target="_blank"><?php _e('Google Map', 'tribe-events-calendar' ); ?></a>
						<?php endif; ?></td>
						<td class="tribe-events-event-meta-value"><?php echo tribe_get_full_address( get_the_ID() ); ?></td>
					</tr>
					<?php endif; ?>
					<?php
						$cost = tribe_get_cost();
						if ( !empty( $cost ) ) :
					?>
					<tr>
						<td class="tribe-events-event-meta-desc"><?php _e('Cost:', 'tribe-events-calendar'); ?></td>
						<td class="tribe-events-event-meta-value" itemprop="price"><?php echo $cost; ?></td>
					 </tr>
					<?php endif; ?>
				</table>
			</div>
		</div> <!-- End post -->
	<?php endwhile;// posts ?>
	<?php else :?>
		<div class="tribe-events-no-entry">
		<?php 
			$tribe_ecp = TribeEvents::instance();
			if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
				$cat = get_term_by( 'slug', get_query_var('term'), $tribe_ecp->get_event_taxonomy() );
				if( tribe_is_upcoming() ) {
					$is_cat_message = sprintf(__(' listed under %s. Check out past events for this category or view the full calendar.','tribe-events-calendar'),$cat->name);
				} else if( tribe_is_past() ) {
					$is_cat_message = sprintf(__(' listed under %s. Check out upcoming events for this category or view the full calendar.','tribe-events-calendar'),$cat->name);
				}
			}
		?>
		<?php if(tribe_is_day()): ?>
			<?php printf( __('No events scheduled for <strong>%s</strong>. Please try another day.', 'tribe-events-calendar'), date_i18n('F d, Y', strtotime(get_query_var('eventDate')))); ?>
		<?php endif; ?>

		<?php if(tribe_is_upcoming()){ ?>
			<?php _e('No upcoming events', 'tribe-events-calendar');
			echo !empty($is_cat_message) ? $is_cat_message : "."; ?>

		<?php }elseif(tribe_is_past()){ ?>
			<?php _e('No previous events' , 'tribe-events-calendar');
			echo !empty($is_cat_message) ? $is_cat_message : "."; ?>
		<?php } ?>
		</div>
	<?php endif; ?>


	</div><!-- #tribe-events-loop -->
	<div id="tribe-events-nav-below" class="tribe-events-nav clearfix">

		<div class="tribe-events-nav-previous"><?php 
		// Display Previous Page Navigation
		if( tribe_is_upcoming() && get_previous_posts_link() ) : ?>
			<?php previous_posts_link( '<span>'.__('&laquo; Previous Events', 'tribe-events-calendar').'</span>' ); ?>
		<?php elseif( tribe_is_upcoming() && !get_previous_posts_link( ) ) : ?>
			<a href='<?php echo tribe_get_past_link(); ?>'><span><?php _e('&laquo; Previous Events', 'tribe-events-calendar' ); ?></span></a>
		<?php elseif( tribe_is_past() && get_next_posts_link( ) ) : ?>
			<?php next_posts_link( '<span>'.__('&laquo; Previous Events', 'tribe-events-calendar').'</span>' ); ?>
		<?php endif; ?>
		</div>

		<div class="tribe-events-nav-next"><?php
		// Display Next Page Navigation
		if( tribe_is_upcoming() && get_next_posts_link( ) ) : ?>
			<?php next_posts_link( '<span>'.__('Next Events &raquo;', 'tribe-events-calendar').'</span>' ); ?>
		<?php elseif( tribe_is_past() && get_previous_posts_link( ) ) : ?>
			<?php previous_posts_link( '<span>'.__('Next Events &raquo;', 'tribe-events-calendar').'</span>' ); // a little confusing but in 'past view' to see newer events you want the previous page ?>
		<?php elseif( tribe_is_past() && !get_previous_posts_link( ) ) : ?>
			<a href='<?php echo tribe_get_upcoming_link(); ?>'><span><?php _e('Next Events &raquo;', 'tribe-events-calendar'); ?></span></a>
		<?php endif; ?>
		</div>

	</div>
	<?php if ( !empty($hasPosts) && function_exists('tribe_get_ical_link') ): ?>
		<a title="<?php esc_attr_e('iCal Import', 'tribe-events-calendar'); ?>" class="ical" href="<?php echo tribe_get_ical_link(); ?>"><?php _e('iCal Import', 'tribe-events-calendar'); ?></a>
	<?php endif; ?>
</div>
