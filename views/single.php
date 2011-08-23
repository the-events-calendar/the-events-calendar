<?php
/**
* Single event template
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<span class="back"><a href="<?php echo tribe_get_events_link(); ?>"><?php _e('&laquo; Back to Events', $tribe_ecp->pluginDomain); ?></a></span>				
<?php if (tribe_get_end_date() > time()  ) { ?><small><?php  _e('This event has passed.', $tribe_ecp->pluginDomain) ?></small> <?php } ?>
<div id="tribe-events-event-meta">
	<dl class="column">
		<dt><?php _e('Start:', $tribe_ecp->pluginDomain) ?></dt> 
			<dd><?php echo tribe_get_start_date(); ?></dd>
		<?php if (tribe_get_start_date() !== tribe_get_end_date() ) { ?>
			<dt><?php _e('End:', $tribe_ecp->pluginDomain) ?></dt>
			<dd><?php echo tribe_get_end_date();  ?></dd>						
		<?php } ?>
		<?php if ( tribe_get_cost() ) : ?>
			<dt><?php _e('Cost:', $tribe_ecp->pluginDomain) ?></dt>
			<dd><?php echo tribe_get_cost(); ?></dd>
		<?php endif; ?>
		<?php tribe_meta_event_cats(); ?>
		<?php if ( tribe_get_organizer_link() ) : ?>
			<dt><?php _e('Organizer:', $tribe_ecp->pluginDomain) ?></dt>
			<dd><?php echo tribe_get_organizer_link(); ?></dd>
		<?php endif; ?>
		<?php if ( tribe_get_organizer_phone() ) : ?>
			<dt><?php _e('Phone:', $tribe_ecp->pluginDomain) ?></dt>
			<dd><?php echo tribe_get_organizer_phone(); ?></dd>
		<?php endif; ?>
		<?php if ( tribe_get_organizer_email() ) : ?>
			<dt><?php _e('Email:', $tribe_ecp->pluginDomain) ?></dt>
			<dd><?php echo tribe_get_organizer_email(); ?></dd>
		<?php endif; ?>
		<?php if ( function_exists('tribe_get_recurrence_text') && tribe_is_recurring_event() ) : ?>
			<dt><?php _e('Schedule:', $tribe_ecp->pluginDomain) ?></dt>
			<dd><?php echo tribe_get_recurrence_text(); ?> (<a href='<?php tribe_all_occurences_link() ?>'>See all</a>)</dd>
		<?php endif; ?>
	</dl>
	<dl class="column">
		<?php if(tribe_get_venue()) : ?>
		<dt><?php _e('Venue:', $tribe_ecp->pluginDomain) ?></dt> 
			<dd><?php echo tribe_get_venue(get_the_ID(), true); ?></dd>
		<?php endif; ?>
		<?php if(tribe_get_phone()) : ?>
		<dt><?php _e('Phone:', $tribe_ecp->pluginDomain) ?></dt> 
			<dd><?php echo tribe_get_phone(); ?></dd>
		<?php endif; ?>
		<?php if( tribe_address_exists( get_the_ID() ) ) : ?>
		<dt>
			<?php _e('Address:', $tribe_ecp->pluginDomain) ?><br />
			<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
				<a class="gmap" href="<?php tribe_the_map_link() ?>" title="<?php _e('Click to view a Google Map', $tribe_ecp->pluginDomain); ?>" target="_blank"><?php _e('Google Map', $tribe_ecp->pluginDomain ); ?></a>
			<?php endif; ?>
		</dt>
			<dd>
			<?php tribe_the_full_address( get_the_ID() ); ?>
			</dd>
		<?php endif; ?>
	</dl>
  
   	<?php if( function_exists('tribe_event_meta') ): ?>
              <dl class='column'>
              	<?php echo tribe_event_meta( get_the_ID() ); ?>
             	</dl>
          <?php endif; ?>
</div>

<?php if( get_post_meta( get_the_ID(), '_EventShowMap', true ) == 'true' ) : ?>
	<?php if( tribe_address_exists( get_the_ID() ) ) tribe_the_embedded_map(); ?>
<?php endif; ?>
<div class="entry">
	<?php
	if ( function_exists('has_post_thumbnail') && has_post_thumbnail() ) {?>
		<?php the_post_thumbnail(); ?>
	<?php } ?>
	<?php the_content() ?>	
	<?php if (function_exists('tribe_get_ticket_form')) { tribe_get_ticket_form(); } ?>		
</div>
<a class="ical single" href="<?php echo tribe_get_single_ical_link(); ?>"><?php _e('iCal Import', $tribe_ecp->pluginDomain); ?></a>
<a href="<?php echo tribe_get_add_to_gcal_link() ?>" class="gcal-add" title="<?php _e('Add to Google Calendar', $tribe_ecp->pluginDomain); ?>"><?php _e('+ Google Calendar', $tribe_ecp->pluginDomain); ?></a>
<div class="navlink previous"><?php tribe_previous_event_link();?></div>

<div class="navlink next"><?php tribe_next_event_link();?></div>
<div style="clear:both"></div>