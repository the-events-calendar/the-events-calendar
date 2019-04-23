<?php
/**
 * The interface all Views should implement.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

use Tribe__Context as Context;

/**
 * Interface View_Interface
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
interface View_Interface {

	/**
	 * Returns a View HTML code.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_html(  );

	/**
	 * Returns the view slug.
	 *
	 * The slug should be the one that will allow the view to be built by the View class by slug.
	 *
	 * @since TBD
	 *
	 * @return string The view slug.
	 */
	public function registration_slug(  );

	/**
	 * Returns the context instance the view will render from.
	 *
	 * Views that have not been explicitly assigned a Context will use, and return here,
	 * the global one.
	 *
	 * @since TBD
	 *
	 * @return Context The View current Context instance or the global context if the view
	 *                 has not been assigned a context.
	 */
	public function get_context();

	/**
	 * Sets, or unset, the View context.
	 *
	 * @since TBD
	 *
	 * @param \Tribe__Context|null $context Either a context instance or null to make the View use the global one.
	 */
	public function set_context( Context $context = null );

	/**
	 * Sets the View slug, usually the one it was registered with in the `tribe_events_views` filter.
	 *
	 * @since TBD
	 *
	 * @param string $slug The slug to set for the View instance.
	 */
	public function set_slug( $slug  );

	/**
	 * Returns a View slug, usually the one it was registered with in the `tribe_events_views` filter.
	 *
	 * @since TBD
	 *
	 * @return string The view slug, usually the one it was registered with in the `tribe_events_views` filter.
	 */
	public function get_slug( );

	/**
	 * Returns a View template class.
	 *
	 * @since TBD
	 *
	 * @return Template The template instance used by the View.
	 */
	public function get_template();

	/**
	 * Sets a view Template.
	 *
	 * @since TBD
	 *
	 * @param Template $template The template instance the View should use.
	 */
	public function set_template( Template $template );
}