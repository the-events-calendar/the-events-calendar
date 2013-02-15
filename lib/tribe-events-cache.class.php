<?php

/**
 * Manage setting and expiring cached data
 *
 * Select actions can be used to force cached
 * data to expire. Implemented so far:
 *  - save_post
 *
 */
class TribeEventsCache {

	/**
	 * @param string $id
	 * @param mixed $value
	 * @param int $expiration
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function set( $id, $value, $expiration = 0, $expiration_trigger = '' ) {
		return wp_cache_set( $this->get_id($id, $expiration_trigger), $value, 'tribe-events', $expiration );
	}

	/**
	 * @param string $id
	 * @param string $expiration_trigger
	 *
	 * @return mixed
	 */
	public function get( $id, $expiration_trigger = '' ) {
		return wp_cache_get( $this->get_id($id, $expiration_trigger), 'tribe-events' );
	}

	/**
	 * @param string $id
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function delete( $id, $expiration_trigger = '' ) {
		return wp_cache_delete( $this->get_id($id, $expiration_trigger), 'tribe-events' );
	}

	/**
	 * @param string $key
	 * @param string $expiration_trigger
	 *
	 * @return string
	 */
	public function get_id( $key, $expiration_trigger = '' ) {
		$last = empty($expiration_trigger)?'':$this->get_last_occurrence($expiration_trigger);
		$id = $key.$last;
		if ( strlen($id) > 40 ) {
			$id = md5($id);
		}
		return $id;
	}

	/**
	 * @param string $action
	 *
	 * @return int
	 */
	public function get_last_occurrence( $action ) {
		return (int)get_option( 'tribe_last_'.$action, time() );
	}

	/**
	 * @param string $action
	 * @param int $timestamp
	 */
	public function set_last_occurrence( $action, $timestamp = 0 ) {
		if ( empty( $timestamp ) ) {
			$timestamp = time();
		}
		update_option( 'tribe_last_'.$action, (int)$timestamp );
	}
}

/**
 * Listen for events and update their timestamps
 */
class TribeEventsCacheListener {
	private static $instance = NULL;
	private $cache = NULL;

	public function __construct() {
		$this->cache = new TribeEventsCache();
	}

	public function init() {
		$this->add_hooks();
	}

	private function add_hooks() {
		add_action( 'save_post', array( $this, 'save_post' ), 0, 2 );
	}

	public function save_post( $post_id, $post ) {
		if ( in_array($post->post_type, TribeEvents::getPostTypes()) ) {
			$this->cache->set_last_occurrence( 'save_post' );
		}
	}

	/**
	 * For any hook that doesn't need any additional filtering
	 * 
	 * @param $method
	 * @param $args
	 */
	public function __call( $method, $args ) {
		$this->cache->set_last_occurrence( $method );
	}

	public static function instance() {
		if ( empty(self::$instance) ) {
			self::$instance = self::create_listener();
		}
		return self::$instance;
	}

	private static function create_listener() {
		$listener = new self();
		$listener->init();
		return $listener;
	}
}