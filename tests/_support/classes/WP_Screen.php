<?php
namespace Tribe\Events\Test;

/**
 * Class WP_Screen
 *
 * A dummy \WP_Screen implementation as the original one is final.
 * Injection is made using the `__construct` method.
 *
 * @package Tribe\Events\Test
 */
class WP_Screen {

	/**
	 * @var bool
	 */
	private $in_admin;

	/**
	 * WP_Screen constructor.
	 *
	 * @param array $vars An associative array of key values to inject.
	 */
	public function __construct( array $vars = array() ) {
		foreach ($vars as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function in_admin() {
		return $this->in_admin;
	}
}
