				<span class="back"><a href="<?php echo tribe_get_events_link(); ?>"><?php _e('&laquo; Back to Events', $tribe_ecp->pluginDomain); ?></a></span>								
				<div id="tec-event-meta">
					<div style='margin: 0 0 10px 0; float: right;'>
						<?php echo tribe_venue_get_embedded_map(get_the_ID(), '350px', '200px') ?>
					</div>					
					<dl class="column">
						<dt><?php _e('Name:', $tribe_ecp->pluginDomain) ?></dt> 
							<dd><?php the_title() ?></dd>
						<?php if(tribe_venue_get_phone()) : ?>
						<dt><?php _e('Phone:', $tribe_ecp->pluginDomain) ?></dt> 
							<dd><?php echo tribe_venue_get_phone(); ?></dd>
						<?php endif; ?>
						<?php if( tribe_venue_address_exists( get_the_ID() ) ) : ?>
						<dt>
							<?php _e('Address:', $tribe_ecp->pluginDomain) ?><br />
							<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
								<a class="gmap" href="<?php tribe_the_map_link() ?>" title="<?php _e('Click to view a Google Map', $tribe_ecp->pluginDomain); ?>" target="_blank"><?php _e('Google Map', $tribe_ecp->pluginDomain ); ?></a>
							<?php endif; ?>
						</dt>
							<dd>
							<?php tribe_venue_the_full_address( get_the_ID() ); ?>
							</dd>
						<?php endif; ?>
					</dl>
				</div>
				<div id="tec-events-loop" class="tec-events post-list clearfix upcoming venue-events">
					<?php $venueEvents =tribe_get_events(array('post_type'=>Events_Calendar_Pro::POSTTYPE, 'venue'=>get_the_ID(), 'posts_per_page'=> -1)); global $post; $first = true?>					
					<?php if( sizeof($venueEvents) > 0 ): ?>
						<h2 class='tec-cal-title'>Upcoming Events At This Venue</h2>					
						<?php foreach( $venueEvents as $post ): setup_postdata($post);  ?>
							<div id="post-<?php the_ID() ?>" <?php post_class($first ? 'tec-event clearfix first': 'tec-event clearfix' ); $first = false; ?>>
								<?php if ( sp_is_new_event_day() ) : ?>
									<h4 class="event-day"><?php echo sp_get_start_date( null, false ); ?></h4>
								<?php endif; ?>
								<?php the_title('<h2 class="entry-title"><a href="' . get_permalink() . '" title="' . the_title_attribute('echo=0') . '" rel="bookmark">', '</a></h2>'); ?>
								<div class="entry-content tec-event-entry">
									<?php has_excerpt() ? the_excerpt() : the_content() ?>
								</div> <!-- End tec-event-entry -->
								<div class="tec-event-list-meta">
									  <table cellspacing="0">
											<tr>
											  <td class="tec-event-meta-desc"><?php _e('Start:', $sp_ecp->pluginDomain) ?></td>
											  <td class="tec-event-meta-value"><?php echo sp_get_start_date(); ?></td>
											</tr>
											<tr>
											  <td class="tec-event-meta-desc"><?php _e('End:', $sp_ecp->pluginDomain) ?></td>
											  <td class="tec-event-meta-value"><?php echo sp_get_end_date(); ?></td>
											</tr>
											<?php
											  $cost = sp_get_cost();
											  if ( !empty( $cost ) ) :
											?>
												<tr>
													<td class="tec-event-meta-desc"><?php _e('Cost:', $sp_ecp->pluginDomain) ?></td>
													<td class="tec-event-meta-value"><?php echo $cost; ?></td>
												</tr>
											<?php endif; ?>
									  </table>
								</div>
							</div> <!-- End post -->				
						<?php endforeach; ?>						
					<?php endif; ?>
				</div>
