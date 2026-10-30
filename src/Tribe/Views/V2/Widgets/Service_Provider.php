<?php
/**
 * The main service provider for the version 2 of the Widgets.
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

use Tribe\Events\Views\V2\Views\Widgets\Widget_List_View;
use Tribe\Events\Views\V2\Views\Widgets\Widget_QR_Code_View;
use TEC\Common\Contracts\Service_Provider as Provider_Contract;
use WP_Widget;


/**
 * Class Service_Provider
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Service_Provider extends Provider_Contract {


	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.2.1
	 */
	public function register() {
		// Determine if V2 views are loaded.
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		// Determine if V2 widgets should load.
		if ( ! tribe_events_widgets_v2_is_enabled() ) {
			return;
		}

		// These hooks always run to provide widget compatibility for v1 to v2 and reverse.
		$this->register_compatibility();

		$this->hook();
		$this->register_assets();
	}


	/**
	 * Registers the provider handling all assets for widgets v2.
	 *
	 * @since 5.5.0
	 */
	protected function register_assets() {
		$assets = new Assets( $this->container );
		$assets->register();

		$this->container->singleton( Assets::class, $assets );
	}

	/**
	 * Registers the provider handling for compatibility hooks.
	 *
	 * @since 5.3.0
	 */
	protected function register_compatibility() {
		$compatibility = new Compatibility();
		$this->container->singleton( Compatibility::class, $compatibility );
		$this->container->singleton( 'events.views.v2.widgets.compatibility', $compatibility );

		add_action( 'tribe_plugins_loaded', [ $compatibility, 'switch_compatibility' ] );
		add_filter( 'option_sidebars_widgets', [ $compatibility, 'remap_list_widget_id_bases' ] );

		add_filter( 'rest_pre_dispatch', [ $this, 'enable_widget_copy_paste' ], 10, 3 );
		add_filter( 'rest_dispatch_request', [ $this, 'enable_saving_widget_copied' ], 10, 3 );
	}

	/**
	 * Function used to attach the hooks associated with this class.
	 *
	 * @since 5.2.1
	 */
	public function hook() {
		add_filter( 'tribe_widgets', [ $this, 'register_widget' ] );
		add_filter( 'tribe_events_views', [ $this, 'add_views' ] );
	}

	/**
	 * Add the widgets to register with WordPress.
	 *
	 * @since 5.2.1
	 *
	 * @param array<string,string> $widgets An array of widget classes to register.
	 *
	 * @return array<string,string> An array of registered widget classes.
	 */
	public function register_widget( $widgets ) {
		$widgets[ Widget_List::get_widget_slug() ]    = Widget_List::class;
		$widgets[ Widget_QR_Code::get_widget_slug() ] = Widget_QR_Code::class;

		return $widgets;
	}

	/**
	 * Enable widget copy paste for the Legacy Widgets that we are registering.
	 *
	 * @since 6.11.1
	 * @since 6.17.4.1 Sign and store the sanitized instance instead of the submitted bytes.
	 * @since TBD Leave correctly signed instances alone and re-sign only the widget's own sanitized settings.
	 *
	 * @param mixed           $result  The result of the rest request.
	 * @param WP_REST_Server  $server  The REST server.
	 * @param WP_REST_Request $request The REST request.
	 *
	 * @return mixed The result of the rest request.
	 */
	public function enable_widget_copy_paste( $result, $server, $request ) {
		// Bail if result is already set.
		if ( null !== $result ) {
			return $result;
		}

		// Get the route being requested.
		$route = $request->get_route();

		// Check if this matches our target endpoint.
		if ( ! preg_match( '#^/wp/v2/widget-types/([a-zA-Z0-9_-]+)/(?:encode|render)$#', $route, $matches ) ) {
			return $result;
		}

		// Bail if the widget type ID is not set.
		if ( ! isset( $matches[1] ) ) {
			return $result;
		}

		// Get the widget type ID from the route.
		$widget_type_id = $matches[1];

		// Bail if the widget is not a tribe widget.
		if ( ! str_starts_with( $widget_type_id, 'tribe-widget-' ) ) {
			return $result;
		}

		// Bail if the widget instance is not set.
		if ( ! isset( $request['instance']['encoded'], $request['instance']['hash'] ) ) {
			return $result;
		}

		global $wp_widget_factory;

		$widget_object = $wp_widget_factory->get_widget_object( $widget_type_id );

		// Bail if the widget object is not found.
		if ( ! $widget_object ) {
			return $result;
		}

		$new_instance = $this->get_signed_widget_instance( $widget_object, $request['instance'] );
		if ( null === $new_instance ) {
			return $result;
		}

		$request->set_param( 'instance', $new_instance );

		return $result;
	}

	/**
	 * Enable widget copy paste for the Legacy Widgets that we are registering.
	 *
	 * @since 6.11.2
	 * @since 6.17.4.1 Sign and store the sanitized instance instead of the submitted bytes.
	 * @since TBD Leave correctly signed instances alone and re-sign only the widget's own sanitized settings.
	 *
	 * @param mixed           $result  The result of the rest request.
	 * @param WP_REST_Request $request The REST request.
	 * @param string          $route   The route of the request.
	 *
	 * @return mixed The result of the rest request.
	 */
	public function enable_saving_widget_copied( $result, $request, $route ) {
		// Bail if result is already set.
		if ( null !== $result ) {
			return $result;
		}

		// Bail if the widget type ID is not a string.
		if ( ! is_string( $route ) ) {
			return $result;
		}

		// Check if this matches our target endpoint.
		if ( ! str_starts_with( $route, '/wp/v2/widgets' ) ) {
			return $result;
		}

		$widget_type_id = $request->get_param( 'id_base' );

		// Bail if the widget type ID is not a string.
		if ( ! is_string( $widget_type_id ) ) {
			return $result;
		}

		// Bail if the widget is not a tribe widget.
		if ( ! str_starts_with( $widget_type_id, 'tribe-widget-' ) ) {
			return $result;
		}

		// Bail if the widget instance is not set.
		if ( ! isset( $request['instance']['encoded'], $request['instance']['hash'] ) ) {
			return $result;
		}

		global $wp_widget_factory;

		$widget_object = $wp_widget_factory->get_widget_object( $widget_type_id );

		// Bail if the widget object is not found.
		if ( ! $widget_object ) {
			return $result;
		}

		$new_instance = $this->get_signed_widget_instance( $widget_object, $request['instance'] );
		if ( null === $new_instance ) {
			return $result;
		}

		$request->set_param( 'instance', $new_instance );

		return $result;
	}

	/**
	 * Enable widget copy paste for the Legacy Widgets that we are registering.
	 *
	 * @since 6.11.2
	 * @since 6.17.4.1 Sign and store the sanitized instance instead of the submitted bytes.
	 * @deprecated TBD Block data is no longer re-signed on render; only instances the site signed are rendered.
	 *
	 * @param array $parsed_block The parsed block.
	 *
	 * @return array The parsed block, unchanged.
	 */
	public function enable_rendering_widget_copied( $parsed_block ) {
		_deprecated_function( __METHOD__, 'TBD' );

		return $parsed_block;
	}

	/**
	 * Returns a sanitized copy of a copied widget instance, or null when it cannot be trusted.
	 *
	 * The instance is rebuilt from a strict decode and re-serialized, so only plain,
	 * expected data is ever stored and signed.
	 *
	 * @since 6.17.3
	 * @since 6.17.3.1 Only accept instances that unserialize to a plain array.
	 * @since 6.17.4.1 Return the sanitized instance instead of a boolean.
	 *
	 * @param string $serialized The decoded widget instance.
	 *
	 * @return string|null The sanitized serialized instance, or null when not safe.
	 */
	protected function get_safe_widget_instance( $serialized ) {
		if ( ! is_string( $serialized ) ) {
			return null;
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		$data = @unserialize( $serialized, [ 'allowed_classes' => false ] );

		if ( ! is_array( $data ) || $this->contains_object( $data ) ) {
			return null;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		return serialize( $data );
	}

	/**
	 * Recursively determine whether a value holds any object.
	 *
	 * @since 6.17.3
	 * @since 6.17.4.1 Bail out on overly nested input.
	 *
	 * @param mixed $data  The value to inspect.
	 * @param int   $depth Current recursion depth.
	 *
	 * @return bool Whether an object was found, or the input is too deeply nested.
	 */
	protected function contains_object( $data, $depth = 0 ) {
		if ( $depth > 20 ) {
			return true;
		}

		if ( is_object( $data ) ) {
			return true;
		}

		if ( is_array( $data ) ) {
			foreach ( $data as $value ) {
				if ( $this->contains_object( $value, $depth + 1 ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Add the widget views to the view manager.
	 *
	 * @since 5.2.1
	 *
	 * @param array<string,string> $views An associative array of views in the shape `[ <slug> => <class> ]`.
	 *
	 * @return array<string,string> $views The modified array of views in the shape `[ <slug> => <class> ]`.
	 */
	public function add_views( $views ) {
		$views[ Widget_List_View::get_view_slug() ]    = Widget_List_View::class;
		$views[ Widget_QR_Code_View::get_view_slug() ] = Widget_QR_Code_View::class;

		return $views;
	}

	/**
	 * Returns a copied widget instance signed for this site, or null to leave it as submitted.
	 *
	 * An instance the site already signed is left alone. Any other instance is rebuilt through
	 * the widget's own `update()`, so only its settings, sanitized as on a normal save, are signed.
	 *
	 * @since TBD
	 *
	 * @param WP_Widget                            $widget_object The widget the instance belongs to.
	 * @param array{encoded: string, hash: string} $instance      The submitted instance; values are checked to be strings.
	 *
	 * @return array{encoded: string, hash: string}|null The instance to use, or null to leave the request unchanged.
	 */
	private function get_signed_widget_instance( WP_Widget $widget_object, array $instance ): ?array {
		if ( ! is_string( $instance['encoded'] ) || ! is_string( $instance['hash'] ) ) {
			return null;
		}

		$serialized = base64_decode( $instance['encoded'] );

		if ( hash_equals( wp_hash( $serialized ), $instance['hash'] ) ) {
			return null;
		}

		if ( ! $widget_object instanceof Widget_Abstract ) {
			return null;
		}

		$safe_instance = $this->get_safe_widget_instance( $serialized );
		if ( null === $safe_instance ) {
			return null;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		$data     = unserialize( $safe_instance, [ 'allowed_classes' => false ] );
		$settings = $widget_object->update( array_merge( $widget_object->get_default_arguments(), $data ), [] );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		$signed = serialize( $settings );

		$instance['encoded'] = base64_encode( $signed );
		$instance['hash']    = wp_hash( $signed );

		return $instance;
	}
}
