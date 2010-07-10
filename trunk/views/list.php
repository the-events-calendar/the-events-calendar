<?php
	global $spEvents;
	$spEvents->loadDomainStylesScripts();
	
	get_header();
?>
	<div id="tec-content" class="upcoming">
		<div id='tec-events-calendar-header' class="clearfix">
			<h2 class="tec-cal-title"><?php _e('Calendar of Events', $spEvents->pluginDomain) ?></h2>
		<span class='tec-calendar-buttons'> 
			<a class='tec-button-on' href='<?php echo sp_get_listview_link(); ?>'><?php _e('Event List', $spEvents->pluginDomain)?></a>
			<a class='tec-button-off' href='<?php echo sp_get_gridview_link(); ?>'><?php _e('Calendar', $spEvents->pluginDomain)?></a>
		</span>

		</div><!--#tec-events-calendar-header-->
		<div id="tec-events-loop" class="tec-events post-list clearfix">
		<?php while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID() ?>" <?php post_class('tec-event clearfix') ?>>
							        <?php if ( sp_is_new_event_day() ) : ?>
					<h4 class="event-day"><?php echo sp_get_start_date( null, false ); ?></h4>
							        <?php endif; ?>
						<?php the_title('<h2 class="entry-title"><a href="' . get_permalink() . '" title="' . the_title_attribute('echo=0') . '" rel="bookmark">', '</a></h2>'); ?>
					<div class="entry-content tec-event-entry">
						<?php the_content(); ?>
					</div> <!-- End tec-event-entry -->

					<div class="tec-event-list-meta">
		              <table cellspacing="0">
		                  <tr>
		                    <td class="tec-event-meta-desc"><?php _e('Start:', $spEvents->pluginDomain) ?></td>
		                    <td class="tec-event-meta-value"><?php echo sp_get_start_date(); ?></td>
		                  </tr>
		                  <tr>
		                    <td class="tec-event-meta-desc"><?php _e('End:', $spEvents->pluginDomain) ?></td>
		                    <td class="tec-event-meta-value"><?php echo sp_get_end_date(); ?></td>
		                  </tr>
		                  <?php
		                    $venue = sp_get_venue();
		                    if ( !empty( $venue ) ) :
		                  ?>
		                  <tr>
		                    <td class="tec-event-meta-desc"><?php _e('Venue:', $spEvents->pluginDomain) ?></td>
		                    <td class="tec-event-meta-value"><?php echo $venue; ?></td>
		                  </tr>
		                  <?php endif; ?>
		                  <?php
		                    $phone = sp_get_phone();
		                    if ( !empty( $phone ) ) :
		                  ?>
		                  <tr>
		                    <td class="tec-event-meta-desc"><?php _e('Phone:', $spEvents->pluginDomain) ?></td>
		                    <td class="tec-event-meta-value"><?php echo $phone; ?></td>
		                  </tr>
		                  <?php endif; ?>
		                  <?php if (sp_address_exists( $post->ID ) ) : ?>
		                  <tr>
							<td class="tec-event-meta-desc"><?php _e('Address:', $spEvents->pluginDomain); ?><br />
							<?php if( get_post_meta( $post->ID, '_EventShowMapLink', true ) == 'true' ) : ?>
								<a class="gmap" href="<?php sp_the_map_link(); ?>" title="Click to view a Google Map" target="_blank"><?php _e('Google Map', $spEvents->pluginDomain ); ?></a>
							<?php endif; ?></td>
							<td class="tec-event-meta-value"><?php sp_the_full_address( $post->ID ); ?></td>
		                  </tr>
		                  <?php endif; ?>
		                  <?php
		                    $cost = sp_get_cost();
		                    if ( !empty( $cost ) ) :
		                  ?>
	 		              <tr>
							<td class="tec-event-meta-desc"><?php _e('Cost:', $spEvents->pluginDomain) ?></td>
							<td class="tec-event-meta-value"><?php echo $cost; ?></td>
						 </tr>
		                  <?php endif; ?>
		              </table>
					</div>
				</div> <!-- End post -->
		<?php endwhile; // posts ?>



		</div><!-- #tec-events-loop -->
		<div id="tec-nav-below" class="tec-nav clearfix">

			<div class="tec-nav-previous"><?php 
			// Display Previous Page Navigation
			if( sp_is_upcoming() && get_previous_posts_link( ) ) : ?>
				<?php previous_posts_link( '<span>&laquo; Previous Events</span>' ); ?>
			<?php elseif( sp_is_upcoming() && !get_previous_posts_link( ) ) : ?>
				<a href='<?php echo sp_get_past_link(); ?>'><span><?php _e('&laquo; Previous Events', $spEvents->pluginDomain ); ?></span></a>
			<?php elseif( sp_is_past() && get_next_posts_link( ) ) : ?>
				<?php next_posts_link( '<span>&laquo; Previous Events</span>' ); ?>
			<?php endif; ?>
			</div>

			<div class="tec-nav-next"><?php
			// Display Next Page Navigation
			if( sp_is_upcoming() && get_next_posts_link( ) ) : ?>
				<?php next_posts_link( '<span>Next Events &raquo;</span>' ); ?>
			<?php elseif( sp_is_past() && get_previous_posts_link( ) ) : ?>
				<?php previous_posts_link( '<span>Next Events &raquo;</span>' ); // a little confusing but in 'past view' to see newer events you want the previous page ?>
			<?php elseif( sp_is_past() && !get_previous_posts_link( ) ) : ?>
				<a href='<?php echo sp_get_upcoming_link(); ?>'><span><?php _e('Next Events &raquo;', $spEvents->pluginDomain); ?></span></a>
			<?php endif; ?>
			</div>

		</div>
		<a title="<?php esc_attr_e('iCal Import', $spEvents->pluginDomain) ?>" class="ical" href="<?php echo sp_get_ical_link(); ?>"><?php _e('iCal Import', $spEvents->pluginDomain) ?></a>
	</div>


<?php
get_footer();
