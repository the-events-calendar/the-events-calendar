<?php
/**
* A single event.  This displays the event title, description, meta, and 
* optionally, the Google map for the event.
*
* You can customize this view by putting a replacement file of the same name (single.php) in the events/ directory of your theme.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<span class="back"><a href="<?php echo tribe_get_events_link(); ?>"><?php _e('&laquo; Back to Events', TribeEvents::PLUGIN_DOMAIN); ?></a></span>				
<?php if (tribe_get_end_date() > time()  ) { ?><small><?php  _e('This event has passed.', TribeEvents::PLUGIN_DOMAIN) ?></small> <?php } ?>
<div id="tribe-events-event-meta" itemscope itemtype="http://schema.org/Event">
	<dl class="column">
		<dt><?php _e('Start:', TribeEvents::PLUGIN_DOMAIN) ?></dt> 
			<dd itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d' ); ?>"><?php echo tribe_get_start_date(); ?></dd>
		<?php if (tribe_get_start_date() !== tribe_get_end_date() ) { ?>
			<dt><?php _e('End:', TribeEvents::PLUGIN_DOMAIN) ?></dt>
			<dd itemprop="endDate" content="<?php echo tribe_get_end_date( null, false, 'Y-m-d' ); ?>"><?php echo tribe_get_end_date();  ?></dd>						
		<?php } ?>
		<?php if ( tribe_get_cost() ) : ?>
			<dt><?php _e('Cost:', TribeEvents::PLUGIN_DOMAIN) ?></dt>
			<dd itemprop="price"><?php echo tribe_get_cost(); ?></dd>
		<?php endif; ?>
		<?php tribe_meta_event_cats(); ?>
		<?php if ( tribe_get_organizer_link() ) : ?>
			<dt><?php _e('Organizer:', TribeEvents::PLUGIN_DOMAIN) ?></dt>
			<dd><?php echo tribe_get_organizer_link(); ?></dd>
		<?php endif; ?>
		<?php if ( tribe_get_organizer_phone() ) : ?>
			<dt><?php _e('Phone:', TribeEvents::PLUGIN_DOMAIN) ?></dt>
			<dd itemprop="telephone"><?php echo tribe_get_organizer_phone(); ?></dd>
		<?php endif; ?>
		<?php if ( tribe_get_organizer_email() ) : ?>
			<dt><?php _e('Email:', TribeEvents::PLUGIN_DOMAIN) ?></dt>
			<dd itemprop="email"><?php echo tribe_get_organizer_email(); ?></dd>
		<?php endif; ?>
		<?php if ( function_exists('tribe_get_recurrence_text') && tribe_is_recurring_event() ) : ?>
			<dt><?php _e('Schedule:', TribeEvents::PLUGIN_DOMAIN) ?></dt>
			<dd><?php echo tribe_get_recurrence_text(); ?> (<a href='<?php tribe_all_occurences_link() ?>'>See all</a>)</dd>
		<?php endif; ?>
	</dl>
	<dl class="column" itemprop="location" itemscope itemtype="http://schema.org/Place">
		<?php if(tribe_get_venue()) : ?>
		<dt><?php _e('Venue:', TribeEvents::PLUGIN_DOMAIN) ?></dt> 
			<dd itemprop="name"><?php echo tribe_get_venue(get_the_ID(), true); ?></dd>
		<?php endif; ?>
		<?php if(tribe_get_phone()) : ?>
		<dt><?php _e('Phone:', TribeEvents::PLUGIN_DOMAIN) ?></dt> 
			<dd itemprop="telephone"><?php echo tribe_get_phone(); ?></dd>
		<?php endif; ?>
		<?php if( tribe_address_exists( get_the_ID() ) ) : ?>
		<dt>
			<?php _e('Address:', TribeEvents::PLUGIN_DOMAIN) ?><br />
			<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
				<a class="gmap" itemprop="maps" href="<?php tribe_the_map_link() ?>" title="<?php _e('Click to view a Google Map', TribeEvents::PLUGIN_DOMAIN); ?>" target="_blank"><?php _e('Google Map', TribeEvents::PLUGIN_DOMAIN ); ?></a>
			<?php endif; ?>
		</dt>
			<dd>
			<?php tribe_the_full_address( get_the_ID() ); ?>
			</dd>
		<?php endif; ?>
	</dl>
  
   	<?php if( function_exists('tribe_the_custom_fields') ): ?>
	  	<?php echo tribe_the_custom_fields( get_the_ID() ); ?>
	<?php endif; ?>
</div>
<?php if( get_post_meta( get_the_ID(), '_EventShowMap', true ) == true ) : ?>
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
<?php if( function_exists('tribe_get_single_ical_link') ): ?>
   <a class="ical single" href="<?php echo tribe_get_single_ical_link(); ?>"><?php _e('iCal Import', TribeEvents::PLUGIN_DOMAIN); ?></a>
<?php endif; ?>
<?php if( function_exists('tribe_get_gcal_link') ): ?>
   <a href="<?php echo tribe_get_gcal_link() ?>" class="gcal-add" title="<?php _e('Add to Google Calendar', TribeEvents::PLUGIN_DOMAIN); ?>"><?php _e('+ Google Calendar', TribeEvents::PLUGIN_DOMAIN); ?></a>
<?php endif; ?>

<div class="navlink previous"><?php tribe_previous_event_link();?></div>

<div class="navlink next"><?php tribe_next_event_link();?></div>
<div style="clear:both"></div>
