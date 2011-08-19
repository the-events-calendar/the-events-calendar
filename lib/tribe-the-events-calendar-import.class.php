<?php
/**
 * Import functionality from the old open source plugin - The Events Calendar
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('TribeEventsImport')) {
	class TribeEventsImport {

		/* Static Singleton Factory Method */
		private static $instance;
		public static function instance() {
			if (!isset(self::$instance)) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			return self::$instance;
		}
		
		public static $curVenues = array();
		public static $legacyVenueTags = array(
			'_EventVenue',
			'_EventCountry',
			'_EventAddress',
			'_EventCity',
			'_EventZip',
			'_EventPhone',
		);
		
		private function __construct( ) {
			add_action( 'admin_init', array( $this, 'upgradeData' ) );
			add_action( 'tribe_events_options_post_form', array( $this, 'adminForm' ) );
		}
		
		public function adminForm() {
			if ( self::hasLegacyEvents() ) {
				$old_events_copy = '<p class="message">' . sprintf( __('It looks like you have %s events in the category "%s". Click below to import!', TribeEvents::PLUGIN_DOMAIN ), $old_events, self::CATEGORYNAME ) . '</p>'; ?>

				<form id="sp-upgrade" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<?php wp_nonce_field('upgradeEventsCalendar') ?>
					<h4><?php _e('Upgrade from The Events Calendar', TribeEvents::PLUGIN_DOMAIN ); ?></h4>
					<p><?php _e('We built a vibrant community around our free <a href="http://wordpress.org/extend/plugins/the-events-calendar/" target="_blank">The Events Calendar</a> plugin. If you used the free version and are now using our premium version, thanks, we\'re glad to have you here!', TribeEvents::PLUGIN_DOMAIN ) ?></p>
					<?php echo $old_events_copy; ?>
					<input type="submit" value="<?php _e('Migrate Data!', TribeEvents::PLUGIN_DOMAIN); ?>" class="button-secondary" name="upgradeEventsCalendar" />
				</form>	
				<?php 
			}
		}
		
		/**
		 * Will upgrade data from old free plugin to pro plugin
		 */
		public static function upgradeData() {			
			if ( isset($_POST['upgradeEventsCalendar']) && check_admin_referer('upgradeEventsCalendar') ) {
				
				/*
				
				TODO: migrate the following:
				
				* option "sp_events_calendar_options" needs to be renamed as TribeEvents::OPTIONNAME
				* posts of type "sp_events" need to be moved to TribeEvents::POSTTYPE
				* posts of type "sp_venue" need to be moved to TribeEvents::VENUE_POST_TYPE
				* posts of type "sp_organizer" need to be moved to TribeEvents::ORGANIZER_POST_TYPE
				* categories of type "sp_events_cat" need to be moved to TribeEvents::TAXONOMY
				* options that saved using on/enabled/yes need to be set to true and off/disabled/no being false
			
				*/
				
				$posts = self::getLegacyEvents();

				// we don't want the old event category
				$eventCat = get_term_by('name', 'Events', 'category' );
				// existing event cats
				$existingEventCats = (array) get_terms(TribeEvents::TAXONOMY, array('fields' => 'names'));
				// store potential new event cats;
				$newEventCats = array();

				// first create log needed new event categories
				foreach ($posts as $key => $post) {
					// we don't want the old Events category
					$cats = self::removeEventCat( get_the_category($post->ID), $eventCat );
					// see what new ones we need
					$newEventCats = self::mergeCatList( $cats, $newEventCats );
					// store on the $post to keep from re-querying
					$posts[$key]->cats = self::getCatNames( $cats );
				}
				// dedupe
				$newEventCats = array_unique($newEventCats);

				// let's create new events cats
				foreach ( $newEventCats as $cat ) {
					// leave alone existing ones
					if ( in_array( $cat, $existingEventCats ) )
						continue;

					// make 'em!
					wp_insert_term( $cat, TribeEvents::TAXONOMY );
				}
				// now we know what we're in for
				$masterCats = get_terms( TribeEvents::TAXONOMY, array('hide_empty'=>false) );

				// let's convert those posts
				foreach ( $posts as $post ) {
					// new post_type sir
					set_post_type( $post->ID, TribeEvents::POSTTYPE );
					// set new events cats. we stored the array above, remember?
					if ( ! empty($post->cats) )
						wp_set_object_terms( $post->ID, $post->cats, TribeEvents::TAXONOMY );

					self::convertVenue($post);
				}
			}
		}

		/**
		 * Test for legacy events
		 *
		 * @return boolean for legacy events
		 */
		public static function hasLegacyEvents() {
			return count( self::getLegacyEvents( 1 ) );
		}

		/**
		 * Convert Venue data
		 *
		 * @param string $post 
		 */
		private static function convertVenue($post) {
			$venue = array();
		
			foreach( self::$legacyVenueTags as $tag) {
				$strippedTag = str_replace('_Event','',$tag);
				$meta = get_post_meta($post->ID, $tag, true);
				$venue[$strippedTag] = $meta;
				delete_post_meta($post->ID, $tag);
			}
		
			if( $venue['Country'] == 'United States') {
				$venue['StateProvince'] = get_post_meta($post->ID, '_EventState', true);
			} else {
				$venue['StateProvince'] = get_post_meta($post->ID, '_EventProvince', true);
			}
		
			$unique_venue = $venue['Venue'] . $venue['Address'] . $venue['StateProvince'];

			if( $unique_venue && trim($unique_venue) != "" ) {
				if( !self::$curVenues[$unique_venue] ) {
					self::$curVenues[$unique_venue] = Tribe_Event_API::createVenue($venue);
				} else {
					Tribe_Event_API::updateVenue(self::$curVenues[$unique_venue], $venue);
				}
			
				update_post_meta($post->ID, '_EventVenueID', self::$curVenues[$unique_venue]);
			}
		}

		/**
		 * Search for legacy events
		 *
		 * @param int $number max event count to look up
		 * @return query of posts
		 */
		private static function getLegacyEvents( $number = -1 ) {
			// TODO: needs to account for either v1 posts or v2 'sp_events'
			$query = new WP_Query( array(
				'post_status' => 'any',
				'posts_per_page' => $number,
				'meta_key' => '_EventStartDate',
				'category_name' => 'Events'
			));
			return $query->posts;
		}

		/**
		 * Get category names
		 *
		 * @param array $cats array of category objects
		 * @return array of category names
		 */
		private static function getCatNames( $cats ) {
			foreach ( $cats as $cat ) {
				$r[] = $cat->name;
			}
			return $r;
		}

		/**
		 * Merge lists of category names
		 *
		 * @return array of merged category names
		 */
		private static function mergeCatList ( $new, $old ) {
			$r = (array) self::getCatNames( $new );
			return array_merge( $r, $old );
		}

		/**
		 * Remove event category names
		 *
		 * @param array $cats category names
		 * @param object $removeCat category object to remove from array of categories
		 * @return array of category names
		 */
		private static function removeEventCat( $cats, $removeCat ) {

			foreach ( $cats as $k => $cat ) {
				if ( $cat->term_id == $removeCat->term_id ) {
					unset($cats[$k]);
				}
			}
			return $cats;
		}		
	}

	TribeEventsImport::instance();
}
?>