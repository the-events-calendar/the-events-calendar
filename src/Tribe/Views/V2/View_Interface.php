<?php
/**
 * The interface all Views should implement.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Interfaces\Repository_User_Interface;
use Tribe\Events\Views\V2\Interfaces\View_Url_Provider_Interface;
use Tribe__Context as Context;

/**
 * Interface View_Interface
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */
interface View_Interface  extends View_Url_Provider_Interface, Repository_User_Interface {

	/**
	 * Returns a View HTML code.
	 *
	 * @since 4.9.2
	 *
	 * @return string
	 */
	public function get_html();

	/**
	 * Returns a View label.
	 *
	 * @since  4.9.4
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Returns if this view is publicly visible by default. Which will make it show up
	 * on the events-bar of the views UI.
	 *
	 * @since 4.9.4
	 *
	 * @return bool
	 */
	public function is_publicly_visible();

	/**
	 * Returns the context instance the view will render from.
	 *
	 * Views that have not been explicitly assigned a Context will use, and return here,
	 * the global one.
	 *
	 * @since 4.9.2
	 *
	 * @return Context The View current Context instance or the global context if the view
	 *                 has not been assigned a context.
	 */
	public function get_context();

	/**
	 * Sets, or unset, the View context.
	 *
	 * @since 4.9.2
	 *
	 * @param \Tribe__Context|null $context Either a context instance or null to make the View use the global one.
	 */
	public function set_context( Context $context = null );

	/**
	 * Sets the View slug, usually the one it was registered with in the `tribe_events_views` filter.
	 *
	 * @since 4.9.2
	 *
	 * @param string $slug The slug to set for the View instance.
	 */
	public function set_slug( $slug );

	/**
	 * Returns a View slug, usually the one it was registered with in the `tribe_events_views` filter.
	 *
	 * @since 4.9.2
	 *
	 * @return string The view slug, usually the one it was registered with in the `tribe_events_views` filter.
	 */
	public function get_slug();

	/**
	 * Returns a View template class.
	 *
	 * @since 4.9.2
	 *
	 * @return Template The template instance used by the View.
	 */
	public function get_template();

	/**
	 * Sets a view Template.
	 *
	 * @since 4.9.2
	 *
	 * @param Template $template The template instance the View should use.
	 */
	public function set_template( Template $template );

	/**
	 * Sets up, by replacing the global query, the loop variables.
	 *
	 * The variables can be restored by using the `replace_the_loop` method.
	 *
	 * @since 4.9.3
	 *
	 * @param  array|null  $args An array of associative arguments used to setup the repository for the View.
	 *
	 */
	public function setup_the_loop( array $args = [] );

	/**
	 * Sets a View URL object either from some arguments or from the current URL.
	 *
	 * @since 4.9.3
	 *
	 * @param  array|null  $args An associative array of arguments that will be mapped to the corresponding query
	 *                           arguments by the View, or `null` to use the current URL.
	 */
	public function set_url( array $args = null, $merge = false );

	/**
	 * Returns the post IDs of the posts the View is displaying in the order it's displaying them.
	 *
	 * @since 4.9.4
	 *
	 * @return array An array of post IDs of the posts the view is currently displaying.
	 */
	public function found_post_ids();

	/**
	 * Returns the slug that should be used to find the View template.
	 *
	 * It's usually the same returned by the `View_Interface::get_slug` method but some Views might implement a
	 * different logic (e.g. the `/all` view).
	 *
	 * @since 4.9.5
	 *
	 * @return string The slug that should be used to find the View template.
	 */
	public function get_template_slug(  );

	/**
	 * Sets the View template slug.
	 *
	 * @since 4.9.5
	 *
	 * @param string $slug The slug the View should use to locate its template.
	 */
	public function set_template_slug( $slug );
}