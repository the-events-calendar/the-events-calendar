<?php
	global $spEvents;
	$spEvents->loadDomainStylesScripts();
	get_header();
?>	
	<div id="tec-content" class="tec-event widecolumn">
	<?php the_post(); global $post; ?>
			<div id="post-<?php the_ID() ?>" <?php post_class() ?>>
				<span class="back"><a href="<?php echo events_get_events_link(); ?>"><?php _e('&laquo; Back to Events', $spEvents->pluginDomain); ?></a></span>
				<h2 class="entry-title"><?php the_title() ?></h2>
				<?php if (the_event_end_date() > time()  ) { ?><small><?php  _e('This event has passed.', $spEvents->pluginDomain) ?></small> <?php } ?>
				<div id="tec-event-meta">
					<dl class="column">
						<dt><?php _e('Start:', $spEvents->pluginDomain) ?></dt> 
							<dd><?php echo the_event_start_date(); ?></dd>
						<?php if (the_event_start_date() !== the_event_end_date() ) { ?>
							<dt><?php _e('End:', $spEvents->pluginDomain) ?></dt>
							<dd><?php echo the_event_end_date();  ?></dd>						
						<?php } ?>
						<?php if ( the_event_cost() ) : ?>
							<dt><?php _e('Cost:', $spEvents->pluginDomain) ?></dt>
							<dd><?php echo the_event_cost(); ?></dd>
						<?php endif; ?>
					</dl>
					<dl class="column">
						<?php if(the_event_venue()) : ?>
						<dt><?php _e('Venue:', $spEvents->pluginDomain) ?></dt> 
							<dd><?php echo the_event_venue(); ?></dd>
						<?php endif; ?>
						<?php if(the_event_phone()) : ?>
						<dt><?php _e('Phone:', $spEvents->pluginDomain) ?></dt> 
							<dd><?php echo the_event_phone(); ?></dd>
						<?php endif; ?>
						<?php if( tec_address_exists( $post->ID ) ) : ?>
						<dt>
							<?php _e('Address:', $spEvents->pluginDomain) ?><br />
							<?php if( get_post_meta( $post->ID, '_EventShowMapLink', true ) == 'true' ) : ?>
								<a class="gmap" href="<?php event_google_map_link() ?>" title="<?php _e('Click to view a Google Map', $spEvents->pluginDomain); ?>" target="_blank"><?php _e('Google Map', $spEvents->pluginDomain ); ?></a>
							<?php endif; ?>
						</dt>
							<dd>
							<?php tec_event_address( $post->ID ); ?>
							</dd>
						<?php endif; ?>
					</dl>
				</div>
				<?php if( get_post_meta( $post->ID, '_EventShowMap', true ) == 'true' ) : ?>
					<?php if( tec_address_exists( $post->ID ) ) event_google_map_embed(); ?>
				<?php endif; ?>
				<div class="entry">
					<?php the_content() ?>	
					<?php if (function_exists('the_event_ticket_form')) { the_event_ticket_form(); } ?>		
				</div>
				<?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
			</div><!-- post -->

		<?php if(eventsGetOptionValue('showComments','no') == 'yes'){ comments_template();} ?>

	</div><!-- tec-content -->
	
<?php
	get_footer();