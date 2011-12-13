<?php
/*
 Plugin Name: Tribe Related Posts
 Description: Template tags and shortcode to display related posts by taxonomy.
 Author: Modern Tribe, Inc., Paul Hughes
 Version: 1.0
 Author URI: http://tri.be
 */

if( !class_exists( 'TribeRelatedPosts' ) ) {

	class TribeRelatedPosts {
		
		private static $instance;
		private static $cache;

		/**
		 * Enforce Singleton Pattern
		 */
		public function getInstance() {
			if(null == self::$instance) {
				self::$instance = new TribeRelatedPosts;
			}
			return self::$instance;
		}

		public function __construct() {
			add_action('init', array($this, 'registerShortcodes'), 5);
			add_action('init', array($this, 'setUpThumbnails'), 5);
			self::$cache = array();
		}

		public function setUpThumbnails() {
			if (current_theme_supports('post-thumbnails')) {
				global $_wp_additional_image_sizes;
				if (!isset($_wp_additional_image_sizes['tribe-related-posts-thumbnail'])) {
					add_image_size('tribe-related-posts-thumbnail', 150, 100, true);
				}
			}
		}

		public function registerShortcodes() {
			add_shortcode('tribe-related-posts', array($this, 'shortcodeFeature'));
		}

		public function shortcodeFeature($atts, $content = null) {
			$defaults = array('tag'=>false, 'blog'=>false, 'count'=>5, 'only_display_related'=>false, 'thumbnails'=>true);
			$atts = shortcode_atts($defaults, $atts);
			return $this->displayPosts( $atts['tag'], $atts['count'], $atts['blog'], $atts['only_display_related'], $atts['thumbnails'] );
		}
		
		public function getPosts( $tags=array(), $count=5, $blog=false, $only_display_related=false ) {
			$post_id = get_the_ID();
			if (isset(self::$cache[$post_id])) {
				return self::$cache[$post_id];
			}
			if ( count($tags) = 0 ) {
				// get tag from current post.
				$posttags = get_the_tags(get_the_ID());
				// Abstract the list of slugs from the tags.
				if (is_array($posttags)) {
					foreach($posttags as $k => $v) {
						$tags[] = $v->slug;
					}
				}
			}

			if ( count($tags) > 0 ) {
				if ( $blog && !is_numeric($blog) ) { $blog = get_id_from_blogname( $blog ); }
				if ( $blog ) { switch_to_blog( $blog ); }
				$exclude = array($post_id);
				$args = array( 'tag' => join(',',$tags), 'numberposts' => $count, 'exclude' => $exclude );
				// filter the args
				$args = apply_filters( 'tribe-related-posts-args', $args );
				$posts = get_posts( $args );

				// If result count is not high enough, then find more unrelated posts to fill the extra slots
				if ($only_display_related==false && count($posts) < $count) {
					foreach ($posts as $post) {
						$exclude[] = $post->ID;
					}
					$args = array( 'numberposts' => ($count-count($posts)), 'exclude'=>$exclude );
					$args = apply_filters( 'tribe-related-posts-args-extra', $args );
					$posts = array_merge($posts, get_posts( $args ) );
				}
				if ( $blog ) { restore_current_blog(); }
				self::$cache[$post_id] = $posts;
			} else {
				self::$cache[$post_id] = array();
			}
			rewind_posts();
			return self::$cache[$post_id];
		}
		
		public function displayPosts( $tag=false, $count=5, $blog=false, $only_display_related=false, $thumbnails=false ) {
			$posts = self::getPosts( $tag, $count, $blog, $only_display_related );
			ob_start();
			if (is_array($posts) && count($posts)) {
				echo '<ul class="tribe-related-posts">';
				foreach ($posts as $post) {
					echo '<li>';
					if ($thumbnails) {
						$thumb = get_the_post_thumbnail($post->ID, 'tribe-related-posts-thumbnail' );
						if ($thumb) { echo '<div class="tribe-related-posts-thumbnail"><a href="'.get_permalink($post).'">'.$thumb.'</a></div>'; }
					}
					echo '<div class="tribe-related-posts-title"><a href="'.get_permalink($post).'">'.get_the_title($post).'</a></div>';
					echo '</li>';
				}
				echo '</ul>';
			}
			$output = ob_get_clean();
			// Filterable results.
			return apply_filters( 'tribe-related-posts-display', $output, $posts, $tag, $count, $blog, $only_display_related, $thumbnails );
		}
	}

    TribeRelatedPosts::getInstance();
	if ( !function_exists('get_related_posts') ) { 
		function get_related_posts( $tag=false, $count=5, $blog=false, $only_display_related=false ) {
			return TribeRelatedPosts::getPosts( $tag, $count, $blog, $only_display_related );
		}
	}
	if ( !function_exists('related_posts') ) { 
		function related_posts( $tag=false, $count=5, $blog=false, $only_display_related=false, $thumbnails=false ) {
			TribeRelatedPosts::displayPost( $tag, $count, $blog, $only_display_related, $thumbnails );
		}
	}
	if ( !function_exists('has_related_posts') ) { 
		function has_related_posts( $tag=false, $count=5, $blog=false, $only_display_related=false ) {
			$posts = get_related_posts( $tag, $count, $blog, $only_display_related );
			return count($posts) > 0;
		}
	}
}
?>