<?php
	global $spEvents;
	$spEvents->loadDomainStylesScripts();
	
	get_header();
?>
	<div id="tec-content" class="upcoming">
		<div id='tec-events-calendar-header' class="clearfix">
			<h2 class="tec-cal-title"><?php _e('Calendar of Events', $spEvents->pluginDomain) ?></h2>
		<span class='tec-calendar-buttons'> 
			<a class='tec-button-on' href='<?php echo events_get_listview_link(); ?>'><?php _e('Event List', $spEvents->pluginDomain)?></a>
			<a class='tec-button-off' href='<?php echo events_get_gridview_link(); ?>'><?php _e('Calendar', $spEvents->pluginDomain)?></a>
		</span>

		</div><!--#tec-events-calendar-header-->

		<div id="tec-events-loop" class="tec-events post-list clearfix">
		<?php while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID() ?>" class="tec-event post clearfix<?php echo $alt ?>">
							    <div style="clear:both;"></div>
							        <?php if ( is_new_event_day() ) : ?>
					<h4 class="event-day"><?php echo the_event_start_date( null, false ); ?></h4>
							        <?php endif; ?>
						<?php the_title('<h2 class="entry-title"><a href="' . get_permalink() . '" title="' . the_title_attribute('echo=0') . '" rel="bookmark">', '</a></h2>'); ?>
					<div class="entry-content tec-event-entry">
						<?php the_excerpt() ?>
					</div> <!-- End tec-event-entry -->

					<div class="tec-event-list-meta">
		              <table cellspacing="0">
		                  <tr>
		                    <td class="tec-event-meta-desc"><?php _e('Start:', $spEvents->pluginDomain) ?></td>
		                    <td class="tec-event-meta-value"><?php echo the_event_start_date(); ?></td>
		                  </tr>
		                  <tr>
		                    <td class="tec-event-meta-desc"><?php _e('End:', $spEvents->pluginDomain) ?></td>
		                    <td class="tec-event-meta-value"><?php echo the_event_end_date(); ?></td>
		                  </tr>
		                  <?php
		                    $venue = the_event_venue();
		                    if ( !empty( $venue ) ) :
		                  ?>
		                  <tr>
		                    <td class="tec-event-meta-desc"><?php _e('Venue:', $spEvents->pluginDomain) ?></td>
		                    <td class="tec-event-meta-value"><?php echo $venue; ?></td>
		                  </tr>
		                  <?php endif; ?>
		                  <?php
		                    $phone = the_event_phone();
		                    if ( !empty( $phone ) ) :
		                  ?>
		                  <tr>
		                    <td class="tec-event-meta-desc"><?php _e('Phone:', $spEvents->pluginDomain) ?></td>
		                    <td class="tec-event-meta-value"><?php echo $phone; ?></td>
		                  </tr>
		                  <?php endif; ?>
		                  <?php if (tec_address_exists( $post->ID ) ) : ?>
		                  <tr>
							<td class="tec-event-meta-desc"><?php _e('Address:', $spEvents->pluginDomain); ?><br />
							<?php if( get_post_meta( $post->ID, '_EventShowMapLink', true ) == 'true' ) : ?>
								<a class="gmap" href="<?php event_google_map_link(); ?>" title="Click to view a Google Map" target="_blank"><?php _e('Google Map', $spEvents->pluginDomain ); ?></a>
							<?php endif; ?></td>
							<td class="tec-event-meta-value"><?php tec_event_address( $post->ID ); ?></td>
		                  </tr>
		                  <?php endif; ?>
		                  <?php
		                    $cost = the_event_cost();
		                    if ( !empty( $cost ) ) :
		                  ?>
	 		              <tr>
							<td class="tec-event-meta-desc"><?php _e('Cost:', $spEvents->pluginDomain) ?></td>
							<td class="tec-event-meta-value"><?php echo $cost; ?></td>
						 </tr>
		                  <?php endif; ?>
		              </table>
					</div>
					<div style="clear:both;"></div>
				</div> <!-- End post -->
				<div class="tec-events-list content_footer"></div>
	<?php $alt = ( empty( $alt ) ) ? ' alt' : '';?> 
		<?php endwhile; // posts ?>



		</div><!-- #tec-events-loop -->
		<div class="tec-nav" id="tec-nav-below">

			<div class="tec-nav-previous"><?php 
			// Display Previous Page Navigation
			if( events_displaying_upcoming() && get_previous_posts_link( ) ) : ?>
				<?php previous_posts_link( '<span>&laquo; Previous Events</span>' ); ?>
			<?php elseif( events_displaying_upcoming() && !get_previous_posts_link( ) ) : ?>
				<a href='<?php echo events_get_past_link(); ?>'><span><?php _e('&laquo; Previous Events', $spEvents->pluginDomain ); ?></span></a>
			<?php elseif( events_displaying_past() && get_next_posts_link( ) ) : ?>
				<?php next_posts_link( '<span>&laquo; Previous Events</span>' ); ?>
			<?php endif; ?>
			</div>

			<div class="tec-nav-next"><?php
			// Display Next Page Navigation
			if( events_displaying_upcoming() && get_next_posts_link( ) ) : ?>
				<?php next_posts_link( '<span>Next Events &raquo;</span>' ); ?>
			<?php elseif( events_displaying_past() && get_previous_posts_link( ) ) : ?>
				<?php previous_posts_link( '<span>Next Events &raquo;</span>' ); // a little confusing but in 'past view' to see newer events you want the previous page ?>
			<?php elseif( events_displaying_past() && !get_previous_posts_link( ) ) : ?>
				<a href='<?php echo events_get_upcoming_link(); ?>'><span><?php _e('Next Events &raquo;', $spEvents->pluginDomain); ?></span></a>
			<?php endif; ?>
			</div>

		</div>

	</div>


<?php
get_footer();
