<?php
/**
* List View
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<div id="tec-content" class="upcoming">
	<div id='tec-events-calendar-header' class="clearfix">
	<span class='tec-calendar-buttons'> 
		<a class='tec-button-on' href='<?php echo tribe_get_listview_link(); ?>'><?php _e('Event List', $tribe_ecp->pluginDomain)?></a>
		<a class='tec-button-off' href='<?php echo tribe_get_gridview_link(); ?>'><?php _e('Calendar', $tribe_ecp->pluginDomain)?></a>
	</span>

	</div><!--#tec-events-calendar-header-->
	<div id="tec-events-loop" class="tec-events post-list clearfix">
	
	<?php if (have_posts()) : ?>
	<?php while ( have_posts() ) : the_post(); ?>

			<div id="post-<?php the_ID() ?>" <?php post_class('tec-event clearfix') ?>>
						        <?php if ( tribe_is_new_event_day() ) : ?>
				<h4 class="event-day"><?php echo tribe_get_start_date( null, false ); ?></h4>
						        <?php endif; ?>
					<?php the_title('<h2 class="entry-title"><a href="' . tribe_get_event_link() . '" title="' . the_title_attribute('echo=0') . '" rel="bookmark">', '</a></h2>'); ?>
				<div class="entry-content tec-event-entry">
					<?php if (has_excerpt ()): ?>
						<?php the_excerpt(); ?>
					<?php else: ?>
						<?php the_content(); ?>
					<?php endif; ?>
				</div> <!-- End tec-event-entry -->

				<div class="tec-event-list-meta">
	              <table cellspacing="0">
	                  <tr>
	                    <td class="tec-event-meta-desc"><?php _e('Start:', $tribe_ecp->pluginDomain) ?></td>
	                    <td class="tec-event-meta-value"><?php echo tribe_get_start_date(); ?></td>
	                  </tr>
	                  <tr>
	                    <td class="tec-event-meta-desc"><?php _e('End:', $tribe_ecp->pluginDomain) ?></td>
	                    <td class="tec-event-meta-value"><?php echo tribe_get_end_date(); ?></td>
	                  </tr>
	                  <?php
	                    $venue = tribe_get_venue();
	                    if ( !empty( $venue ) ) :
	                  ?>
	                  <tr>
	                    <td class="tec-event-meta-desc"><?php _e('Venue:', $tribe_ecp->pluginDomain) ?></td>
	                    <td class="tec-event-meta-value"><?php echo $venue; ?></td>
	                  </tr>
	                  <?php endif; ?>
	                  <?php
	                    $phone = tribe_get_phone();
	                    if ( !empty( $phone ) ) :
	                  ?>
	                  <tr>
	                    <td class="tec-event-meta-desc"><?php _e('Phone:', $tribe_ecp->pluginDomain) ?></td>
	                    <td class="tec-event-meta-value"><?php echo $phone; ?></td>
	                  </tr>
	                  <?php endif; ?>
	                  <?php if (tribe_address_exists( $post->ID ) ) : ?>
	                  <tr>
						<td class="tec-event-meta-desc"><?php _e('Address:', $tribe_ecp->pluginDomain); ?><br />
						<?php if( get_post_meta( $post->ID, '_EventShowMapLink', true ) == 'true' ) : ?>
							<a class="gmap" href="<?php tribe_the_map_link(); ?>" title="Click to view a Google Map" target="_blank"><?php _e('Google Map', $tribe_ecp->pluginDomain ); ?></a>
						<?php endif; ?></td>
						<td class="tec-event-meta-value"><?php tribe_the_full_address( $post->ID ); ?></td>
	                  </tr>
	                  <?php endif; ?>
	                  <?php
	                    $cost = tribe_get_cost();
	                    if ( !empty( $cost ) ) :
	                  ?>
 		              <tr>
						<td class="tec-event-meta-desc"><?php _e('Cost:', $tribe_ecp->pluginDomain) ?></td>
						<td class="tec-event-meta-value"><?php echo $cost; ?></td>
					 </tr>
	                  <?php endif; ?>
	              </table>
				</div>
			</div> <!-- End post -->
	<?php endwhile;// posts ?>
	<?php else :?>
		<?php 
			$tribe_ecp = TribeEvents::instance();
			if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
				$cat = get_term_by( 'slug', get_query_var('term'), $tribe_ecp->get_event_taxonomy() );
				$is_cat_message = sprintf(__(' listed under %s; check out past events for this category or the full calendar',$tribe_ecp->pluginDomain),$cat->name);
			}
		?>

		<?php if(tribe_is_upcoming()){ ?>
			<?php _e('No upcoming events.', $tribe_ecp->pluginDomain);
			echo $is_cat_message ;?>

		<?php }elseif(tribe_is_past()){ ?>
			<?php _e('No previous events.', $tribe_ecp->pluginDomain);
			echo $is_cat_message ?>
		<?php } ?>
		
	<?php endif; ?>


	</div><!-- #tec-events-loop -->
	<div id="tec-nav-below" class="tec-nav clearfix">

		<div class="tec-nav-previous"><?php 
		// Display Previous Page Navigation
		if( tribe_is_upcoming() && get_previous_posts_link() ) : ?>
			<?php previous_posts_link( '<span>'.__('&laquo; Previous Events', $tribe_ecp->pluginDomain).'</span>' ); ?>
		<?php elseif( tribe_is_upcoming() && !get_previous_posts_link( ) ) : ?>
			<a href='<?php echo tribe_get_past_link(); ?>'><span><?php _e('&laquo; Previous Events', $tribe_ecp->pluginDomain ); ?></span></a>
		<?php elseif( tribe_is_past() && get_next_posts_link( ) ) : ?>
			<?php next_posts_link( '<span>'.__('&laquo; Previous Events', $tribe_ecp->pluginDomain).'</span>' ); ?>
		<?php endif; ?>
		</div>

		<div class="tec-nav-next"><?php
		// Display Next Page Navigation
		if( tribe_is_upcoming() && get_next_posts_link( ) ) : ?>
			<?php next_posts_link( '<span>'.__('Next Events &raquo;', $tribe_ecp->pluginDomain).'</span>' ); ?>
		<?php elseif( tribe_is_past() && get_previous_posts_link( ) ) : ?>
			<?php previous_posts_link( '<span>'.__('Next Events &raquo;', $tribe_ecp->pluginDomain).'</span>' ); // a little confusing but in 'past view' to see newer events you want the previous page ?>
		<?php elseif( tribe_is_past() && !get_previous_posts_link( ) ) : ?>
			<a href='<?php echo tribe_get_upcoming_link(); ?>'><span><?php _e('Next Events &raquo;', $tribe_ecp->pluginDomain); ?></span></a>
		<?php endif; ?>
		</div>

	</div>
	<a title="<?php esc_attr_e('iCal Import', $tribe_ecp->pluginDomain) ?>" class="ical" href="<?php echo tribe_get_ical_link(); ?>"><?php _e('iCal Import', $tribe_ecp->pluginDomain) ?></a>
</div>