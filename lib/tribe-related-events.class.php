<?php
/**
* Tribe Related Events
*
* Allows the user to created use a shortcode or widget to display events related
* to the current post. The shortcode is [related-events] and accepts
* "title", "count", "thumbnails", and "start_date" as arguments to accept a 
* title, the number of events to show, to show or not show thumbnails, and to show
* or not show the start date of each event, respectively.
*
* @author Paul Hughes
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

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
				if (!isset($_wp_additional_image_sizes['related-event-thumbnail'])) {
					add_image_size('related-event-thumbnail', 150, 100, true);
				}
			}
		}
		
		
		// Function to get the Related Events.
		public function getEvents( $count=3 ) {
			$post_id = get_the_ID();
			$event_taxonomy_name = TribeEvents::POSTTYPE;
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
					$events = get_posts( array( 'tag' => $tag->slug, 'posts_per_page' => $count, 'exclude' => $exclude, 'post_type' => $event_taxonomy_name, 'orderby' => 'rand' ) );
				}
			}
			return $events;
		}
		
		// Set up the code to display the events.
		public function displayEvents( $title, $count=3, $thumbnails=false, $start_date=false, $get_title=true ) {
			// Get the evemts.
			$events = self::getEvents( $count );
			
			echo '<div class="related-events-wrapper">';
			if ($get_title == true) {
				echo '<h2 class="related-events-title">'.$title .'</h2>';
			}
			// If events were returned, display them.
			if (is_array($events) && count($events)) {
				echo '<ul class="related-events">';
				foreach ($events as $event) {
					echo '<li>';
					// If thumbnail was requested, get and display it.
					if ($thumbnails) {
						if (has_post_thumbnail($event->ID)) {
							echo '<div class="related-event-thumbnail"><a href="' .get_permalink($event) .'">' .get_the_post_thumbnail($event->ID, 'related-event-thumbnail' ).'</a></div>';
						}
					}

					// If startdate was requested, get and display it.
					if ($start_date) {
						$date_format = 'M. jS';
						echo '<div class="related-event-date">' .tribe_get_start_date($event->ID, false, $date_format).'</div>';
					}
					
					// Display the other event information.
					echo '<div class="related-event-title"><a href="' .get_permalink($event) .'">' .get_the_title($event) .'</a></div>';
					echo '</li>';
				}
				echo '</ul>';
			} else {
				echo __('No Related Events', 'tribe-events-calendar-pro');
			}
			echo '</div>';
		}
		
	}
	TribeRelatedEvents::getInstance();
}