<?php
/**
 * Handles the integration of the Custom Tables v1 implementation with the Advanced Custom Fields plugin.
 *
 * @since   6.0.11
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations\ACF;
 */

namespace TEC\Events\Custom_Tables\V1\Integrations\ACF;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Controller.
 *
 * @since   6.0.11
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations\ACF;
 */
class Controller extends Service_Provider {
	/**
	 * The priority at which the ACF field handling will start.
	 *
	 * @since 6.0.11
	 */
	public const EARLY_PRIORITY = 0;

	/**
	 * The priority at which the ACF field handling will end.
	 *
	 * @since 6.0.11
	 */
	public const LATE_PRIORITY = 1000;

	/**
	 * The priority at which the ACF field handling will start for AJAX queries.
	 *
	 * @since 6.0.11
	 */
	public const AJAX_QUERY_PRIORITY = 10;

	/**
	 * A flag property indicating whether an ACF field of the supported type is being rendered or not.
	 *
	 * @since 6.0.11
	 *
	 * @var bool
	 */
	private bool $rendering_acf_field = false;

	/**
	 * Returns the list of supported field types.
	 *
	 * @since 6.0.11
	 *
	 * @return array<string> The list of supported field types.
	 */
	public static function get_supported_field_types(): array {
		return [
			'post_object',
			'relationship',
		];
	}

	/**
	 * Registers the implementations, actions and filters required by the Custom Tables implementation to work with
	 * the Advanced Custom Fields plugin.
	 *
	 * @since 6.0.11
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		foreach ( self::get_supported_field_types() as $supported_field_type ) {
			// Detect the start of a specific ACF field type to boot the handling.
			add_action( 'acf/render_field/type=' . $supported_field_type, [
				$this,
				'start_acf_handling'
			], self::EARLY_PRIORITY, 0 );
			// Hook again reasonably late to stop the handling.
			add_action( 'acf/render_field/type=' . $supported_field_type, [
				$this,
				'stop_acf_handling'
			], self::LATE_PRIORITY, 0 );
		}

		// Hook to redirect queries in the context of AJAX queries. The below filter is used only in AJAX context.
		add_filter( 'acf/fields/post_object/query', [
			$this,
			'redirect_post_ajax_query'
		], self::AJAX_QUERY_PRIORITY, 2 );

		// Register a dedicated query modified implementation, pull the flag from the provider.
		$this->container->bind(
			Query_Modifier::class, fn() => ( new Query_Modifier() )->set_handling( $this->rendering_acf_field )
		);
		add_filter( 'tec_events_custom_tables_v1_query_modifier_implementations', [
			$this,
			'add_query_modifier_implementation'
		] );
	}

	/**
	 * Removes the actions and filters added by the provider.
	 *
	 * @since 6.0.11
	 *
	 * @return void Actions and filters are removed.
	 */
	public function unregister() {
		// Unhook the same action twice for each supported field type; see the `register` method.
		foreach ( self::get_supported_field_types() as $supported_field_type ) {
			remove_action( 'acf/render_field/type=' . $supported_field_type, [
				$this,
				'start_acf_handling'
			], self::EARLY_PRIORITY );
			remove_action( 'acf/render_field/type=' . $supported_field_type, [
				$this,
				'stop_acf_handling'
			], self::LATE_PRIORITY );
		}

		remove_filter( 'tec_events_custom_tables_v1_query_modifier_implementations', [
			$this,
			'add_query_modifier_implementation'
		] );
		remove_filter( 'acf/fields/post_object/query', [
			$this,
			'redirect_post_ajax_query'
		], self::AJAX_QUERY_PRIORITY );

		$this->rendering_acf_field = false;
	}

	/**
	 * Raises the flag indicating that an ACF field of the supported type is being rendered.
	 *
	 * @since 6.0.11
	 *
	 * @return void The method does not return anything, the flag is raised.
	 */
	public function start_acf_handling(): void {
		$this->rendering_acf_field = true;
	}

	/**
	 * Lowers the flag indicating that an ACF field of the supported type is being rendered.
	 *
	 * @since 6.0.11
	 *
	 * @return void The method does not return anything, the flag is lowered.
	 */
	public function stop_acf_handling(): void {
		$this->rendering_acf_field = false;
	}

	/**
	 * Adds the query modifier implementation to the list of implementations.
	 *
	 * @since 6.0.11
	 *
	 * @param array<string> $implementations The list of implementations of the `Query_Modifier_Interface`.
	 *
	 * @return array<string> The filtered list of `Query_Modifier_Interface` implementations.
	 */
	public function add_query_modifier_implementation( $implementations ) {
		if ( ! is_array( $implementations ) ) {
			return $implementations;
		}

		// Build it now and hold a reference to it.
		$implementations[] = Query_Modifier::class;

		return $implementations;
	}

	/**
	 * Redirect supported type queries to the custom tables.
	 *
	 * Note: the method hooks, but never unhooks, to the `acf/fields/post_object/query` filter.
	 * The is because there is not action/filter to hook to that is fired when the AJAX query is done,
	 * and because the request will send the JSON data and die.
	 *
	 * @since 6.0.11
	 *
	 * @param array<string,mixed> $args  The input query argument, left unmodified.
	 * @param array<string,mixed> $field The field data.
	 *
	 * @return array<string,mixed> The query arguments, left unmodified.
	 */
	public function redirect_post_ajax_query( $args, $field ) {
		if ( ! (
			is_array( $args )
			&& is_array( $field )
			&& isset( $field['type'] )
			&& in_array( $field['type'], self::get_supported_field_types(), true ) )
		) {
			return $args;
		}
		$this->start_acf_handling();

		return $args;
	}
}
