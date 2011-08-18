<?php
if ( !class_exists( 'Events_Calendar_Pro' ) ) {
	/**
	 * Main plugin
	 */
	class Events_Calendar_Pro {
		const EVENTSERROROPT		= '_tec_events_errors';
		const CATEGORYNAME	 		= 'Events'; // legacy category
		const OPTIONNAME 			= 'sp_events_calendar_options';
		const POSTTYPE				= 'sp_events';
		const TAXONOMY				= 'sp_events_cat';
		
		const VENUE_POST_TYPE = 'sp_venue';
		const VENUE_TITLE = 'Venue';
		const ORGANIZER_POST_TYPE = 'sp_organizer';
		const ORGANIZER_TITLE = 'Organizer';
		const PLUGIN_DOMAIN = 'tribe-events-calendar';
		const VERSION = '2.0';

		private $postTypeArgs = array(
			'public' => true,
			'rewrite' => array('slug' => 'event', 'with_front' => false),
			'menu_position' => 6,
			'supports' => array('title','editor','excerpt','author','thumbnail')
		);
		private $postVenueTypeArgs = array(
			'public' => true,
			'rewrite' => array('slug'=>'venue', 'with_front' => false),
			'show_ui' => true,
			'show_in_menu' => 0,
			'supports' => array('')
		);
		private $postOrganizerTypeArgs = array(
			'public' => true,
			'rewrite' => false,
			'show_ui' => true,
			'show_in_menu' => 0,			 
			'menu_position' => 6,
			'supports' => array('')
		);
		private $taxonomyLabels;

		public $supportUrl = 'http://support.makedesignnotwar.com/';
		public $envatoUrl = 'http://plugins.shaneandpeter.com/';

	    private static $instance;
		private $rewriteSlug = 'events';
		private $rewriteSlugSingular = 'event';
		private $taxRewriteSlug = 'event/category';
		private $monthSlug = 'month';
		private $pastSlug = 'past';
		private $upcomingSlug = 'upcoming';
		private $defaultOptions = '';
		public $latestOptions;
		private $postExceptionThrown = false;
		private $optionsExceptionThrown = false;
		public $displaying;
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginName;
		public $date;
		public $pluginDomain = 'tribe-events-calendar';
		private $tabIndexStart = 2000;

		public $metaTags = array(
			'_EventAllDay',
			'_EventStartDate',
			'_EventEndDate',
			'_EventVenueID',
			'_EventShowMapLink',
			'_EventShowMap',
			'_EventCost',
			'_EventOrganizerID',
			'_EventPhone',
			'_EventHideFromUpcoming',
			self::EVENTSERROROPT
		);

		public $legacyVenueTags = array(
			'_EventVenue',
			'_EventCountry',
			'_EventAddress',
			'_EventCity',
			'_EventState',
			'_EventProvince',
			'_EventZip',
			'_EventPhone',
		);

		public $venueTags = array(
			'_VenueVenue',
			'_VenueCountry',
			'_VenueAddress',
			'_VenueCity',
			'_VenueStateProvince',
			'_VenueZip',
			'_VenuePhone'
		);

		public $organizerTags = array(
			'_OrganizerOrganizer',
			'_OrganizerEmail',
			'_OrganizerWebsite',
			'_OrganizerPhone'
		);

		public $states = array();
		public $currentPostTimestamp;
		public $daysOfWeekShort;
		public $daysOfWeek;
		public $daysOfWeekMin;
		public $monthsFull;
		public $monthsShort;
		
		/* Static Singleton Factory Method */
	    public static function instance()
	    {
	        if (!isset(self::$instance)) {
	            $className = __CLASS__;
	            self::$instance = new $className;
	        }

	        return self::$instance;
	    }		

		/**
		 * Initializes plugin variables and sets up wordpress hooks/actions.
		 *
		 * @return void
		 */
		private function __construct( ) {
			$this->pluginDir		= trailingslashit( basename( dirname(__FILE__) ) );
			$this->pluginPath		= trailingslashit( dirname( dirname(__FILE__) ) );
			$this->pluginUrl 		= WP_PLUGIN_URL.'/'.$this->pluginDir;
			
			register_deactivation_hook( __FILE__, 	array( &$this, 'on_deactivate' ) );
			register_activation_hook( __FILE__, 	array( &$this, 'on_activate' ) );
			$this->addFilters();
			$this->addActions();
		}
		
		public function init() {
			$this->loadTextDomain();
			$this->pluginName = __( 'The Events Calendar', $this->pluginDomain );
			$this->rewriteSlug = $this->getOption('eventsSlug', 'events');
			$this->rewriteSlugSingular = $this->getOption('singleEventSlug', 'event');
			$this->taxRewriteSlug = $this->rewriteSlug . '/' . __( 'category', $this->pluginDomain );
			$this->monthSlug = __('month', $this->pluginDomain);
			$this->upcomingSlug = __('upcoming', $this->pluginDomain);
			$this->pastSlug = __('past', $this->pluginDomain);
			$this->postTypeArgs['rewrite']['slug'] = $this->rewriteSlugSingular;
         	$this->postVenueTypeArgs['rewrite']['slug'] = __( 'venue', $this->pluginDomain );
			$this->currentDay = '';
			$this->errors = '';
			Tribe_Event_Query::init();
			$this->registerPostType();

			//If the custom post type's rewrite rules have not been generated yet, flush them. (This can happen on reactivations.)
			if(is_array(get_option('rewrite_rules')) && !array_key_exists($this->rewriteSlugSingular.'/[^/]+/([^/]+)/?$',get_option('rewrite_rules')))
				$this->flushRewriteRules();
		}

		private function addFilters() {
			add_filter( 'post_class', array( $this, 'post_class') );
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_filter( 'query_vars',		array( $this, 'eventQueryVars' ) );
			add_filter( 'admin_body_class', array($this, 'admin_body_class') );
			add_filter( 'the_content', array($this, 'emptyEventContent' ), 1 );
			add_filter( 'wp_title', array($this, 'maybeAddEventTitle' ), 10, 2 );
			add_filter('bloginfo_rss',  array($this, 'add_space_to_rss' ));
			add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );
			add_filter( 'post_updated_messages', array($this, 'updatePostMessage') );
			
			/* Add nav menu item - thanks to http://wordpress.org/extend/plugins/cpt-archives-in-nav-menus/ */
			add_filter( 'nav_menu_items_' . Events_Calendar_Pro::POSTTYPE, array( $this, 'add_events_checkbox_to_menu' ), null, 3 );
			add_filter( 'wp_nav_menu_objects', array( $this, 'add_current_menu_item_class_to_events'), null, 2);
		}

		public function add_current_menu_item_class_to_events( $items, $args ) {
			foreach($items as $item) {
				if($item->url == $this->getLink() ) {
					if ( is_singular( Events_Calendar_Pro::POSTTYPE ) || is_singular( Events_Calendar_Pro::VENUE_POST_TYPE ) || 
							  is_tax(Events_Calendar_Pro::TAXONOMY) ||
							  tribe_is_upcoming() || tribe_is_past() || tribe_is_month() ) {
						$item->classes[] = 'current-menu-item current_page_item';
					}
					break;
				}
			}

			return $items;
		}
		
		public function add_events_checkbox_to_menu( $posts, $args, $post_type ) {
			global $_nav_menu_placeholder, $wp_rewrite;
			$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;
			$archive_slug = $this->getLink();

			array_unshift( $posts, (object) array(
				'ID' => 0,
				'object_id' => $_nav_menu_placeholder,
				'post_content' => '',
				'post_excerpt' => '',
				'post_title' => $post_type['args']->labels->all_items,
				'post_type' => 'nav_menu_item',
				'type' => 'custom',
				'url' => $archive_slug,
			) );

			return $posts;			
		}

		private function addDebugColumns() {
			add_filter( 'manage_posts_columns', array($this, 'debug_column_headers'));
			add_action( 'manage_posts_custom_column', array($this, 'debug_custom_columns'), 10, 2);
		}
		
		private function addActions() {
			add_action( 'init', array( $this, 'init'), 10 );
			add_action( 'template_redirect',				array( $this, 'loadStyle' ) );
			add_action( 'tribe-events-save-more-options', array( $this, 'flushRewriteRules' ) );
			add_action( 'admin_menu', 		array( $this, 'addOptionsPage' ) );
			add_action( 'admin_init', 		array( $this, 'checkForOptionsChanges' ) );
			add_action( 'admin_menu', 		array( $this, 'addEventBox' ) );
			add_action( 'save_post',		array( $this, 'addEventMeta' ), 15, 2 );
			add_action( 'save_post',		array( $this, 'save_venue_data' ), 16, 2 );
			add_action( 'save_post',		array( $this, 'save_organizer_data' ), 16, 2 );
			add_action( 'pre_get_posts',  array( $this, 'setDate' ));
			add_action( 'pre_get_posts',  array( $this, 'setDisplay' ));
			add_action( 'tribe_events_post_errors', array( 'TEC_Post_Exception', 'displayMessage' ) );
			add_action( 'tribe_events_options_top', array( 'TEC_WP_Options_Exception', 'displayMessage') );
			add_action( 'admin_enqueue_scripts', array( $this, 'addAdminScriptsAndStyles' ) );
			add_action( 'plugins_loaded', array( $this, 'accessibleMonthForm'), -10 );
			add_action( 'the_post', array( $this, 'setReccuringEventDates' ) );
			
			if ( is_admin() && !$this->getOption('spEventsDebug', false) ) {
				add_action('admin_footer', array($this, 'removeMenuItems'));
			} else if ( $this->getOption('spEventsDebug', false) ) {
				$this->addDebugColumns();
				add_action('admin_footer', array($this, 'debugInfo'));
			}			
			add_action( "trash_" . Events_Calendar_Pro::VENUE_POST_TYPE, array($this, 'cleanupPostVenues'));
			add_action( "trash_" . Events_Calendar_Pro::ORGANIZER_POST_TYPE, array($this, 'cleanupPostOrganizers'));
		}
		
		public function debugInfo() {
			echo '<h4>Events Calendar Pro Debug Info:</h4>';
			$this->printDebug($this->date, '$this->date');
			$this->printDebug($this->displaying, '$this->displaying');
		}
		
		public function printDebug($data, $title = '') {
			$title = ($title) ? '<strong>' . $title . '</strong> : ' : '';
			echo '<pre style="white-space:pre-wrap;font-size:11px;margin:1em;">';
			echo $title;
			print_r($data);
			echo '</pre>';
		}
		
		public function get_event_taxonomy() {
			return self::TAXONOMY;
		}

		public function add_space_to_rss($title) {
			global $wp_query;
			if(get_query_var('eventDisplay') == 'upcoming' && get_query_var('post_type') == Events_Calendar_Pro::POSTTYPE) {
				return $title . ' ';
			}

			return $title;
		}

		public function addDateToRecurringEvents($permalink, $post) {
			if(function_exists('tribe_is_recurring_event') && $post->post_type == self::POSTTYPE && tribe_is_recurring_event($post->ID) ) {
				if( is_admin() && !$post->EventStartDate ) {
					if( isset($_REQUEST['eventDate'] ) ) {
						$post->EventStartDate = $_REQUEST['eventDate'];
					} else  {
						$post->EventStartDate = Events_Calendar_Pro::getRealStartDate( $post->ID );
					}
				}
				
				if( '' == get_option('permalink_structure') || 'off' == $this->getOption('useRewriteRules','on') ) {
					return add_query_arg('eventDate', TribeDateUtils::dateOnly( $post->EventStartDate ), $permalink ); 					
				} else {
					return trailingslashit($permalink) . TribeDateUtils::dateOnly( $post->EventStartDate );
				}
			}
			
			return $permalink;
		}
		
		// sorts the meta to ensure we are getting the real start date
		public static function getRealStartDate( $postId ) {
			$start_dates = get_post_meta( $postId, '_EventStartDate' );

			if( is_array( $start_dates ) && sizeof( $start_dates ) > 0 ) {
				sort($start_dates);
				return $start_dates[0];
			}

			return null;
		}		

		public function maybeAddEventTitle($title, $sep){
			if(get_query_var('eventDisplay') == 'upcoming'){
				$new_title = __("Upcoming Events", $this->pluginDomain). ' '.$sep . ' ' . $title;
			}elseif(get_query_var('eventDisplay') == 'past'){
 				$new_title = __("Past Events", $this->pluginDomain) . ' '. $sep . ' ' . $title;

			}elseif(get_query_var('eventDisplay') == 'month'){
				if(get_query_var('eventDate')){
					$new_title = sprintf(__("Events for %s", $this->pluginDomain),date("F, Y",strtotime(get_query_var('eventDate')))) . ' '. $sep . ' ' . $title;
				}else{
					$new_title = sprintf(__("Events this month", $this->pluginDomain),get_query_var('eventDate')) . ' '. $sep . ' ' . $title;
				}

			}else{
				return $title;
			}

			return $new_title;

		}
		
		public function emptyEventContent( $content ) {
			global $post;
			if ( '' == $content && $post->post_type == self::POSTTYPE ) {
				$content = __('No description has been entered for this event.', $this->pluginDomain);
			}
			return $content;
		}
		
		public function debug_column_headers( $columns ) {
			global $post;

			if ( $post->post_type == self::POSTTYPE ) {
				$columns['sp-debug'] = __( 'Debug', $this->pluginDomain );
			}
			
			return $columns;
		}
		
		public function debug_custom_columns( $column_id, $post_id ) {
			if ( $column_id == 'sp-debug' ) {
				echo 'EventStartDate: ' . get_post_meta($post_id, '_EventStartDate', true );
				echo '<br />';
				echo 'EventEndDate: ' . get_post_meta($post_id, '_EventEndDate', true );
			}
			
		}

		public function accessibleMonthForm() {
			if ( isset($_GET['EventJumpToMonth']) && isset($_GET['EventJumpToYear'] )) {
				$_GET['eventDisplay'] = 'month';
				$_GET['eventDate'] = $_GET['EventJumpToYear'] . '-' . $_GET['EventJumpToMonth'];
			}
		}
		
		public function log( $data = array() ) {
			error_log(print_r($data,1));
		}
		
		public function body_class( $c ) {
			if ( get_query_var('post_type') == self::POSTTYPE ) {
				if ( ! is_single() || sp_is_showing_all() ) {
					$c[] = 'events-archive';
				}
				else {
					$c[] = 'events-single';
				}
			}
			return $c;
		}
		
		public function post_class( $c ) {
			global $post;
			if ( $post->post_type == self::POSTTYPE && $terms = get_the_terms( $post->ID , self::TAXONOMY ) ) {
				foreach ($terms as $term) {
					$c[] = 'cat_' . sanitize_html_class($term->slug, $term->term_taxonomy_id);
				}
			}
			return $c;
		}
		
		public function registerPostType() {
			$this->generatePostTypeLabels();
			register_post_type(self::POSTTYPE, $this->postTypeArgs);
			register_post_type(self::VENUE_POST_TYPE, $this->postVenueTypeArgs);
			register_post_type(self::ORGANIZER_POST_TYPE, $this->postOrganizerTypeArgs);
			
			register_taxonomy( self::TAXONOMY, self::POSTTYPE, array(
				'hierarchical' => true,
				'update_count_callback' => '',
				'rewrite' => array('slug'=> $this->taxRewriteSlug),
				'public' => true,
				'show_ui' => true,
				'labels' => $this->taxonomyLabels
			));
			
			if( $this->getOption('showComments','no') == 'yes' ) {
				add_post_type_support( self::POSTTYPE, 'comments');
			}
			
		}
		
		private function generatePostTypeLabels() {
			$this->postTypeArgs['labels'] = array(
				'name' => __('Events', $this->pluginDomain),
				'singular_name' => __('Event', $this->pluginDomain),
				'add_new' => __('Add New', $this->pluginDomain),
				'add_new_item' => __('Add New Event', $this->pluginDomain),
				'edit_item' => __('Edit Event', $this->pluginDomain),
				'new_item' => __('New Event', $this->pluginDomain),
				'view_item' => __('View Event', $this->pluginDomain),
				'search_items' => __('Search Events', $this->pluginDomain),
				'not_found' => __('No events found', $this->pluginDomain),
				'not_found_in_trash' => __('No events found in Trash', $this->pluginDomain)
			);
			
			$this->postVenueTypeArgs['labels'] = array(
				'name' => __('Venues', $this->pluginDomain),
				'singular_name' => __('Venue', $this->pluginDomain),
				'add_new' => __('Add New', $this->pluginDomain),
				'add_new_item' => __('Add New Venue', $this->pluginDomain),
				'edit_item' => __('Edit Venue', $this->pluginDomain),
				'new_item' => __('New Venue', $this->pluginDomain),
				'view_item' => __('View Venue', $this->pluginDomain),
				'search_items' => __('Search Venues', $this->pluginDomain),
				'not_found' => __('No venue found', $this->pluginDomain),
				'not_found_in_trash' => __('No venues found in Trash', $this->pluginDomain)
			);
			
			$this->postOrganizerTypeArgs['labels'] = array(
				'name' => __('Organizers', $this->pluginDomain),
				'singular_name' => __('Organizer', $this->pluginDomain),
				'add_new' => __('Add New', $this->pluginDomain),
				'add_new_item' => __('Add New Organizer', $this->pluginDomain),
				'edit_item' => __('Edit Organizer', $this->pluginDomain),
				'new_item' => __('New Organizer', $this->pluginDomain),
				'view_item' => __('View Venue', $this->pluginDomain),
				'search_items' => __('Search Organizers', $this->pluginDomain),
				'not_found' => __('No organizer found', $this->pluginDomain),
				'not_found_in_trash' => __('No organizers found in Trash', $this->pluginDomain)
			);
			
			$this->taxonomyLabels = array(
				'name' =>  __( 'Event Categories', $this->pluginDomain ),
				'singular_name' =>  __( 'Event Category', $this->pluginDomain ),
				'search_items' =>  __( 'Search Event Categories', $this->pluginDomain ),
				'all_items' => __( 'All Event Categories', $this->pluginDomain ),
				'parent_item' =>  __( 'Parent Event Category', $this->pluginDomain ),
				'parent_item_colon' =>  __( 'Parent Event Category:', $this->pluginDomain ),
				'edit_item' =>   __( 'Edit Event Category', $this->pluginDomain ),
				'update_item' =>  __( 'Update Event Category', $this->pluginDomain ),
				'add_new_item' =>  __( 'Add New Event Category', $this->pluginDomain ),
				'new_item_name' =>  __( 'New Event Category Name', $this->pluginDomain )
			);
			
		}

		public function updatePostMessage( $messages ) {
		  global $post, $post_ID;

		  $messages[self::POSTTYPE] = array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __('Event updated. <a href="%s">View event</a>'), esc_url( get_permalink($post_ID) ) ),
			 2 => __('Custom field updated.'),
			 3 => __('Custom field deleted.'),
			 4 => __('Event updated.'),
			 /* translators: %s: date and time of the revision */
			 5 => isset($_GET['revision']) ? sprintf( __('Event restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __('Event published. <a href="%s">View event</a>'), esc_url( get_permalink($post_ID) ) ),
			 7 => __('Event saved.'),
			 8 => sprintf( __('Event submitted. <a target="_blank" href="%s">Preview event</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			 9 => sprintf( __('Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			 10 => sprintf( __('Event draft updated. <a target="_blank" href="%s">Preview event</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		  );
		  
		  $messages[self::VENUE_POST_TYPE] = array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __('Venue updated. <a href="%s">View venue</a>'), esc_url( get_permalink($post_ID) ) ),
			 2 => __('Custom field updated.'),
			 3 => __('Custom field deleted.'),
			 4 => __('Venue updated.'),
			 /* translators: %s: date and time of the revision */
			 5 => isset($_GET['revision']) ? sprintf( __('Venue restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __('Venue published. <a href="%s">View venue</a>'), esc_url( get_permalink($post_ID) ) ),
			 7 => __('Venue saved.'),
			 8 => sprintf( __('Venue submitted. <a target="_blank" href="%s">Preview venue</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			 9 => sprintf( __('Venue scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview venue</a>'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			 10 => sprintf( __('Venue draft updated. <a target="_blank" href="%s">Preview venue</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		  );
		  
		  $messages[self::ORGANIZER_POST_TYPE] = array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __('Organizer updated. <a href="%s">View organizer</a>'), esc_url( get_permalink($post_ID) ) ),
			 2 => __('Custom field updated.'),
			 3 => __('Custom field deleted.'),
			 4 => __('Organizer updated.'),
			 /* translators: %s: date and time of the revision */
			 5 => isset($_GET['revision']) ? sprintf( __('Organizer restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __('Organizer published. <a href="%s">View organizer</a>'), esc_url( get_permalink($post_ID) ) ),
			 7 => __('Organizer saved.'),
			 8 => sprintf( __('Organizer submitted. <a target="_blank" href="%s">Preview organizer</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			 9 => sprintf( __('Organizer scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview organizer</a>'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			 10 => sprintf( __('Organizer draft updated. <a target="_blank" href="%s">Preview organizer</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		  );		  

		  return $messages;
		}
		
		public function admin_body_class( $classes ) {
			global $current_screen;			
			if ( isset($current_screen->post_type) &&
					($current_screen->post_type == self::POSTTYPE || $current_screen->id == 'settings_page_the-events-calendar.class')
		   ) {
				$classes .= ' events-cal ';
			}
			return $classes;
		}
		
		public function addAdminScriptsAndStyles() {
			// always load style. need for icon in nav.
			wp_enqueue_style( self::POSTTYPE.'-admin', $this->pluginUrl . 'resources/events-admin.css' );		
					
			global $current_screen;
			if ( isset($current_screen->post_type) ) {
				if ( $current_screen->post_type == self::POSTTYPE || $current_screen->id == 'settings_page_the-events-calendar.class' ) {
					wp_enqueue_style( self::POSTTYPE.'-admin-ui', $this->pluginUrl . 'resources/events-admin-ui.css' );		
					wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ui-dialog', $this->pluginUrl . 'resources/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );					
					wp_enqueue_script( 'jquery-ecp-plugins', $this->pluginUrl . 'resources/jquery-ecp-plugins.js', array('jquery') );					
					wp_enqueue_script( self::POSTTYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js', array('jquery-ui-datepicker'), '', true );
					// calling our own localization because wp_localize_scripts doesn't support arrays or objects for values, which we need.
					add_action('admin_footer', array($this, 'printLocalizedAdmin') );
				}elseif( $current_screen->post_type == self::VENUE_POST_TYPE){
					wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ui-dialog', $this->pluginUrl . 'resources/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );					
					wp_enqueue_script( 'jquery-ecp-plugins', $this->pluginUrl . 'resources/jquery-ecp-plugins.js', array('jquery') );					
					wp_enqueue_style( self::POSTTYPE.'-admin-ui', $this->pluginUrl . 'resources/events-admin-ui.css' );					
					wp_enqueue_script( self::VENUE_POST_TYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js');
					wp_enqueue_style( self::VENUE_POST_TYPE.'-admin', $this->pluginUrl . 'resources/hide-visibility.css' );
				}elseif( $current_screen->post_type == self::ORGANIZER_POST_TYPE){
					wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ui-dialog', $this->pluginUrl . 'resources/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );					
					wp_enqueue_script( 'jquery-ecp-plugins', $this->pluginUrl . 'resources/jquery-ecp-plugins.js', array('jquery') );					
					wp_enqueue_style( self::POSTTYPE.'-admin-ui', $this->pluginUrl . 'resources/events-admin.css' );					
					wp_enqueue_script( self::ORGANIZER_POST_TYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js');
					wp_enqueue_style( self::ORGANIZER_POST_TYPE.'-admin', $this->pluginUrl . 'resources/hide-visibility.css' );
				}
			}
		}
		
		public function localizeAdmin() {
			$dom = $this->pluginDomain;
			
			$bits = array(
				'dayNames' => $this->daysOfWeek,
				'dayNamesShort' => $this->daysOfWeekShort,
				'dayNamesMin' => $this->daysOfWeekMin,
				'monthNames' => array_values( $this->monthNames() ),
				'monthNamesShort' => array_values( $this->monthNames( true ) ),
				'nextText' => __( 'Next', $dom ),
				'prevText' => __( 'Prev', $dom ),
				'currentText' => __( 'Today', $dom ),
				'closeText' => __( 'Done', $dom )
			);
			return $bits;
		}

		public function removeMenuItems(){
			?>		
			<script type='text/javascript'>
			/* <![CDATA[ */

			jQuery(document).ready(function($) {
				jQuery('#menu-posts-spvenue').remove();
				jQuery('#menu-posts-sporganizer').remove()
			});
			/* ]]> */
			</script>
			<style type='text/css'>

				#menu-posts-spvenue, #menu-posts-sporganizer{ display:none;}
			
			</style>
			<?php
		}

		public function printLocalizedAdmin() {
			$object_name = 'TEC';
			$vars = $this->localizeAdmin();
			
			$data = "var $object_name = {\n";
			$eol = '';
			foreach ( $vars as $var => $val ) {
				
				if ( gettype($val) == 'array' || gettype($val) == 'object' ) {
					$val = json_encode($val);
				}
				else {
					$val = '"' . esc_js( $val ) . '"';
				} 
				
				$data .= "$eol\t$var: $val";
				$eol = ",\n";
			}
			$data .= "\n};\n";
			
			echo "<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n";
			echo $data;
			echo "/* ]]> */\n";
			echo "</script>\n";
			
		}
		
		public function addOptionsPage() {
			add_options_page($this->pluginName, $this->pluginName, 'administrator', $this->pluginDomain, array($this,'optionsPageView'));
			add_submenu_page( '/edit.php?post_type=sp_events', __('Venues',$this->pluginDomain), __('Venues',$this->pluginDomain), 'edit_posts', 'edit.php?post_type=sp_venue');
			add_submenu_page( '/edit.php?post_type=sp_events', __('Organizers',$this->pluginDomain), __('Organizers',$this->pluginDomain), 'edit_posts', 'edit.php?post_type=sp_organizer');
		}
		
		public function optionsPageView() {
			include( $this->pluginPath . 'admin-views/events-options.php' );
			// every visit to ECP Settings = flush rules.
			$this->flushRewriteRules();
		}
		
		public function checkForOptionsChanges() {
			
			if ( isset($_POST['upgradeEventsCalendar']) && check_admin_referer('upgradeEventsCalendar') ) {
				Tribe_The_Events_Calendar_Import::upgradeData();
			}
			
			if ( isset($_POST['saveEventsCalendarOptions']) && check_admin_referer('saveEventsCalendarOptions') ) {
                $options = $this->getOptions();
				$options['viewOption'] = $_POST['viewOption'];
				if($_POST['defaultCountry']) {
					$countries = Tribe_View_Helpers::constructCountries();
					$defaultCountryKey = array_search( $_POST['defaultCountry'], $countries );
					$options['defaultCountry'] = array( $defaultCountryKey, $_POST['defaultCountry'] );
				}

				if( $_POST['embedGoogleMapsHeight'] ) {
					$options['embedGoogleMapsHeight'] = $_POST['embedGoogleMapsHeight'];
					$options['embedGoogleMapsWidth'] = $_POST['embedGoogleMapsWidth'];
				}
				
				// single event cannot be same as plural. Or empty.
				if ( $_POST['singleEventSlug'] === $_POST['eventsSlug'] || empty($_POST['singleEventSlug']) ) {
					$_POST['singleEventSlug'] = 'event';
				}
				
				// Events slug can't be empty
				if ( empty( $_POST['eventsSlug'] ) ) {
					$_POST['eventsSlug'] = 'events';
				}
				
				$opts = array('embedGoogleMaps', 'showComments', 'displayEventsOnHomepage', 'resetEventPostDate', 'useRewriteRules', 'spEventsDebug', 'eventsSlug', 'singleEventSlug','spEventsAfterHTML','spEventsBeforeHTML','spEventsCountries','defaultValueReplace','eventsDefaultVenueID', 'eventsDefaultOrganizerID', 'eventsDefaultState','eventsDefaultProvince','eventsDefaultAddress','eventsDefaultCity','eventsDefaultZip','eventsDefaultPhone','multiDayCutoff', 'spEventsTemplate');
				foreach ($opts as $opt) {
					if(isset($_POST[$opt]))
						$options[$opt] = $_POST[$opt];
				}
				
				// events slug happiness
				$slug = $options['eventsSlug'];
				$slug = sanitize_title_with_dashes($slug);
				$slug = str_replace('/',' ',$slug);
				$options['eventsSlug'] = $slug;
				$this->rewriteSlug = $slug;
				
				
				if ( $options['useRewriteRules'] == 'on' || isset( $options['eventsSlug']) ) {
					$this->flushRewriteRules();
				}

				if( !isset($_POST['spEventsDebug']) ) {
					$options['spEventsDebug'] = "";
				}

				try {
					$options = apply_filters( 'tribe-events-options', $options );		
					do_action( 'tribe-events-save-more-options' );
					if ( !$this->optionsExceptionThrown ) $options['error'] = "";
				} catch( TEC_WP_Options_Exception $e ) {
					$this->optionsExceptionThrown = true;
					$options['error'] .= $e->getMessage();
				}
				$this->saveOptions($options);
				$this->latestOptions = $options; //XXX ? duplicated in saveOptions() ?
			} // end if
		}
		
		/// OPTIONS DATA
        public function getOptions() {
            if ('' === $this->defaultOptions) {
                $this->defaultOptions = get_option(Events_Calendar_Pro::OPTIONNAME, array());
            }
            return $this->defaultOptions;
        }
		
	public function getOption($optionName, $default = '') {
		if( ! $optionName )
			return null;
		
		if( $this->latestOptions ) 
			return $this->latestOptions[$optionName];

		$options = $this->getOptions();
		return ( isset($options[$optionName]) ) ? $options[$optionName] : $default;
		
	}

			// clean up trashed venues
			public function cleanupPostVenues($postId) {
				$this->removeDeletedPostTypeAssociation('_EventVenueID', $postId);
			}

			// clean up trashed organizers
			public function cleanupPostOrganizers($postId) {
				$this->removeDeletedPostTypeAssociation('_EventOrganizerID', $postId);
			}		

			// do clean up for trashed venues or organizers
			private function removeDeletedPostTypeAssociation($key, $postId) {
				$the_query = new WP_Query(array('meta_key'=>$key, 'meta_value'=>$postId, 'post_type'=> Events_Calendar_Pro::POSTTYPE ));

				while ( $the_query->have_posts() ): $the_query->the_post();
					delete_post_meta(get_the_ID(), $key);
				endwhile;

				wp_reset_postdata();
			}	
		
        public function saveOptions($options) {
            if (!is_array($options)) {
                return;
            }
            if ( update_option(Events_Calendar_Pro::OPTIONNAME, $options) ) {
				$this->latestOptions = $options;
			} else {
				$this->latestOptions = $this->getOptions();
			}
        }
        
        public function deleteOptions() {
            delete_option(Events_Calendar_Pro::OPTIONNAME);
        }
		
		public function truncate($text, $excerpt_length = 44) {

			$text = strip_shortcodes( $text );

			$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]&gt;', $text);
			$text = strip_tags($text);

			$words = explode(' ', $text, $excerpt_length + 1);
			if (count($words) > $excerpt_length) {
				array_pop($words);
				$text = implode(' ', $words);
				$text = rtrim($text);
				$text .= '&hellip;';
				}

			return $text;
		}
		
		public function loadTextDomain() {
			load_plugin_textdomain( $this->pluginDomain, false, $this->pluginDir . 'lang/');
			$this->constructDaysOfWeek();
			$this->initMonthNames();
		}
		
		public function loadStyle() {
			
			$eventsURL = trailingslashit( $this->pluginUrl ) . 'resources/';
			wp_enqueue_script('sp-events-pjax', $eventsURL.'jquery.pjax.js', array('jquery') );			
			wp_enqueue_script('sp-events-calendar-script', $eventsURL.'events.js', array('jquery', 'sp-events-pjax') );
			// is there an events.css file in the theme?
			if ( $user_style = locate_template(array('events/events.css')) ) {
				$styleUrl = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $user_style );
			}
			else {
				$styleUrl = $eventsURL.'events.css';
			}
			$styleUrl = apply_filters( 'tribe_events_stylesheet_url', $styleUrl );
			
			if ( $styleUrl )
				wp_enqueue_style('sp-events-calendar-style', $styleUrl);
		}
	
		
		public function setDate($query) {
			if ( $query->get('eventDisplay') == 'month' ) {
				$this->date = $query->get('eventDate') . "-01";
			} else if ( $query->get('eventDate') ) {
				$this->date = $query->get('eventDate');
			} else if ( $query->get('eventDisplay') == 'month' ) {
				$date = date_i18n( TribeDateUtils::DBDATEFORMAT );
				$this->date = substr_replace( $date, '01', -2 );
			} else if (is_singular(self::POSTTYPE) && $query->get('eventDate') ) {
				$this->date = $query->get('eventDate');
			} else if (!is_singular(self::POSTTYPE)) { // don't set date for single event unless recurring
				$this->date = date(TribeDateUtils::DBDATETIMEFORMAT);
			}
		}
		
		public function setDisplay() {
			global $wp_query;

			if (is_admin())
				$this->displaying = 'admin';
			else
				$this->displaying = $wp_query->query_vars['eventDisplay'];
		}
		
		public function setReccuringEventDates() {
			global $post;
			
			if( function_exists('tribe_is_recurring_event') && is_singular(self::POSTTYPE) && tribe_is_recurring_event() && !sp_is_showing_all() ) {
				$startTime = get_post_meta($post->ID, '_EventStartDate', true);
				$startTime = TribeDateUtils::timeOnly($startTime);
				
				$post->EventStartDate = TribeDateUtils::addTimeToDate($this->date, $startTime);
				$post->EventEndDate = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime($post->EventStartDate) + get_post_meta($post->ID, '_EventDuration', true) );
			}
		}		
		
		/**
		 * Helper method to return an array of 1-12 for months
		 */
		public function months( ) {
			$months = array();
			foreach( range( 1, 12 ) as $month ) {
				$months[ $month ] = $month;
			}
			return $months;
		}

		private function initMonthNames() {
			global $wp_locale;
			$this->monthsFull = array( 
				'January' => $wp_locale->get_month('01'), 
				'February' => $wp_locale->get_month('02'), 
				'March' => $wp_locale->get_month('03'), 
				'April' => $wp_locale->get_month('04'), 
				'May' => $wp_locale->get_month('05'), 
				'June' => $wp_locale->get_month('06'), 
				'July' => $wp_locale->get_month('07'), 
				'August' => $wp_locale->get_month('08'), 
				'September' => $wp_locale->get_month('09'), 
				'October' => $wp_locale->get_month('10'), 
				'November' => $wp_locale->get_month('11'), 
				'December' => $wp_locale->get_month('12') 
			);
			// yes, it's awkward. easier this way than changing logic elsewhere.
			$this->monthsShort = $months = array( 
				'Jan' => $wp_locale->get_month_abbrev( $wp_locale->get_month('01') ), 
				'Feb' => $wp_locale->get_month_abbrev( $wp_locale->get_month('02') ), 
				'Mar' => $wp_locale->get_month_abbrev( $wp_locale->get_month('03') ), 
				'Apr' => $wp_locale->get_month_abbrev( $wp_locale->get_month('04') ), 
				'May' => $wp_locale->get_month_abbrev( $wp_locale->get_month('05') ), 
				'Jun' => $wp_locale->get_month_abbrev( $wp_locale->get_month('06') ), 
				'Jul' => $wp_locale->get_month_abbrev( $wp_locale->get_month('07') ), 
				'Aug' => $wp_locale->get_month_abbrev( $wp_locale->get_month('08') ), 
				'Sep' => $wp_locale->get_month_abbrev( $wp_locale->get_month('09') ), 
				'Oct' => $wp_locale->get_month_abbrev( $wp_locale->get_month('10') ), 
				'Nov' => $wp_locale->get_month_abbrev( $wp_locale->get_month('11') ), 
				'Dec' => $wp_locale->get_month_abbrev( $wp_locale->get_month('12') )
			); 
		}

		/**
		 * Helper method to return an array of translated month names or short month names
		 * @return Array translated month names
		 */
		public function monthNames( $short = false ) {
			if ($short)
				return $this->monthsShort;
			return $this->monthsFull;
		}
	
		/**
	     * Gets the Category id to use for an Event
		 * Deprecated, but keeping in for legacy users for now.
	     * @return int|false Category id to use or false is none is set
	     */
	    static function eventCategory() {
			return get_cat_id( Events_Calendar_Pro::CATEGORYNAME );
	    }
		/**
		 * Flush rewrite rules to support custom links
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 */
		public function flushRewriteRules() {
			global $wp_rewrite; 
			$wp_rewrite->flush_rules();
			// in case this was called too early, let's get it in the end.
			add_action('shutdown', array($this, 'flushRewriteRules'));
		}		
		/**
		 * Adds the event specific query vars to Wordpress
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 * @return mixed array of query variables that this plugin understands
		 */
		public function eventQueryVars( $qvars ) {
			$qvars[] = 'eventDisplay';
			$qvars[] = 'eventDate';
			$qvars[] = 'ical';
			$qvars[] = 'start_date';
			$qvars[] = 'end_date';
			return $qvars;		  
		}
		/**
		 * Adds Event specific rewrite rules.
		 *
		 *	events/				=>	/?post_type=sp_events
		 *  events/month		=>  /?post_type=sp_events&eventDisplay=month
		 *	events/upcoming		=>	/?post_type=sp_events&eventDisplay=upcoming
		 *	events/past			=>	/?post_type=sp_events&eventDisplay=past
		 *	events/2008-01/#15	=>	/?post_type=sp_events&eventDisplay=bydate&eventDate=2008-01-01
		 * events/category/some-events-category => /?post_type=sp_events&sp_event_cat=some-events-category
		 *
		 * @return void
		 */
		public function filterRewriteRules( $wp_rewrite ) {
			if ( '' == get_option('permalink_structure') || 'off' == $this->getOption('useRewriteRules','on') ) {
				
			}

			$base = trailingslashit( $this->rewriteSlug );
			$baseSingle = trailingslashit( $this->rewriteSlugSingular );
			$baseTax = trailingslashit( $this->taxRewriteSlug );
			$baseTax = "(.*)" . $baseTax;
			
			$month = $this->monthSlug;
			$upcoming = $this->upcomingSlug;
			$past = $this->pastSlug;
			$newRules = array();
			
			// single event
			$newRules[$baseSingle . '([^/]+)/(\d{4}-\d{2}-\d{2})/?$'] = 'index.php?' . self::POSTTYPE . '=' . $wp_rewrite->preg_index(1) . "&eventDate=" . $wp_rewrite->preg_index(2);
			$newRules[$baseSingle . '([^/]+)/all/?$'] = 'index.php?' . self::POSTTYPE . '=' . $wp_rewrite->preg_index(1) . "&eventDisplay=all";			
			
			$newRules[$base . 'page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . 'ical'] = 'index.php?post_type=' . self::POSTTYPE . '&ical=1';
			$newRules[$base . '(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&feed=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $month] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=month';
			$newRules[$base . $upcoming . '/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $upcoming] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming';
			$newRules[$base . $past . '/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=past&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $past] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=past';
			$newRules[$base . '(\d{4}-\d{2})$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=month' .'&eventDate=' . $wp_rewrite->preg_index(1);
			$newRules[$base . 'feed/?$'] = 'index.php?eventDisplay=upcoming&post_type=' . self::POSTTYPE . '&feed=rss2';
			$newRules[$base . '?$']						= 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=' . $this->getOption('viewOption','month');

			// single ical
			$newRules[$baseSingle . '([^/]+)/ical/?$' ] = 'index.php?post_type=' . self::POSTTYPE . '&name=' . $wp_rewrite->preg_index(1) . '&ical=1';

			// taxonomy rules.
			$newRules[$baseTax . '([^/]+)/' . $month] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=month';
			$newRules[$baseTax . '([^/]+)/' . $upcoming . '/page/(\d+)'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/' . $upcoming] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=upcoming';
			$newRules[$baseTax . '([^/]+)/' . $past . '/page/(\d+)'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=past&paged=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/' . $past] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=past';
			$newRules[$baseTax . '([^/]+)/(\d{4}-\d{2})$'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=month' .'&eventDate=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/feed/?$'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(2) . '&eventDisplay=upcoming&post_type=' . self::POSTTYPE . '&feed=rss2';
			$newRules[$baseTax . '([^/]+)/?$'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=' . $this->getOption('viewOption','month');
			$newRules[$baseTax . '([^/]+)/ical/?$'] = 'index.php?post_type= ' . self::POSTTYPE . 'eventDisplay=upcoming&sp_events_cat=' . $wp_rewrite->preg_index(2) . '&ical=1';
			$newRules[$baseTax . '([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?post_type= ' . self::POSTTYPE . 'sp_events_cat=' . $wp_rewrite->preg_index(2) . '&feed=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/page/?([0-9]{1,})/?$'] = 'index.php?post_type= ' . self::POSTTYPE . 'sp_events_cat=' . $wp_rewrite->preg_index(2) . '&paged=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/?$'] = 'index.php?post_type= ' . self::POSTTYPE . '&eventDisplay=upcoming&sp_events_cat=' . $wp_rewrite->preg_index(2);
			
			$wp_rewrite->rules = $newRules + $wp_rewrite->rules; 
		}
		
		/**
		 * returns various internal events-related URLs
		 * @param string $type type of link. See switch statement for types.
		 * @param string $secondary for $type = month, pass a YYYY-MM string for a specific month's URL
		 */
		
		public function getLink( $type = 'home', $secondary = false, $term = null ) {
			// if permalinks are off or user doesn't want them: ugly.
			if( '' == get_option('permalink_structure') || 'off' == $this->getOption('useRewriteRules','on') ) {
				return $this->uglyLink($type, $secondary);
			}

			$eventUrl = trailingslashit( home_url() . '/' . $this->rewriteSlug );
			
			// if we're on an Event Cat, show the cat link, except for home.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) ) {
				$eventUrl = trailingslashit( get_term_link( get_query_var('term'), self::TAXONOMY ) );
			} else if ( $term ) {
				$eventUrl = trailingslashit( get_term_link( $term, self::TAXONOMY ) );
			}
			
			switch( $type ) {
				
				case 'home':
					return $eventUrl;
				case 'month':
					if ( $secondary ) {
						return $eventUrl . $secondary;
					}
					return $eventUrl . $this->monthSlug . '/';
				case 'upcoming':
					return $eventUrl . $this->upcomingSlug . '/';
				case 'past':
					return $eventUrl . $this->pastSlug . '/';
				case 'dropdown':
					return $eventUrl;
				case 'ical':
					if ( $secondary == 'single' )
						$eventUrl = trailingslashit(get_permalink());
					return $eventUrl . 'ical/';
				case 'single':
					if($secondary) 
						$post = $secondary;
					else
						global $post;

					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );					
					$eventUrl = trailingslashit(get_permalink($post));
					add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );
					return $eventUrl . TribeDateUtils::dateOnly( $post->EventStartDate );					
				case 'all':
					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );					
					$eventUrl = trailingslashit(get_permalink());
					add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );										
					return $eventUrl . 'all/';
				default:
					return $eventUrl;
			}
			
		}
		
		private function uglyLink( $type = 'home', $secondary = false ) {
			
			$eventUrl = add_query_arg('post_type', self::POSTTYPE, home_url() );
			
			// if we're on an Event Cat, show the cat link, except for home.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) ) {
				$eventUrl = add_query_arg( self::TAXONOMY, get_query_var('term'), $eventUrl );
			}
			
			switch( $type ) {
				
				case 'home':
					return $eventUrl;
				case 'month':
					$month = add_query_arg( array( 'eventDisplay' => 'month'), $eventUrl );
					if ( $secondary )
						$month = add_query_arg( array( 'eventDate' => $secondary ), $month );
					return $month;
				case 'upcoming':
					return add_query_arg( array( 'eventDisplay' => 'upcoming'), $eventUrl );
				case 'past':
					return add_query_arg( array( 'eventDisplay' => 'past'), $eventUrl );
				case 'dropdown':
					$dropdown = add_query_arg( array( 'eventDisplay' => 'month', 'eventDate' => ' '), $eventUrl );
					return rtrim($dropdown); // tricksy
				case 'ical':
					if ( $secondary == 'single' ) {
						return add_query_arg('ical', '1', get_permalink() );
					}
					return home_url() . '/?ical';
			   case 'single':
				   	global $post;
					$post = $secondary ? $secondary : $post;

					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );					
					$eventUrl = trailingslashit(get_permalink($post));
					add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );
					return add_query_arg('eventDate', TribeDateUtils::dateOnly( $post->EventStartDate ), $eventUrl );					   
			   case 'all':
					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );					
					$eventUrl = add_query_arg('eventDisplay', 'all', get_permalink() );
					add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );															
					return $eventUrl;
				default:
					return $eventUrl;
			}
		}
		
		/**
		 * Returns a link to google maps for the given event
		 *
		 * @param string $postId 
		 * @return string a fully qualified link to http://maps.google.com/ for this event
		 */
		public function get_google_maps_args() {

			$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
			$toUrlEncode = "";
			$languageCode = substr( get_bloginfo( 'language' ), 0, 2 );
			foreach( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func('sp_get_'.$val);
				if ( $metaVal ) 
					$toUrlEncode .= $metaVal . " ";
			}
			if ( $toUrlEncode ) 
				return 'f=q&amp;source=embed&amp;hl=' . $languageCode . '&amp;geocode=&amp;q='. urlencode( trim( $toUrlEncode ) );
			return "";
			
		}
		
		/**
		 * Returns a link to google maps for the given event
		 *
		 * @param string $postId 
		 * @return string a fully qualified link to http://maps.google.com/ for this event
		 */
		public function googleMapLink( $postId = null ) {
			if ( $postId === null || !is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			
			$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
			$toUrlEncode = "";
			foreach( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func('sp_get_'.$val, $postId);
				if ( $metaVal ) 
					$toUrlEncode .= $metaVal . " ";
			}
			if ( $toUrlEncode ) 
				return "http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=" . urlencode( trim( $toUrlEncode ) );
			return "";
			
		}
		
		/**
		 * This plugin does not have any deactivation functionality. Any events, categories, options and metadata are
		 * left behind.
		 * 
		 * @return void
		 */
		public function on_deactivate( ) { 
			//remove_filter( 'generate_rewrite_rules', array( $this, 'filterRewriteRules' ) );
			$this->flushRewriteRules();
		}


		/**
		 * Creates the category and sets up the theme resource folder with sample config files.
		 * 
		 * @return void
		 */
		public function on_activate( ) {
			$now = time();
			$firstTime = $now - ($now % 66400);
			
		}
		/**
		 * Converts a set of inputs to YYYY-MM-DD HH:MM:SS format for MySQL
		 */
		public function dateToTimeStamp( $date, $hour, $minute, $meridian ) {
			if ( preg_match( '/(PM|pm)/', $meridian ) && $hour < 12 ) $hour += "12";
			if ( preg_match( '/(AM|am)/', $meridian ) && $hour == 12 ) $hour = "00";
			$date = $this->dateHelper($date);
			return "$date $hour:$minute:00";
		}
		public function getTimeFormat( $dateFormat = TribeDateUtils::DATEONLYFORMAT ) {
			return $dateFormat . ' ' . get_option( 'time_format', TribeDateUtils::TIMEFORMAT );
		}
		/*
		 * ensures date follows proper YYYY-MM-DD format
		 * converts /, - and space chars to -
		**/
		private function dateHelper( $date ) {

			if($date == '')
				return date(TribeDateUtils::DBDATEFORMAT);

			$date = str_replace( array('-','/',' ',':','–','—','-'), '-', $date );
			// ensure no extra bits are added
			list($year, $month, $day) = explode('-', $date);
			
			if ( ! checkdate($month, $day, $year) )
				$date = date(TribeDateUtils::DBDATEFORMAT); // today's date if error
			else
				$date = $year . '-' . $month . '-' . $day;
	
			return $date;
		}
		
		/**
		 * 
			Adds an alias for get_post_meta so we can do extra stuff to the plugin values.
			If you need the raw unfiltered data, use get_post_meta directly. 
			This is mainly for templates.

		***/
		public function getEventMeta( $id, $meta, $single = true ){
			$use_def_if_empty = sp_get_option('defaultValueReplace');
			if($use_def_if_empty){
				$cleaned_tag = str_replace('_Event','',$meta);
				$default = sp_get_option('eventsDefault'.$cleaned_tag);
				$default = apply_filters('filter_eventsDefault'.$cleaned_tag,$default);
				return (get_post_meta( $id, $meta, $single )) ? get_post_meta( $id, $meta, $single ) : $default;
			}else{
				return get_post_meta( $id, $meta, $single );
			}

		}
		/**
		 * Adds / removes the event details as meta tags to the post.
		 *
		 * @param string $postId 
		 * @return void
		 */
		public function addEventMeta( $postId, $post ) {
			// only continue if it's an event post
			if ( $post->post_type != self::POSTTYPE || defined('DOING_AJAX') ) {
				return;
			}
			// don't do anything on autosave or auto-draft either or massupdates
			if ( wp_is_post_autosave( $postId ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' ) {
				return;
			}
			
			// remove these actions even if nonce is not set
			remove_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );
			remove_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );			
						
			if ( !wp_verify_nonce( $_POST['ecp_nonce'], Events_Calendar_Pro::POSTTYPE ) )
				return;
			
			if ( !current_user_can( 'publish_posts' ) )
				return;

			$_POST['Organizer'] = stripslashes_deep($_POST['organizer']);
			$_POST['Venue'] = stripslashes_deep($_POST['venue']);
			
			Tribe_Event_API::saveEventMeta($postId, $_POST, $post);
		}
		
		
		//** If you are saving a new venu separate from an event
		public function save_venue_data( $postID = null, $post=null ) {
			global $_POST;

			// don't do anything on autosave or auto-draft either or massupdates
			// Or inline saves, or data being posted without a venue Or
			// finally, called from the save_post action, but on save_posts that
			// are not venue posts
			if ( wp_is_post_autosave( $postID ) || $post->post_status == 'auto-draft' ||
                 isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' ||
                 !$_POST['venue'] ||
                 ($post->post_type != self::VENUE_POST_TYPE && $postID)) {
				return;
			}
				 
			if ( !current_user_can( 'publish_posts' ) )
				return;				 

			//There is a possibility to get stuck in an infinite loop. 
			//That would be bad.
			remove_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );

			$data = stripslashes_deep($_POST['venue']);
			$venue_id = Tribe_Event_API::updateVenue($postID, $data);

			return $venue_id;
		}

		function get_venue_info($p = null){
			$r = new WP_Query(array('post_type' => self::VENUE_POST_TYPE, 'nopaging' => 1, 'post_status' => 'publish', 'ignore_sticky_posts ' => 1,'orderby'=>'title', 'order'=>'ASC','p' => $p));
			if ($r->have_posts()) :
				return $r->posts;
			endif;
			return false;
		}

		function saved_venues_dropdown($current = null, $name="venue[VenueID]"){
			$venues = $this->get_venue_info();
			
			if($venues){
				echo '<select name="'.$name.'" id="saved_venue">';
					echo '<option value="0">' . __("Use New Venue", $this->pluginDomain) . '</option>';
				foreach($venues as $venue){
					$selected = ($current == $venue->ID) ? 'selected="selected"' : '';
					echo "<option data-address=" . json_encode( tribe_venue_get_full_address($venue->ID) ) . " value='{$venue->ID}' $selected>{$venue->post_title}</option>";
				}
				echo '</select>';
			}else{
				echo '<p class="nosaved">'.__('No saved venues yet.',$this->lion).'</p>';
			}
		}

		//** If you are saving a new organizer along with the event, we will do this:
		public function save_organizer_data( $postID = null, $post=null ) {
			global $_POST;

			// don't do anything on autosave or auto-draft either or massupdates
			// Or inline saves, or data being posted without a organizer Or
			// finally, called from the save_post action, but on save_posts that
			// are not organizer posts
			if ( wp_is_post_autosave( $postID ) || $post->post_status == 'auto-draft' ||
                 isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' ||
                 !$_POST['organizer'] ||
                 ($post->post_type != self::ORGANIZER_POST_TYPE && $postID)) {
				return;
			}
				 
			if ( !current_user_can( 'publish_posts' ) )
				return;				 				 

			//There is a possibility to get stuck in an infinite loop. 
			//That would be bad.
			remove_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );

			$data = stripslashes_deep($_POST['organizer']);

			$organizer_id = Tribe_Event_API::updateOrganizer($postID, $data);

			return $organizer_id;
		}

		// abstracted for EventBrite
		public function add_new_organizer($data, $post=null) {
			if($data['OrganizerID'])
				return $data['OrganizerID'];

			if ( $post->post_type == self::ORGANIZER_POST_TYPE && $post->ID) {
				$data['OrganizerID'] = $post->ID;
			}

			//google map checkboxes
			$postdata = array(
				'post_title' => $data['Organizer'],
				'post_type' => self::ORGANIZER_POST_TYPE,
				'post_status' => 'publish',
				'ID' => $data['OrganizerID']
			);

			if( isset($data['OrganizerID']) && $data['OrganizerID'] != "0" ) {
				$organizer_id = $data['OrganizerID'];
				wp_update_post( array('post_title' => $data['Organizer'], 'ID'=>$data['OrganizerID'] ));
			} else {
				$organizer_id = wp_insert_post($postdata, true);
			}

			if( !is_wp_error($organizer_id) ) {
				foreach ($data as $key => $var) {
					update_post_meta($organizer_id, '_Organizer'.$key, $var);
				}

				return $organizer_id;
			}
		}

		function get_organizer_info($p = null){
			$r = new WP_Query(array('post_type' => self::ORGANIZER_POST_TYPE, 'nopaging' => 1, 'post_status' => 'publish', 'ignore_sticky_posts ' => 1,'orderby'=>'title', 'order'=>'ASC', 'p' => $p));
			if ($r->have_posts()) :
				return $r->posts;
			endif;
			return false;
		}

		function saved_organizers_dropdown($current = null, $name="organizer[OrganizerID]"){
			$organizers = $this->get_organizer_info();
			if($organizers){
				echo '<select name="'.$name.'" id="saved_organizer">';
					echo '<option value="0">' . __('Use New Organizer', $this->pluginDomain) . '</option>';
				foreach($organizers as $organizer){
					$selected = ($current == $organizer->ID) ? 'selected="selected"' : '';
					echo "<option value='{$organizer->ID}' $selected>{$organizer->post_title}</option>";
				}
				echo '</select>';
			}else{
				echo '<p class="nosaved_organizer">'.__('No saved organizers yet.',$this->lion).'</p>';
			}
		}

		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function EventsChooserBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			foreach ( $this->metaTags as $tag ) {
				if ( $postId && isset($_GET['post']) && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
					// Sort the meta to make sure it is correct for recurring events
					$meta = get_post_meta($postId,$tag); sort($meta);
					$$tag = $meta[0];
				} else {
					$cleaned_tag = str_replace('_Event','',$tag);
					$$tag = sp_get_option('eventsDefault'.$cleaned_tag);
				}
			}
			if($_EventVenueID){
				foreach($this->venueTags as $tag) {
					$$tag = get_post_meta($_EventVenueID, $tag, true );
				}

			}else{
				foreach ( $this->legacyVenueTags as $tag ) {
					if ( $postId && isset($_GET['post']) && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
						$cleaned_tag = str_replace('_Event','_Venue',$tag);
						$$cleaned_tag = get_post_meta( $postId, $tag, true );
					} else {
						$cleaned_tag = str_replace('_Event','',$tag);

						if($cleaned_tag == 'Cost')
							continue;

						${'_Venue'.$cleaned_tag} = sp_get_option('eventsDefault'.$cleaned_tag);
					}
				}

				$_VenueStateProvince = -1; // we want to use default values here
			}
	/*
			foreach($this->organizerTags as $tag)
				$$tag = get_post_meta($_EventOrganizerID, $tag, true );*/

			$isEventAllDay = ( $_EventAllDay == 'yes' || ! TribeDateUtils::dateOnly( $_EventStartDate ) ) ? 'checked="checked"' : ''; // default is all day for new posts
			$startMonthOptions 	 = Tribe_View_Helpers::getMonthOptions( $_EventStartDate );
			$endMonthOptions 		 = Tribe_View_Helpers::getMonthOptions( $_EventEndDate );
			$startYearOptions 	 = Tribe_View_Helpers::getYearOptions( $_EventStartDate );
			$endYearOptions		 = Tribe_View_Helpers::getYearOptions( $_EventEndDate );
			$startMinuteOptions 	 = Tribe_View_Helpers::getMinuteOptions( $_EventStartDate );
			$endMinuteOptions     = Tribe_View_Helpers::getMinuteOptions( $_EventEndDate );
			$startHourOptions	 	 = Tribe_View_Helpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventStartDate, true );
			$endHourOptions		 = Tribe_View_Helpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventEndDate );
			$startMeridianOptions = Tribe_View_Helpers::getMeridianOptions( $_EventStartDate, true );
			$endMeridianOptions	 = Tribe_View_Helpers::getMeridianOptions( $_EventEndDate );
			
			if( $_EventStartDate )
				$start = TribeDateUtils::dateOnly($_EventStartDate);

			$EventStartDate = ( $start ) ? $start : date('Y-m-d');
			
			if ( $_REQUEST['eventDate'] != null )
				$EventStartDate = $_REQUEST['eventDate'];
			
			if( $_EventEndDate )
				$end = TribeDateUtils::dateOnly($_EventEndDate);

			$EventEndDate = ( $end ) ? $end : date('Y-m-d');
			
			if ( $_REQUEST['eventDate'] != null ) {
				$duration = get_post_meta( $postId, '_EventDuration', true );
				$EventEndDate = TribeDateUtils::dateOnly( strtotime($EventStartDate) + $duration, true );
			}

			include( $this->pluginPath . 'admin-views/events-meta-box.php' );
		}
		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function VenueMetaBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			if($post->post_type == self::VENUE_POST_TYPE){
					
				foreach ( $this->venueTags as $tag ) {
					if ( $postId && isset($_GET['post']) && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
						$$tag = esc_html(get_post_meta( $postId, $tag, true ));
					} else {
						$cleaned_tag = str_replace('_Venue','',$tag);
						$$tag = sp_get_option('eventsDefault'.$cleaned_tag);
					}
				}
			}
			?>
				<style type="text/css">
						#EventInfo {border:none;}
				</style>
				<div id='eventDetails' class="inside eventForm">	
					<table cellspacing="0" cellpadding="0" id="EventInfo" class="VenueInfo">
					<?php
					include( $this->pluginPath . 'admin-views/venue-meta-box.php' );
					?>
					</table>
				</div>
			<?php
		}		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function OrganizerMetaBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			if($post->post_type == self::ORGANIZER_POST_TYPE){
					
				foreach ( $this->organizerTags as $tag ) {
					if ( $postId && isset($_GET['post']) && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
						$$tag = get_post_meta( $postId, $tag, true );
					}
				}
			}
			?>
				<style type="text/css">
						#EventInfo {border:none;}
				</style>
				<div id='eventDetails' class="inside eventForm">	
					<table cellspacing="0" cellpadding="0" id="EventInfo" class="OrganizerInfo">
					<?php
					include( $this->pluginPath . 'admin-views/organizer-meta-box.php' );
					?>
					</table>
				</div>
			<?php
		}
		
		public function verify_unique_name($name, $type,$id = 0){
			global $wpdb;
			$name = stripslashes($name);
			if($type == 'venue'){
				$post_type = self::VENUE_POST_TYPE;
			}elseif($type == 'organizer'){
				$post_type = self::ORGANIZER_POST_TYPE;
			}

			$results = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->posts} WHERE post_type = %s && post_title = %s && post_status = 'publish' && ID != %s",$post_type,$name,$id));

			if($results){
				return 0;
			}else{
				return 1;
			}

		}
		/**
		 * Given a date (YYYY-MM-DD), returns the first of the next month
		 *
		 * @param date
		 * @return date
		 */
		public function nextMonth( $date ) {
			$dateParts = split( '-', $date );
			if ( $dateParts[1] == 12 ) {
				$dateParts[0]++;
				$dateParts[1] = "01";
				$dateParts[2] = "01";
			} else {
				$dateParts[1]++;
				$dateParts[2] = "01";
			}
			if ( $dateParts[1] < 10 && strlen( $dateParts[1] ) == 1 ) {
				$dateParts[1] = "0" . $dateParts[1];
			}
			$return =  $dateParts[0] . '-' . $dateParts[1];
			return $return;
		}
		/**
		 * Given a date (YYYY-MM-DD), return the first of the previous month
		 *
		 * @param date
		 * @return date
		 */
		public function previousMonth( $date ) {
			$dateParts = split( '-', $date );

			if ( $dateParts[1] == 1 ) {
				$dateParts[0]--;
				$dateParts[1] = "12";
				$dateParts[2] = "01";
			} else {
				$dateParts[1]--;
				$dateParts[2] = "01";
			}
			if ( $dateParts[1] < 10 ) {
				$dateParts[1] = "0" . $dateParts[1];
			}
			$return =  $dateParts[0] . '-' . $dateParts[1];

			return $return;
		}

		/**
		 * Callback for adding the Meta box to the admin page
		 * @return void
		 */
		public function addEventBox( ) {
			add_meta_box( 'Event Details', $this->pluginName, array( $this, 'EventsChooserBox' ), self::POSTTYPE, 'normal', 'high' );
			add_meta_box( 'Event Options', __('Event Options', $this->pluginDomain), array( $this, 'eventMetaBox' ), self::POSTTYPE, 'side', 'default' );
			
			add_meta_box( 'Venue Details', __('Venue Information', $this->pluginDomain), array( $this, 'VenueMetaBox' ), self::VENUE_POST_TYPE, 'normal', 'high' );
			add_meta_box( 'Organizer Details', __('Organizer Information', $this->pluginDomain), array( $this, 'OrganizerMetaBox' ), self::ORGANIZER_POST_TYPE, 'normal', 'high' );
		}
		public function eventMetaBox() {
			include( $this->pluginPath . 'admin-views/event-sidebar-options.php' );
		}

		public function getDateString( $date ) {
			$monthNames = $this->monthNames();
			$dateParts = split( '-', $date );
			$timestamp = mktime( 0, 0, 0, $dateParts[1], 1, $dateParts[0] );
			return $monthNames[date( "F", $timestamp )] . " " . $dateParts[0];
		}
		/**
	     * echo the next tab index
		 * @return void
		 */
		public function tabIndex() {
			echo $this->tabIndexStart;
			$this->tabIndexStart++;
		}

		public function getEvents( $args = '' ) {
			$tribe_ecp = Events_Calendar_Pro::instance();
			$defaults = array(
				'posts_per_page' => get_option( 'posts_per_page', 10 ),
				'post_type' => Events_Calendar_Pro::POSTTYPE,
				'orderby' => 'event_date',
				'order' => 'ASC'
			);			

			$args = wp_parse_args( $args, $defaults);
			return Tribe_Event_Query::getEvents($args);
		}
		
		public function isEvent( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( get_post_field('post_type', $postId) == self::POSTTYPE ) {
				return true;
			}
			return false;
		}
		
		public function isVenue( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( get_post_field('post_type', $postId) == self::VENUE_POST_TYPE ) {
				return true;
			}
			return false;
		}

		/**
		** Get a "previous/next post" link for events. Ordered by start date instead of ID.
		**/

		public function get_event_link($post, $mode = 'next',$anchor = false){
			global $wpdb;

			if($mode == 'previous'){
				$order = 'DESC';
				$sign = '<';
			}else{
				$order = 'ASC';
				$sign = '>';
			}
			
			$date = $post->EventStartDate;
			$id = $post->ID;
			
			$eventsQuery = "
				SELECT $wpdb->posts.*, d1.meta_value as EventStartDate
				FROM $wpdb->posts 
				LEFT JOIN $wpdb->postmeta as d1 ON($wpdb->posts.ID = d1.post_id)
				WHERE $wpdb->posts.post_type = '".self::POSTTYPE."'
				AND d1.meta_key = '_EventStartDate'
				AND ((d1.meta_value = '" .$date . "' AND ID $sign ".$id.") OR
					d1.meta_value $sign '" .$date . "')
				AND $wpdb->posts.post_status = 'publish'
				ORDER BY TIMESTAMP(d1.meta_value) $order, ID $order
				LIMIT 1";

			$results = $wpdb->get_row($eventsQuery, OBJECT);

			if(is_object($results)) {
				if ( !$anchor ) {
					$anchor = $results->post_title;
				} elseif ( strpos( $anchor, '%title%' ) ) {
					$anchor = preg_replace( '|%title%|', $results->post_title, $anchor );
				}

				echo '<a href='.tribe_get_event_link($results).'>'.$anchor.'</a>';
				
			}
		}
		
		/**
		 * build an ical feed from events posts
		 */
		public function iCalFeed( $postId = null, $eventCatSlug = null ) {
		    $getstring = $_GET['ical'];
			$wpTimezoneString = get_option("timezone_string");
			$postType = self::POSTTYPE;
			$events = "";
			$lastBuildDate = "";
			$eventsTestArray = array();
			$blogHome = get_bloginfo('home');
			$blogName = get_bloginfo('name');
			$includePosts = ( $postId ) ? '&include=' . $postId : '';
			$eventsCats = ( $eventCatSlug ) ? '&'.self::TAXONOMY.'='.$eventCatSlug : '';
			
			$eventPosts = get_posts( 'numberposts=-1&post_type=' . $postType . $includePosts . $eventsCats );
			foreach( $eventPosts as $eventPost ) {
				// convert 2010-04-08 00:00:00 to 20100408T000000 or YYYYMMDDTHHMMSS
				$startDate = str_replace( array("-", " ", ":") , array("", "T", "") , get_post_meta( $eventPost->ID, "_EventStartDate", true) );
				$endDate = str_replace( array("-", " ", ":") , array("", "T", "") , get_post_meta( $eventPost->ID, "_EventEndDate", true) );
				if( get_post_meta( $eventPost->ID, "_EventAllDay", true ) == "yes" ) {
					$startDate = substr( $startDate, 0, 8 );
					$endDate = substr( $endDate, 0, 8 );
					// endDate bumped ahead one day to counter iCal's off-by-one error
					$endDateStamp = strtotime($endDate);
					$endDate = date( 'Ymd', $endDateStamp + 86400 );
					$type="DATE";
				}else{
					$type="DATE-TIME";
				}
				$description = preg_replace("/[\n\t\r]/", " ", strip_tags( $eventPost->post_content ) );
				//$cost = get_post_meta( $eventPost->ID, "_EventCost", true);
				//if( $cost ) $description .= " Cost: " . $cost;
				// add fields to iCal output
				$events .= "BEGIN:VEVENT\n";
				$events .= "DTSTART;VALUE=$type:" . $startDate . "\n";
				$events .= "DTEND;VALUE=$type:" . $endDate . "\n";
				$events .= "DTSTAMP:" . date("Ymd\THis", time()) . "\n";
				$events .= "CREATED:" . str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_date ) . "\n";
				$events .= "LAST-MODIFIED:". str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_modified ) . "\n";
		        $events .= "UID:" . $eventPost->ID . "@" . $blogHome . "\n";
		        $events .= "SUMMARY:" . $eventPost->post_title . "\n";				
		        $events .= "DESCRIPTION:" . str_replace(",",'\,',$description) . "\n";
				$events .= "LOCATION:" . html_entity_decode(sp_get_full_address( $eventPost->ID, true ), ENT_QUOTES) . "\n";
				$events .= "URL:" . get_permalink( $eventPost->ID ) . "\n";
		        $events .= "END:VEVENT\n";
			}
	        header('Content-type: text/calendar');
	        header('Content-Disposition: attachment; filename="iCal-Events_Calendar_Pro.ics"');
			$content = "BEGIN:VCALENDAR\n";
			$content .= "VERSION:2.0\n";
			$content .= "PRODID:-//" . $blogName . "//NONSGML v1.0//EN\n";
			$content .= "CALSCALE:GREGORIAN\n";
			$content .= "METHOD:PUBLISH\n";
			$content .= "X-WR-CALNAME:" . $blogName . "\n";
			$content .= "X-ORIGINAL-URL:" . $blogHome . "\n";
			$content .= "X-WR-CALDESC:Events for " . $blogName . "\n";
			if( $wpTimezoneString ) $content .= "X-WR-TIMEZONE:" . $wpTimezoneString . "\n";
			$content .= $events;
			$content .= "END:VCALENDAR";
			echo $content;
			exit;
		}
		
		private function constructDaysOfWeek() {
			global $wp_locale;
			for ($i = 0; $i <= 6; $i++) {
				$day = $wp_locale->get_weekday($i);
				$this->daysOfWeek[$i] = $day;
				$this->daysOfWeekShort[$i] = $wp_locale->get_weekday_abbrev($day);
				$this->daysOfWeekMin[$i] = $wp_locale->get_weekday_initial($day);
			}
		}

		public function setPostExceptionThrown( $thrown ) {
			$this->postExceptionThrown = $thrown;
		}
		public function getPostExceptionThrown() {
			return $this->postExceptionThrown;
		}

      public function do_action($name, $event_id = null, $showMessage = false, $extra_args = null) {
         try {
            do_action( $name, $event_id, $extra_args );
            if( !$this->getPostExceptionThrown() && $event_id ) delete_post_meta( $event_id, Events_Calendar_Pro::EVENTSERROROPT );
         } catch ( TEC_Post_Exception $e ) {
            $this->setPostExceptionThrown(true);
            if ($event_id) {
               update_post_meta( $event_id, self::EVENTSERROROPT, trim( $e->getMessage() ) );
            }

            if( $showMessage ) {
               $e->displayMessage($showMessage);
            }
         }
      }
	} // end Events_Calendar_Pro class

	Events_Calendar_Pro::instance();
	
	// backwards compatability
	global $sp_ecp;
	$sp_ecp = Events_Calendar_Pro::instance();
	
	add_filter('generate_rewrite_rules', array(&$sp_ecp,'filterRewriteRules'));
} // end if !class_exists Events_Calendar_Pro
