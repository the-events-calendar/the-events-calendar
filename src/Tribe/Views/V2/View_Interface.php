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
	 * @since 4.9.11 Made the method static.
	 *
	 * @return bool
	 */
	public static function is_publicly_visible();

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
	 * Returns all the parent views that the current class as an array of slugs.
	 *
	 * @since 4.9.13
	 *
	 * @return string[] Array of string with the slugs of all the registered views.
	 */
	public function get_parents_slug();

	/**
	 * Returns all html classes for the view instance we are handling.
	 *
	 * @since 4.9.13
	 *
	 * @param array $classes  Array of classes that are going to be appended to this instance.
	 *
	 * @return string[]       Array of string with the classes used
	 */
	public function get_html_classes( array $classes = [] );

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
	 * @param array|null $args   An associative array of arguments that will be mapped to the corresponding query
	 *                           arguments by the View, or `null` to use the current URL.
	 * @param bool       $merge  Whether to merge the arguments or override them.
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

	/**
	 * Returns the View template variables, as they would be set up and filtered before rendering the template.
	 *
	 * @since 4.9.7
	 *
	 * @return array An associative array of the View template variables.
	 */
	public function get_template_vars();

	/**
	 * Returns the URL to show the View for today.
	 *
	 * @since 4.9.8
	 *
	 * @param bool $canonical Whether to return the canonical, pretty, version of the URL or not; default to `false`.
	 *
	 * @return string
	 */
	public function get_today_url( $canonical = false );

	/**
	 * Returns an array of user-facing messages the View will display on the front-end.
	 *
	 * @since 4.9.11
	 *
	 * @return array An array of user-facing messages the View will display on the front-end.
	 */
	public function get_messages();

	/**
	 * Returns the URL to get the View for a date and a set of arguments.
	 *
	 * @since 4.9.13
	 *
	 * @param string|int|\DateTimeInterface $date       The date to return the URL for.
	 * @param array|string                  $query_args The query string or arguments to append to the URL.
	 *
	 * @return string The URL to fetch the View for a date.
	 */
	public function url_for_query_args( $date = null, $query_args = null );
}
