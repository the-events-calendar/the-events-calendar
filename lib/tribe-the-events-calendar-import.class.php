<?php
class Tribe_The_Events_Calendar_Import {
	/**
	 * Will upgrade data from old free plugin to new plugin
	 *
	 */
	public function upgradeData() {
		$posts = self::getLegacyEvents();

		// we don't want the old event category
		$eventCat = get_term_by('name', Events_Calendar_Pro::CATEGORYNAME, 'category' );
		// existing event cats
		$existingEventCats = (array) get_terms(Events_Calendar_Pro::TAXONOMY, array('fields' => 'names'));
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
			wp_insert_term( $cat, Events_Calendar_Pro::TAXONOMY );
		}
		// now we know what we're in for
		$masterCats = get_terms( Events_Calendar_Pro::TAXONOMY, array('hide_empty'=>false) );

		// let's convert those posts
		foreach ( $posts as $post ) {
			// new post_type sir
			set_post_type( $post->ID, Events_Calendar_Pro::POSTTYPE );
			// set new events cats. we stored the array above, remember?
			if ( ! empty($post->cats) )
				wp_set_object_terms( $post->ID, $post->cats, Events_Calendar_Pro::TAXONOMY );
		}
	}
	
	public static function hasLegacyEvents() {
		return count( self::getLegacyEvents( 1 ) );
	}
	

	private static function getLegacyEvents( $number = -1 ) {
		$query = new WP_Query( array(
			'post_status' => 'any',
			'posts_per_page' => $number,
			'meta_key' => '_EventStartDate',
			'category_name' => Events_Calendar_Pro::CATEGORYNAME
		));
		return $query->posts;
	}


	private static function getCatNames( $cats ) {
		foreach ( $cats as $cat ) {
			$r[] = $cat->name;
		}
		return $r;
	}

	private static function mergeCatList ( $new, $old ) {
		$r = (array) self::getCatNames( $new );
		return array_merge( $r, $old );
	}

	private static function removeEventCat( $cats, $removeCat ) {

		foreach ( $cats as $k => $cat ) {
			if ( $cat->term_id == $removeCat->term_id ) {
				unset($cats[$k]);
			}
		}
		return $cats;
	}		
}
?>