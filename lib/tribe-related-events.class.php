<?php
if ( !class_exists( 'TribeRelatedEvents' ) ) {
	class TribeRelatedEvents {
		
		// Set up any required variables.
		private static $instance;
		
		// getInstance function
		public function getInstance() {
			if (null == self::$instance) {
				self::$instance = new TribeRelatedEvents;
			}
			return self::$instance;
		}
		
		// Function to add the proper actions on construct.
		public function __construct() {
			add_action('init', array($this, 'registerShortcodes'), 5);
			add_action('init', array($this, 'setUpThumbnails'), 5);
		}
		
		// Function for registering the shortcode.
		public function registerShortcodes() {
			add_shortcode('related-events', array($this, 'shortcodeFeature'));
		}
		
		// Function with the shortcode details.
		public function shortcodeFeature($atts, $content = null) {
			$defaults = array(
				'title' => 'Related Events',
				'count' => 3,
				'thumbnails' => false,
				'start_date' => false
			);
			$atts = shortcode_atts($defaults, $atts);
			ob_start();
			$this->displayEvents( $atts['title'], $atts['count'], $atts['thumbnails'], $atts['start_date']);
			return ob_get_clean();
		}
		
		// Function to set up thumbnails.
		public function setUpThumbnails() {
			if (current_theme_supports('post-thumbnails')) {
				global $_wp_additional_image_sizes;
				if (!isset($_wp_additional_image_sizes['realted-event-thumbnail'])) {
					add_image_size('related-event-thumbnail', 150, 100, true);
				}
			}
		}
		
		
		// Function to get the Related Events.
		public function getEvents($count=3 ) {
			$post_id = get_the_ID();
			$event_taxonomy_name = "tribe_events";
			if ($count > 5) { $count = 5; }
			if ($count < 1) { $count = 1; }
			
			// Get the tag from the current post.
			$tags = get_the_tags($post_id);
			if (is_array($tags)) {
				// Pick a tag to check for.
				$tag = $tags[array_rand($tags, 1)]->slug;
			}
			
			if ( $tag ) {
				// Make sure not to return the current post/event.
				$exclude = array($post_id);
				
				$events = array();
				// Get an array of posts.
				if ($tag = get_term_by('slug', $tag, 'post_tag')) {
					$events = get_posts( array( 'tag' => $tag->slug, 'numberposts' => $count, 'exclude' => $exclude, 'post_type' => $event_taxonomy_name, 'orderby' => 'rand' ) );
				}
			}
			return $events;
		}
		
		// Set up the code to display the events.
		public function displayEvents( $title, $count=3, $thumbnails=false, $start_date=false ) {
			// Get the evemts.
			$events = self::getEvents( $count );
			
			echo '<div class="related-events-wrapper">';
			echo '<div class="related-events-title">'.$title .'</div>';
			// If events were returned, display them.
			if (is_array($events) && count($events)) {
				echo '<ul class="related-events">';
				foreach ($events as $event) {
					echo '<li>';
					// If thumbnail was requested, get and display it.
					if ($thumbnails) {
						$thumb = get_the_post_thumbnail($event->ID, 'related-event-thumbnail' );
						if ($thumb) {
							echo '<div class="related-event-thumbnail"><a href="' .get_permalink($event) .'">' .$thumb .'</a></div>';
						}
					}
					// If startdate was requested, get and display it.
					if ($start_date) {
						$date = $event->EventStartDate;
						$date = new DateTime($date);
						$date = $date->format('M. jS');
						echo '<div class="related-event-date">' .$date .'</div>';
					}
					// Display the other event information.
					echo '<div class="related-event-title"><a href="' .get_permalink($event) .'">' .get_the_title($event) .'</a></div>';
					echo '</li>';
				}
				echo '</ul>';
			}
			echo '</div>';
		}
		
	}
	TribeRelatedEvents::getInstance();
	
	
	// Declare some functions that other scripts can use.
	
	if ( !function_exists('tribe_get_related_events') ) {
		function tribe_get_related_events ($count=3) {
			return TribeRelatedEvents::getEvents( $count );
		}
	}
	
	if ( !function_exists('tribe_related_events') ) {
		function tribe_related_events ($title, $count=3, $thumbnails=false, $start_date=false) {
			return TribeRelatedEvents::displayEvents( $title, $count, $thumbnails, $start_date );
		}
	}
}