<?php
	global $sp_ecp;
	get_header();
	echo stripslashes(sp_get_option('spEventsBeforeHTML'));
?>	
	<div id="container">
	<div id="content" class="tec-event widecolumn">
	<?php the_post(); global $post; ?>
			<div id="post-<?php the_ID() ?>" <?php post_class() ?>>
				<span class="back"><a href="<?php echo sp_get_events_link(); ?>"><?php _e('&laquo; Back to Events', $sp_ecp->pluginDomain); ?></a></span>
				<h2 class="entry-title"><?php the_title() ?></h2>
				<?php if (sp_get_end_date() > time()  ) { ?><small><?php  _e('This event has passed.', $sp_ecp->pluginDomain) ?></small> <?php } ?>
				<div id="tec-event-meta">
					<dl class="column">
						<dt><?php _e('Start:', $sp_ecp->pluginDomain) ?></dt> 
							<dd><?php echo sp_get_start_date(); ?></dd>
						<?php if (sp_get_start_date() !== sp_get_end_date() ) { ?>
							<dt><?php _e('End:', $sp_ecp->pluginDomain) ?></dt>
							<dd><?php echo sp_get_end_date();  ?></dd>						
						<?php } ?>
						<?php if ( sp_get_cost() ) : ?>
							<dt><?php _e('Cost:', $sp_ecp->pluginDomain) ?></dt>
							<dd><?php echo sp_get_cost(); ?></dd>
						<?php endif; ?>
						<?php sp_meta_event_cats(); ?>
					</dl>
					<dl class="column">
						<?php if(sp_get_venue()) : ?>
						<dt><?php _e('Venue:', $sp_ecp->pluginDomain) ?></dt> 
							<dd><?php echo sp_get_venue(); ?></dd>
						<?php endif; ?>
						<?php if(sp_get_phone()) : ?>
						<dt><?php _e('Phone:', $sp_ecp->pluginDomain) ?></dt> 
							<dd><?php echo sp_get_phone(); ?></dd>
						<?php endif; ?>
						<?php if( sp_address_exists( $post->ID ) ) : ?>
						<dt>
							<?php _e('Address:', $sp_ecp->pluginDomain) ?><br />
							<?php if( get_post_meta( $post->ID, '_EventShowMapLink', true ) == 'true' ) : ?>
								<a class="gmap" href="<?php sp_the_map_link() ?>" title="<?php _e('Click to view a Google Map', $sp_ecp->pluginDomain); ?>" target="_blank"><?php _e('Google Map', $sp_ecp->pluginDomain ); ?></a>
							<?php endif; ?>
						</dt>
							<dd>
							<?php sp_the_full_address( $post->ID ); ?>
							</dd>
						<?php endif; ?>
					</dl>
				</div>
				<?php if( get_post_meta( $post->ID, '_EventShowMap', true ) == 'true' ) : ?>
					<?php if( sp_address_exists( $post->ID ) ) sp_the_embedded_map(); ?>
				<?php endif; ?>
				<div class="entry">
					<?php
					if ( function_exists('has_post_thumbnail') && has_post_thumbnail() ) {?>
						<?php the_post_thumbnail(); ?>
					<?php } ?>
					<?php the_content() ?>	
					<?php if (function_exists('sp_get_ticket_form')) { sp_get_ticket_form(); } ?>		
				</div>
				<a class="ical single" href="<?php echo sp_get_single_ical_link(); ?>"><?php _e('iCal Import', $sp_ecp->pluginDomain); ?></a>
				<a href="<?php echo sp_get_add_to_gcal_link() ?>" class="gcal-add" title="<?php _e('Add to Google Calendar', $sp_ecp->pluginDomain); ?>"><?php _e('+ Google Calendar', $sp_ecp->pluginDomain); ?></a>
				<?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
			</div><!-- post -->

		<?php if(sp_get_option('showComments','no') == 'yes'){ comments_template();} ?>

	</div><!-- #content -->
	</div><!--#container-->
<?php get_sidebar(); ?>	
<?php
	echo stripslashes(sp_get_option('spEventsAfterHTML'));
	get_footer();