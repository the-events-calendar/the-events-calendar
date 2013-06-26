<?php
/**
 * Import functionality from the 1.x to 2.x open source plugin - The Events Calendar
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('TribeEventsImport')) {

	/**
	 * Import events from 1.x version of The Events Calendar to 2.x.
	 */
	class TribeEventsImport {

		/* Static Singleton Factory Method */
		private static $instance;

        /**
         * Singleton method.
         *
         * @return TribeEventsImport
         */
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

		private static $upgradeMessage = '';

        /**
         * Class constructor.
         */
        private function __construct( ) {
			add_action( 'admin_init', array( $this, 'upgradeData' ) );
			add_action( 'tribe_settings_after_form_element_tab_general', array( $this, 'adminForm' ) );
			add_action( 'admin_notices', array( $this, 'upgradeNotice' ) );
			add_action( 'admin_notices', array( $this, 'promptUpgrade') );
		}

        /**
         * If the user has upgraded from a really old version, display the proper prompt.
         *
         * @return void
         */
        public function promptUpgrade() {
			if ( self::hasLegacyEvents() ) {
				$utm_string = '?utm_source=notices&utm_campaign=in-app&utm_medium=plugin-tec';
				echo '<div class="error"><p>' .
						sprintf(
							__('Welcome to Events 2.0! This is a HUGE upgrade from 1.6.5. Please make sure you have backed up before proceeding any further. You can easily <a href="%s">revert to an old version</a> if you want to backup first. This upgrade includes two major steps, <a href="%s">migrating data</a> &amp; updating your templates as necessary. There have been significant changes to the template tags and functions. Check out our <a href="%s">walk-through on the upgrade</a> before proceeding and check out the FAQ &amp; Knowledge base from the <a href="%s">support page</a>. If you\'re new to The Events Calendar, you may want to review our <a href="%s">new user primer</a>.<br/><br/> You have events that need to be migrated.  Please visit the bottom of the <a href="%s">settings page</a> to perform the migration.', 'tribe-events-calendar'),
							'http://wordpress.org/extend/plugins/the-events-calendar/download/'.$utm_string,
							'edit.php?post_type=' . TribeEvents::POSTTYPE . '&page=tribe-settings&tab=general',
							TribeEvents::$tribeUrl . 'migrating-from-events-calendar-1-6-5-to-2-0'.$utm_string,
							TribeEvents::$tribeUrl . 'support/'.$utm_string,
							TribeEvents::$tribeUrl . 'support/documentation/events-calendar-pro-new-user-primer/'.$utm_string,
							'edit.php?post_type=' . TribeEvents::POSTTYPE .'&page=tribe-events-calendar'
						) .
					'</p></div>';
			}
		}

        /**
         * If There are legacy events, display the proper form.
         *
         * @return void
         */
        public function adminForm() {
			if ( self::hasLegacyEvents() ) {
				?>
				<form id="tribe-upgrade" method="post" >
					<?php wp_nonce_field('upgradeEventsCalendar') ?>
					<h4><?php _e('Upgrade from The Events Calendar', 'tribe-events-calendar' ); ?></h4>
					<p><?php _e('It appears that you have some old events calendar data that needs to be upgraded. Please be sure to back up your database before initiating the upgrade. This process can not be undone.', 'tribe-events-calendar' ) ?></p>
					<input type="submit" value="<?php _e('Migrate Data!', 'tribe-events-calendar'); ?>" class="button-secondary" name="upgradeEventsCalendar" />
				</form>
				<?php
			}
		}

		/**
		 * Will upgrade data from old free plugin to pro plugin
         *
         * @return void
		 */
		public static function upgradeData() {
			$num_upgraded = 0;
			if ( isset($_POST['upgradeEventsCalendar']) && check_admin_referer('upgradeEventsCalendar') ) {

				/*

				TODO: migrate the following:

				* option "sp_events_calendar_options" needs to be renamed as TribeEvents::OPTIONNAME
				* posts of type "sp_events" need to be moved to TribeEvents::POSTTYPE
				* posts of type "sp_venue" need to be moved to TribeEvents::VENUE_POST_TYPE
				* posts of type "sp_organizer" need to be moved to TribeEvents::ORGANIZER_POST_TYPE
				* categories of type "sp_events_cat" need to be moved to TribeEvents::TAXONOMY
				* options that saved using on/enabled/yes need to be set to 1 and off/disabled/no being 0
				* update plugin meta for EVERY event post meta to replace on/enabled/yes with 1 and off/disabled/no to be 0

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

					// Translate the post's setting for google maps display
					self::translateGoogleMaps( $post );
					$num_upgraded++;

				}
				if ( $num_upgraded > 0 ) {
					self::$upgradeMessage = sprintf( __( 'You successfully migrated (%d) entries.', 'tribe-events-calendar' ), $num_upgraded );
				}
			}
		}

        /**
         * Show the upgrade notice.
         *
         * @return void
         */
        public static function upgradeNotice() {
			if ( self::$upgradeMessage != '' ) {
				echo '<div class="updated"><p>' . self::$upgradeMessage . '</p></div>';
				self::$upgradeMessage = '';
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
				if( !isset( self::$curVenues[$unique_venue] ) ) {
					self::$curVenues[$unique_venue] = TribeEventsAPI::createVenue($venue);
				} else {
					TribeEventsAPI::updateVenue(self::$curVenues[$unique_venue], $venue);
				}

				update_post_meta($post->ID, '_EventVenueID', self::$curVenues[$unique_venue]);
			}
		}

		/**
		 * Search for legacy events
		 *
		 * @param int $number max event count to look up
		 * @return array query of posts
		 */
		private static function getLegacyEvents( $number = -1 ) {
			// TODO: needs to account for either v1 posts or v2 'sp_events'
			$query = new WP_Query( array(
				'post_status' => 'any',
				'posts_per_page' => $number,
				'meta_key' => '_EventStartDate',
				'category_name' => 'Events'
			));

			if (count($query->posts)) {
				TribeEvents::debug( __( 'Install has 1 or more legacy event!', 'tribe-events-calendar' ), false, 'warning' );
			}
			return $query->posts;
		}

		/**
		 * Get category names
		 *
		 * @param array $cats array of category objects
		 * @return array of category names
		 */
		private static function getCatNames( $cats ) {
			$r = array();
			foreach ( $cats as $cat ) {
				$r[] = $cat->name;
			}
			return $r;
		}

		/**
		 * Merge lists of category names
		 *
		 * @param $new
		 * @param $old
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

		/**
		 * Translate Google Maps setting over
		 *
		 * @param object $post post object
         * @return void
		 */
		private static function translateGoogleMaps( $post ) {
			$show_map = (get_post_meta( $post->ID, '_EventShowMap', 'false' ) == 'true') ? '1' : '0';
			update_post_meta( $post->ID, '_EventShowMap', $show_map );
			$show_map_link = (get_post_meta( $post->ID, '_EventShowMapLink', 'false' ) == 'true') ? '1' : '0';
			update_post_meta( $post->ID, '_EventShowMapLink', $show_map_link );
		}
	}

	TribeEventsImport::instance();
}
