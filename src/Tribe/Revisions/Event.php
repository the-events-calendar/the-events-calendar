<?php


/**
 * Class Tribe__Events__Revisions__Event
 *
 * Handles the saving operations of an event revision.
 *
 * @since 4.2.5
 */
class Tribe__Events__Revisions__Event extends Tribe__Events__Revisions__Post {

	/**
	 * @var Tribe__Events__Meta__Save
	 */
	protected $meta_save;

	/**
	 * Tribe__Events__Revisions__Event constructor.
	 *
	 * @param Tribe__Events__Meta__Save|null $meta_save
	 */
	public function __construct( WP_Post $post, Tribe__Events__Meta__Save $meta_save = null ) {
		parent::__construct( $post );
		$this->meta_save = $meta_save ? $meta_save : new Tribe__Events__Meta__Save( $this->post->ID, $this->post );
	}

	/**
	 * Saves the revision.
	 */
	public function save() {
		return $this->meta_save->save();
	}

}
