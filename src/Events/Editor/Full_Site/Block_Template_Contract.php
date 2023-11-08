<?php

namespace TEC\Events\Editor\Full_Site;

use WP_Block_Template;

/**
 * Interface Block_Template_Contract
 */
interface Block_Template_Contract {

	/**
	 * The Block ID.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function id(): string;

	/**
	 * The Block slug.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug(): string;

	/**
	 * Our namespace for a set of blocks.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_namespace() : string;

	/**
	 * The getter for this template service to retrieve a hydrated WP_Block_Template.
	 *
	 * @since TBD
	 *
	 * @return WP_Block_Template|null
	 */
	public function get_block_template(): ?WP_Block_Template;

	/**
	 * Handles rendering the template.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function render(): string;
}