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
<span class="back"><a href="<?php echo tribe_get_events_link(); ?>"><?php _e('&laquo; Back to Events', 'tribe-events-calendar'); ?></a></span>				
<?php if (tribe_get_end_date() > time()  ) { ?><small><?php  _e('This event has passed.', 'tribe-events-calendar') ?></small> <?php } ?>
<div id="tribe-events-event-meta" itemscope itemtype="http://schema.org/Event">
	<dl class="column">
		<dt><?php _e('Event:', 'tribe-events-calendar') ?></dt>
		<dd itemprop="name"><span class="summary"><?php the_title() ?></span></dd>
		<?php if (tribe_get_start_date() !== tribe_get_end_date() ) { ?>
			<dt><?php _e('Start:', 'tribe-events-calendar') ?></dt> 
			<dd><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d-h:i:s' ); ?>"/><?php echo tribe_get_start_date(); ?></dd>
			<dt><?php _e('End:', 'tribe-events-calendar') ?></dt>
			<dd><meta itemprop="endDate" content="<?php echo tribe_get_end_date( null, false, 'Y-m-d-h:i:s' ); ?>"/><?php echo tribe_get_end_date();  ?></dd>						
		<?php } else { ?>
			<dt><?php _e('Date:', 'tribe-events-calendar') ?></dt> 
			<dd><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d-h:i:s' ); ?>"/><?php echo tribe_get_start_date(); ?></dd>
		<?php } ?>
		<?php if ( tribe_get_cost() ) : ?>
			<dt><?php _e('Cost:', 'tribe-events-calendar') ?></dt>
			<dd itemprop="price"><?php echo tribe_get_cost(); ?></dd>
		<?php endif; ?>
		<?php tribe_meta_event_cats(); ?>
		<?php if ( tribe_get_organizer_link( false, false ) ) : ?>
			<dt><?php _e('Organizer:', 'tribe-events-calendar') ?></dt>
			<dd class="vcard author"><span class="fn url"><?php echo tribe_get_organizer_link(); ?></span></dd>
		<?php endif; ?>
		<?php if ( tribe_get_organizer_phone() ) : ?>
			<dt><?php _e('Phone:', 'tribe-events-calendar') ?></dt>
			<dd itemprop="telephone"><?php echo tribe_get_organizer_phone(); ?></dd>
		<?php endif; ?>
		<?php if ( tribe_get_organizer_email() ) : ?>
			<dt><?php _e('Email:', 'tribe-events-calendar') ?></dt>
			<dd itemprop="email"><a href="mailto:<?php echo tribe_get_organizer_email(); ?>"><?php echo tribe_get_organizer_email(); ?></a></dd>
		<?php endif; ?>
		<dt><?php _e('Updated:', 'tribe-events-calendar') ?></dt>
		<dd><span class="date updated"><?php the_date(); ?></span></dd>
		<?php if ( function_exists('tribe_get_recurrence_text') && tribe_is_recurring_event() ) : ?>
			<dt><?php _e('Schedule:', 'tribe-events-calendar') ?></dt>
			<dd><?php echo tribe_get_recurrence_text(); ?> (<a href='<?php tribe_all_occurences_link() ?>'>See all</a>)</dd>
		<?php endif; ?>
	</dl>
	<dl class="column" itemprop="location" itemscope itemtype="http://schema.org/Place">
		<?php if(tribe_get_venue()) : ?>
		<dt><?php _e('Venue:', 'tribe-events-calendar') ?></dt> 
		<dd itemprop="name">
			<? if( class_exists( 'TribeEventsPro' ) ): ?>
				<?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
			<? else: ?>
				<?php echo tribe_get_venue( get_the_ID() ) ?>
			<? endif; ?>
		</dd>
		<?php endif; ?>
		<?php if(tribe_get_phone()) : ?>
		<dt><?php _e('Phone:', 'tribe-events-calendar') ?></dt> 
			<dd itemprop="telephone"><?php echo tribe_get_phone(); ?></dd>
		<?php endif; ?>
		<?php if( tribe_address_exists( get_the_ID() ) ) : ?>
		<dt>
			<?php _e('Address:', 'tribe-events-calendar') ?><br />
			<?php if( tribe_show_google_map_link( get_the_ID() ) ) : ?>
				<a class="gmap" itemprop="maps" href="<?php echo tribe_get_map_link() ?>" title="<?php _e('Click to view a Google Map', 'tribe-events-calendar'); ?>" target="_blank"><?php _e('Google Map', 'tribe-events-calendar' ); ?></a>
			<?php endif; ?>
		</dt>
			<dd>
			<?php echo tribe_get_full_address( get_the_ID() ); ?>
			</dd>
		<?php endif; ?>
	</dl>
  
   	<?php if( function_exists('tribe_the_custom_fields') ): ?>
	  	<?php echo tribe_the_custom_fields( get_the_ID() ); ?>
	<?php endif; ?>
</div>
<?php if( tribe_embed_google_map( get_the_ID() ) ) : ?>
<?php if( tribe_address_exists( get_the_ID() ) ) { echo tribe_get_embedded_map(); } ?>
<?php endif; ?>
<div class="entry">
	<?php
	if ( function_exists('has_post_thumbnail') && has_post_thumbnail() ) {?>
		<?php the_post_thumbnail(); ?>
	<?php } ?>
	<div class="summary"><?php the_content() ?></div>
	<?php if (function_exists('tribe_get_ticket_form')) { tribe_get_ticket_form(); } ?>		
</div>
<?php if( function_exists('tribe_get_single_ical_link') ): ?>
   <a class="ical single" href="<?php echo tribe_get_single_ical_link(); ?>"><?php _e('iCal Import', 'tribe-events-calendar'); ?></a>
<?php endif; ?>
<?php if( function_exists('tribe_get_gcal_link') ): ?>
   <a href="<?php echo tribe_get_gcal_link() ?>" class="gcal-add" title="<?php _e('Add to Google Calendar', 'tribe-events-calendar'); ?>"><?php _e('+ Google Calendar', 'tribe-events-calendar'); ?></a>
<?php endif; ?>

<div class="navlink previous"><?php tribe_previous_event_link();?></div>

<div class="navlink next"><?php tribe_next_event_link();?></div>
<div style="clear:both"></div>
