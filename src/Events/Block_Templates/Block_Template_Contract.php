<?php

namespace TEC\Events\Block_Templates;

use WP_Block_Template;

/**
 * Interface Block_Template_Contract
 *
 * @since 6.3.3 Moved and decoupled from Block API requirements, focusing on Template requirements.
 * @since 6.2.7
 */
interface Block_Template_Contract {
	/**
	 * Which is the name/slug of this template block.
	 *
	 * @since 6.3.3
	 *
	 * @return string
	 */
	public function slug();

	/**
	 * The Block ID.
	 *
	 * @since 6.2.7
	 *
	 * @return string
	 */
	public function id(): string;

	/**
	 * The getter for this template service to retrieve a hydrated WP_Block_Template.
	 *
	 * @since 6.2.7
	 *
	 * @return WP_Block_Template|null
	 */
	public function get_block_template(): ?WP_Block_Template;
}