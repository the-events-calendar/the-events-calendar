<?php

/**
 * Class Tribe_Events_Recurrence_Instance
 */
class Tribe_Events_Recurrence_Instance {
	private $parent_id = 0;
	private $start_date = NULL;
	private $post_id = 0;

	public function __construct( $parent_id, $start_date ) {
		$this->parent_id = $parent_id;
		$this->start_date = new DateTime('@'.$start_date);
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
		update_post_meta( $this->post_id,'_EventStartDate', $this->start_date->format(DateSeriesRules::DATE_FORMAT) );
		update_post_meta( $this->post_id,'_EventEndDate', $end_date->format(DateSeriesRules::DATE_FORMAT) );
		update_post_meta( $this->post_id,'_EventDuration', $duration );
	}

	public function get_id() {
		return $this->post_id;
	}

	public function get_duration() {
		return get_post_meta( $this->parent_id, '_EventDuration', true );
	}

	public function get_end_date() {
		$duration = $this->get_duration();
		$end_timestamp = $this->start_date->getTimestamp() + $duration;
		return new DateTime('@'.$end_timestamp);
	}
}
 