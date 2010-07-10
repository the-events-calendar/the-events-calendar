<?php
	global $spEvents;
	get_header();
?>	
	<div id="container">
	<div id="content" class="tec-event widecolumn">
	<?php the_post(); global $post; ?>
			<div id="post-<?php the_ID() ?>" <?php post_class() ?>>
				<span class="back"><a href="<?php echo sp_get_events_link(); ?>"><?php _e('&laquo; Back to Events', $spEvents->pluginDomain); ?></a></span>
				<h2 class="entry-title"><?php the_title() ?></h2>
				<?php if (sp_get_end_date() > time()  ) { ?><small><?php  _e('This event has passed.', $spEvents->pluginDomain) ?></small> <?php } ?>
				<div id="tec-event-meta">
					<dl class="column">
						<dt><?php _e('Start:', $spEvents->pluginDomain) ?></dt> 
							<dd><?php echo sp_get_start_date(); ?></dd>
						<?php if (sp_get_start_date() !== sp_get_end_date() ) { ?>
							<dt><?php _e('End:', $spEvents->pluginDomain) ?></dt>
							<dd><?php echo sp_get_end_date();  ?></dd>						
						<?php } ?>
						<?php if ( sp_get_cost() ) : ?>
							<dt><?php _e('Cost:', $spEvents->pluginDomain) ?></dt>
							<dd><?php echo sp_get_cost(); ?></dd>
						<?php endif; ?>
					</dl>
					<dl class="column">
						<?php if(sp_get_venue()) : ?>
						<dt><?php _e('Venue:', $spEvents->pluginDomain) ?></dt> 
							<dd><?php echo sp_get_venue(); ?></dd>
						<?php endif; ?>
						<?php if(sp_get_phone()) : ?>
						<dt><?php _e('Phone:', $spEvents->pluginDomain) ?></dt> 
							<dd><?php echo sp_get_phone(); ?></dd>
						<?php endif; ?>
						<?php if( sp_address_exists( $post->ID ) ) : ?>
						<dt>
							<?php _e('Address:', $spEvents->pluginDomain) ?><br />
							<?php if( get_post_meta( $post->ID, '_EventShowMapLink', true ) == 'true' ) : ?>
								<a class="gmap" href="<?php sp_the_map_link() ?>" title="<?php _e('Click to view a Google Map', $spEvents->pluginDomain); ?>" target="_blank"><?php _e('Google Map', $spEvents->pluginDomain ); ?></a>
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
					<?php the_content() ?>	
					<?php if (function_exists('sp_get_ticket_form')) { sp_get_ticket_form(); } ?>		
				</div>
				<a class="ical single" href="<?php echo sp_get_single_ical_link(); ?>"><?php _e('iCal Import', $spEvents->pluginDomain); ?></a>
				<?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
			</div><!-- post -->

		<?php if(sp_get_option('showComments','no') == 'yes'){ comments_template();} ?>

	</div><!-- #content -->
	</div><!--#container-->
<?php get_sidebar(); ?>	
<?php
	get_footer();