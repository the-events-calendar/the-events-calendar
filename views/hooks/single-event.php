<?php

/**
 * The abstracted view of a single event.
 * This view contains the hooks and filters required to create an effective single event view.
 *
 * You can recreate and ENTIRELY new single view (that does not utilize these hooks and filters)
 * by doing a template override, and placing a single-event.php file in a /tribe-events/ directory within your theme directory, which will override the /views/single-event.php.
 */


if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Single_Event_Template')){
	class Tribe_Events_Single_Event_Template extends Tribe_Template_Factory {
		function init(){
			// start single template
			add_filter( 'tribe_events_single_event_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// event title
			add_filter( 'tribe_events_single_event_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_title', array( __CLASS__, 'the_title' ), 1, 2 );
			add_filter( 'tribe_events_single_event_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// event notices
			add_filter( 'tribe_events_single_event_notices', array( __CLASS__, 'notices' ), 1, 2 );

			// event meta
			add_filter( 'tribe_events_single_event_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// event map
			add_filter( 'tribe_events_single_event_the_map', array( __CLASS__, 'the_map' ), 1, 1 );

			// event content
			add_filter( 'tribe_events_single_event_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );

			// event pagination
			add_filter( 'tribe_events_single_event_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// end single template
			apply_filters( 'tribe_events_single_event_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		public function before_template( $post_id ){
			$filter_name = 'tribe_events_single_event_before_template';
			$html = parent::debug( $filter_name );
			$html .= '<span class="back"><a href="' . tribe_get_events_link( $post_id ) . '">' . __('&laquo; Back to Events', 'tribe-events-calendar') . '</a></span>';
			$html .= parent::debug( $filter_name, false );
			return $html;
		}
		public function before_the_title( $post_id ){
			$filter_name = 'tribe_events_single_event_before_the_title';
			$html = parent::debug( $filter_name );
			$html .= parent::debug( $filter_name, false );
			return $html;
		}
		public function the_title( $title, $post_id ){
			$filter_name = 'tribe_events_single_event_the_title';
			$html = parent::debug( $filter_name );
			$html .= $title;
			$html .= parent::debug( $filter_name, false );
			return $html;
		}
		public function after_the_title( $post_id ){
			$filter_name = 'tribe_events_single_event_after_the_title';
			$html = parent::debug( $filter_name );
			$html .= parent::debug( $filter_name, false );
			return $html;
		}
		public function notices( $notices = array(), $post_id ) {
			$filter_name = 'tribe_events_single_event_notices';
			$html = parent::debug( $filter_name );
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div>';
			$html .= parent::debug( $filter_name, false );
			return $html;
		}
		public function before_the_meta( $post_id ){
			$filter_name = 'tribe_events_single_event_before_the_meta';
			$html = parent::debug( $filter_name );
			$html .= parent::debug( $filter_name, false );
			return $html;
		}
		public function the_meta( $post_id ){
			$filter_name = 'tribe_events_single_event_the_meta';
			echo parent::debug( $filter_name );

?><div id="tribe-events-event-meta" itemscope itemtype="http://schema.org/Event">
	<dl class="column">
		<dt class="event-label event-label-name"><?php _e('Event:', 'tribe-events-calendar'); ?></dt>
		<dd itemprop="name" class="event-meta event-meta-name"><span class="summary"><?php echo get_the_title( $post_id ); ?></span></dd>
		<?php if (tribe_get_start_date() !== tribe_get_end_date() ) { ?>
			<dt class="event-label event-label-start"><?php _e('Start:', 'tribe-events-calendar'); ?></dt> 
			<dd class="event-meta event-meta-start"><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d-h:i:s' ); ?>"/><?php echo tribe_get_start_date(); ?></dd>
			<dt class="event-label event-label-end"><?php _e('End:', 'tribe-events-calendar'); ?></dt>
			<dd class="event-meta event-meta-end"><meta itemprop="endDate" content="<?php echo tribe_get_end_date( null, false, 'Y-m-d-h:i:s' ); ?>"/><?php echo tribe_get_end_date(); ?></dd>						
		<?php } else { ?>
			<dt class="event-label event-label-date"><?php _e('Date:', 'tribe-events-calendar'); ?></dt> 
			<dd class="event-meta event-meta-date"><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d-h:i:s' ); ?>"/><?php echo tribe_get_start_date(); ?></dd>
		<?php } ?>
		<?php if ( tribe_get_cost() ) : ?>
			<dt class="event-label event-label-cost"><?php _e('Cost:', 'tribe-events-calendar'); ?></dt>
			<dd itemprop="price" class="event-meta event-meta-cost"><?php echo tribe_get_cost(); ?></dd>
		<?php endif; ?>
		<?php tribe_meta_event_cats(); ?>
		<?php if ( tribe_get_organizer_link( get_the_ID(), false, false ) ) : ?>
			<dt class="event-label event-label-organizer"><?php _e('Organizer:', 'tribe-events-calendar'); ?></dt>
			<dd class="vcard author event-meta event-meta-author"><span class="fn url"><?php echo tribe_get_organizer_link(); ?></span></dd>
      <?php elseif (tribe_get_organizer()): ?>
			<dt class="event-label event-label-organizer"><?php _e('Organizer:', 'tribe-events-calendar'); ?></dt>
			<dd class="vcard author event-meta event-meta-author"><span class="fn url"><?php echo tribe_get_organizer(); ?></span></dd>
		<?php endif; ?>
		<?php if ( tribe_get_organizer_phone() ) : ?>
			<dt class="event-label event-label-organizer-phone"><?php _e('Phone:', 'tribe-events-calendar'); ?></dt>
			<dd itemprop="telephone" class="event-meta event-meta-phone"><?php echo tribe_get_organizer_phone(); ?></dd>
		<?php endif; ?>
		<?php if ( tribe_get_organizer_email() ) : ?>
			<dt class="event-label event-label-email"><?php _e('Email:', 'tribe-events-calendar'); ?></dt>
			<dd itemprop="email" class="event-meta event-meta-email"><a href="mailto:<?php echo tribe_get_organizer_email(); ?>"><?php echo tribe_get_organizer_email(); ?></a></dd>
		<?php endif; ?>
		<dt class="event-label event-label-updated"><?php _e('Updated:', 'tribe-events-calendar'); ?></dt>
		<dd class="event-meta event-meta-updated"><span class="date updated"><?php the_date(); ?></span></dd>
		<?php if ( class_exists('TribeEventsRecurrenceMeta') && function_exists('tribe_get_recurrence_text') && tribe_is_recurring_event() ) : ?>
			<dt class="event-label event-label-schedule"><?php _e('Schedule:', 'tribe-events-calendar'); ?></dt>
         <dd class="event-meta event-meta-schedule"><?php echo tribe_get_recurrence_text(); ?> 
            <?php if( class_exists('TribeEventsRecurrenceMeta') && function_exists('tribe_all_occurences_link')): ?>(<a href='<?php tribe_all_occurences_link(); ?>'>See all</a>)<?php endif; ?>
         </dd>
		<?php endif; ?>
	</dl>
	<dl class="column" itemprop="location" itemscope itemtype="http://schema.org/Place">
		<?php if(tribe_get_venue()) : ?>
		<dt class="event-label event-label-venue"><?php _e('Venue:', 'tribe-events-calendar'); ?></dt> 
		<dd itemprop="name" class="event-meta event-meta-venue">
			<?php if( class_exists( 'TribeEventsPro' ) ): ?>
				<?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
			<?php else: ?>
				<?php echo tribe_get_venue( get_the_ID() ); ?>
			<?php endif; ?>
		</dd>
		<?php endif; ?>
		<?php if(tribe_get_phone()) : ?>
		<dt class="event-label event-label-venue-phone"><?php _e('Phone:', 'tribe-events-calendar'); ?></dt> 
			<dd itemprop="telephone" class="event-meta event-meta-venue-phone"><?php echo tribe_get_phone(); ?></dd>
		<?php endif; ?>
		<?php if( tribe_address_exists( get_the_ID() ) ) : ?>
		<dt class="event-label event-label-address">
			<?php _e('Address:', 'tribe-events-calendar') ?><br />
			<?php if( tribe_show_google_map_link( get_the_ID() ) ) : ?>
				<a class="gmap" itemprop="maps" href="<?php echo tribe_get_map_link(); ?>" title="<?php _e('Click to view a Google Map', 'tribe-events-calendar'); ?>" target="_blank"><?php _e('Google Map', 'tribe-events-calendar' ); ?></a>
			<?php endif; ?>
		</dt>
			<dd class="event-meta event-meta-address">
			<?php echo tribe_get_full_address( get_the_ID() ); ?>
			</dd>
		<?php endif; ?>
	</dl>
  
   	<?php if( function_exists('tribe_the_custom_fields') && tribe_get_custom_fields( get_the_ID() ) ): ?>
	  	<?php tribe_the_custom_fields( get_the_ID() ); ?>
	<?php endif; ?>
</div><?php


			echo parent::debug( $filter_name, false );
		}
		public function after_the_meta( $post_id ){
			$filter_name = 'tribe_events_single_event_after_the_meta';
			echo parent::debug( $filter_name );
			echo parent::debug( $filter_name, false );
		}
		public function the_map( $post_id ){
			$filter_name = 'tribe_events_single_event_before_the_map';
			echo parent::debug( $filter_name );
			if( tribe_embed_google_map( $post_id ) &&  tribe_address_exists( $post_id ) ) 
				echo tribe_get_embedded_map();
			echo parent::debug( $filter_name, false );
		}
		public function before_the_content( $post_id ){
			$filter_name = 'tribe_events_single_event_before_the_content';
			echo parent::debug( $filter_name );
			echo '<div class="entry">';
			echo parent::debug( $filter_name, false );
		}
		public function the_content( $post_id ){
			$filter_name = 'tribe_events_single_event_the_content';
			echo parent::debug( $filter_name );
			if ( function_exists('has_post_thumbnail') && has_post_thumbnail() )
				the_post_thumbnail();
			echo '<div class="summary">';
					the_content();
			echo '</div>';
			if (function_exists('tribe_get_ticket_form') && tribe_get_ticket_form()) 
				tribe_get_ticket_form();
			echo parent::debug( $filter_name, false );
		}
		public function after_the_content( $post_id ){
			$filter_name = 'tribe_events_single_event_after_the_content';
			echo parent::debug( $filter_name );
			echo '</div>';
			if( function_exists('tribe_get_single_ical_link') )
				echo '<a class="ical single" href="' . tribe_get_single_ical_link() . '">' . __('iCal Import', 'tribe-events-calendar') . '</a>';
			if( function_exists('tribe_get_gcal_link') )
				echo '<a href="' . tribe_get_gcal_link() . '" class="gcal-add" title="' . __('Add to Google Calendar', 'tribe-events-calendar') . '">' . __('+ Google Calendar', 'tribe-events-calendar') . '</a>';
			echo parent::debug( $filter_name, false );
		}
		public function before_pagination( $post_id){
			$filter_name = 'tribe_events_single_event_before_pagination';
			echo parent::debug( $filter_name );
			echo parent::debug( $filter_name, false );
		}
		public function pagination( $post_id ){
			$filter_name = 'tribe_events_single_event_pagination';
			echo parent::debug( $filter_name );
			echo '<div class="navlink tribe-previous">';
					tribe_previous_event_link();
			echo '</div><div class="navlink tribe-next">';
					tribe_next_event_link();
			echo '</div>';
			echo parent::debug( $filter_name, false );
		}
		public function after_pagination( $post_id ){
			$filter_name = 'tribe_events_single_event_after_pagination';
			echo parent::debug( $filter_name );
			echo parent::debug( $filter_name, false );
		}
		public function after_template( $post_id ){
			$filter_name = 'tribe_events_single_event_after_template';
			echo parent::debug( $filter_name );
			echo '<div style="clear:both"></div>';
			echo parent::debug( $filter_name, false );
		}
	}
	Tribe_Events_Single_Event_Template::init();
}