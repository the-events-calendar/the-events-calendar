<?php

/**
 * Class TribeEventsPro_RecurrenceInstance
 */
class TribeEventsPro_RecurrenceInstance {
	private $parent_id = 0;
	private $start_date = NULL;
	private $post_id = 0;

	public function __construct( $parent_id, $start_date, $instance_id = 0 ) {
		$this->parent_id = $parent_id;
		$this->start_date = new DateTime('@'.$start_date);
		$this->post_id = $instance_id;
	}

	public function save() {
		$parent = get_post($this->parent_id);
		$post_to_save = get_object_vars($parent);
		unset($post_to_save['ID']);
		$post_to_save['post_parent'] = $parent->ID;
		unset($post_to_save['guid']);
		if ( !empty($this->post_id) ) {
			$post_to_save['ID'] = $this->post_id;
			//$post_to_save['guid'] = get_the_guid($this->post_id);
			$this->post_id = wp_update_post($post_to_save);
		} else {
			$this->post_id = wp_insert_post($post_to_save);
		}

		$duration = $this->get_duration();
		$end_date = $this->get_end_date();
		update_post_meta( $this->post_id, '_EventStartDate', $this->start_date->format(DateSeriesRules::DATE_FORMAT) );
		update_post_meta( $this->post_id, '_EventEndDate', $end_date->format(DateSeriesRules::DATE_FORMAT) );
		update_post_meta( $this->post_id, '_EventDuration', $duration );
		$this->copy_meta(); // everything else
		$this->set_terms();
	}

	public function get_id() {
		return $this->post_id;
	}

	public function get_duration() {
		return get_post_meta( $this->parent_id, '_EventDuration', true );
	}

	public function get_end_date() {
		$duration = $this->get_duration();
		$end_timestamp = (int)($this->start_date->format('U')) + $duration;
		return new DateTime('@'.$end_timestamp);
	}

	public function get_organizer() {
		$organizer = get_post_meta( $this->parent_id, '_EventOrganizerID', TRUE );
		if ( empty( $organizer) ) {
			return 0;
		}
		return (int)$organizer;
	}

	public function get_venue() {
		$venue = get_post_meta( $this->parent_id, '_EventVenueID', TRUE );
		if ( empty( $venue ) ) {
			return 0;
		}
		return (int)$venue;
	}

	private function copy_meta() {
		require_once('tribeeventspro-postmetacopier.php');
		$copier = new TribeEventsPro_PostMetaCopier();
		$copier->copy_meta($this->parent_id, $this->post_id);
	}

	private function set_terms() {
		$taxonomies = get_object_taxonomies(TribeEvents::POSTTYPE);
		foreach ( $taxonomies as $tax ) {
			$terms = wp_get_object_terms( $this->parent_id, $tax, array( 'fields' => 'ids' ) );
			wp_set_object_terms( $this->post_id, $terms, $tax );
		}
	}

}
 