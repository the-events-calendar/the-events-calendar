<?php
/**
 * Builds and returns a mocked version of the context.
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

use Tribe__Context as Context;

/**
 * Class ContextMocker
 *
 * @package Tribe\Events\Views\V2
 */
class ContextMocker {

	/**
	 * An array of key/values that will be set on the built context.
	 *
	 * @var array
	 */
	protected $values = [];

	/**
	 * Whether the mocked context was ever built or not.
	 *
	 * This flag will be set to `true` even when the global context was not altered but a
	 * context was produced with the `get_context` method.
	 *
	 * @var bool
	 */
	protected $did_mock = false;

	/**
	 * An instance of the altered context this instance built, if any.
	 *
	 * @var Context
	 */
	protected $context;

	/**
	 * The previous version of the global context; saved before a global context replacement only.
	 *
	 * @var Context
	 */
	protected $previous_global_context;

	/**
	 * Sugar method to set a batch of keys at the same time.
	 *
	 * @param array $array An associative array of key values to set.
	 *
	 * @return \Tribe\Events\Views\V2\ContextMocker The mocker instance to allow for method chaining.
	 */
	public function with_args( array $array ): ContextMocker {
		foreach ( $array as $key => $value ) {
			if ( 'view' === $key ) {
				$this->for_view( $value );
				continue;
			}
			$this->set( $key, $value );

		}

		return $this;
	}

	/**
	 * Sugar method to set the `view` parameter on the context.
	 *
	 * @param string $view Either the view slug (as registered via the `tribe_events_views` filter), or
	 *                     the View class fully-qualified name.
	 *
	 * @return \Tribe\Events\Views\V2\ContextMocker The mocker instance to allow for method chaining.
	 */
	public function for_view( string $view ): ContextMocker {
		if ( class_exists( $view ) ) {
			$view_slug = class_exists( $view ) ? View::get_view_slug( $view ) : $view;
			if ( false === $view_slug ) {
				$message = "Currently no View is registered for the '{$view}' slug;";
				$message .= "\n\tDid you register the View in the `tribe_events_views` filter?";
				throw new \RuntimeException( $message );
			}
		} else {
			$view_slug = $view;
		}

		$this->set( 'view', $view_slug );

		return $this;
	}

	/**
	 * Sets a value on the context that will be built.
	 *
	 * @param string $key   The key to set.
	 * @param mixed  $value The value that will be assigned to the key.
	 *
	 * @return \Tribe\Events\Views\V2\ContextMocker The mocker instance to allow for method chaining.
	 */
	public function set( string $key, $value ): ContextMocker {
		$this->values[ $key ] = $value;

		return $this;
	}

	/**
	 * Returns whether the the instance did inject a context replacing the global one or not.
	 *
	 * @return bool Whether the the instance did inject a context replacing the global one or not.
	 */
	public function did_mock(): bool {
		return $this->did_mock;
	}

	/**
	 * Alters and replaces the global context.
	 *
	 * @since TBD
	 */
	public function alter_global_context() {
		$this->context = $this->get_context();
		$this->context->dangerously_set_global_context( array_keys( $this->values ) );
	}

	/**
	 * Builds and returns an altered version of the current global context.
	 *
	 * @return Context An altered clone of the current global Context.
	 */
	protected function get_context(): Context {
		$this->did_mock = true;

		return tribe_context()->alter( $this->values );
	}
}