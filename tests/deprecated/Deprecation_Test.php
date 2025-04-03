<?php
/**
 * Tests for deprecated functions.
 *
 * This test case will not require modification when new methods are added to a deprecation trait or new
 * deprecation traits are added. As lons as the deprecation traits are in the `src/deprecated/Traits` directory
 * and follow the `<class>_Deprecated.php` naming convention, this test will automatically pick them up.
 */

use Codeception\TestCase\WPTestCase;
use PHPUnit\Framework\AssertionFailedError;


class Expected_Deprecation_Signal extends Exception {
}

class Deprecation_Test extends WPTestCase {
	private string $plugin_version;
	private string $plugin_major_version;

	/**
	 * @before
	 */
	public function set_plugin_versions(): void {
		$plugin_version = Tribe__Events__Main::VERSION;
		$this->plugin_version = $plugin_version;
		$this->plugin_major_version = substr( $plugin_version, 0, strpos( $plugin_version, '.' ) );
	}

	public function deprecated_methods_and_versions_data_provider(): Generator {
		foreach ( glob( dirname( __DIR__, 2 ) . '/src/deprecated/Traits/*_Deprecated.php' ) as $file ) {
			$trait = basename( $file, '.php' );
			$methods = ( new ReflectionClass( $trait ) )->getMethods( ReflectionMethod::IS_PUBLIC );
			foreach ( $methods as $method ) {
				$doc_block = $method->getDocComment();
				$method_name = $method->getName();
				if ( ! preg_match( '/@deprecated\s+([0-9.]+)/', $doc_block, $matches ) ) {
					throw new RuntimeException(
						'Could not find @deprecated version number in doc block for ' . $trait . '::' . $method_name
					);
				}
				$version = $matches[1];
				$class = str_replace( '_Deprecated', '', $trait );

				yield sprintf( '%s::%s', $trait, $method->name ) => [
					$method->isStatic(),
					$class,
					$method_name,
					$version
				];
			}
		}
	}

	/**
	 * @dataProvider deprecated_methods_and_versions_data_provider
	 */
	public function test_deprecated_methods_and_versions( bool $is_static, string $class, string $method, string $version ) {
		// Let's avoid building costly things.
		$instance = ( new ReflectionClass( $class ) )->newInstanceWithoutConstructor();
		// Work out the arguments.
		$args = ( new ReflectionMethod( $class, $method ) )->getParameters();
		$args = array_map( static function ( ReflectionParameter $arg ) {
			return $arg->isDefaultValueAvailable() ? $arg->getDefaultValue() : null;
		}, $args );
		// Trigger the deprecation error, check it and then bail out of the function.
		add_filter( 'deprecated_function_trigger_error', '__return_true' );
		// Work out the plugin major version now, to make sure methods are deprecated in the right version.
		add_action( 'deprecated_function_run', $this->get_test_closure( $class, $method, $version ), 10, 3 );
		set_error_handler( static function () {
			// Throw to just get out of the function before any real logic runs.
			throw new Expected_Deprecation_Signal();
		}, E_USER_DEPRECATED );

		try {
			if ( $is_static ) {
				$class::$method( ...$args );
			} else {
				$instance->{$method}( ...$args );
			}
		} catch ( Expected_Deprecation_Signal $e ) {
			// Do nothing, this is expected.
		}
	}

	/**
	 * Returns the Closure that should be hooked to the `deprecated_function_run` action to test the deprecation.
	 *
	 * @param string $class   The fully-qualified class name of the deprecated method.
	 * @param string $method  The name of the deprecated method.
	 * @param string $version The version the method was deprecated in.
	 *
	 * @return Closure The Closure that should be hooked to the `deprecated_function_run` action to test the
	 *                 deprecation.
	 */
	protected function get_test_closure( string $class, string $method, string $version ): Closure {
		return function ( $function, $replacement, $deprecation_version )
		use (
			$class,
			$method,
			$version
		) {
			$this->assertContains( $class . '::' . $method, $function );
			$this->assertEquals( $version, $deprecation_version );
			$deprecation_major_version = substr( $deprecation_version, 0, strpos( $deprecation_version, '.' ) );
			$this->assertEquals(
				$this->plugin_major_version,
				$deprecation_major_version,
				"Deprecation version $deprecation_version of $class::$method is not the same major " .
				"version as the plugin ($this->plugin_version); should this be removed?."
			);
		};
	}

	private function eval_class_template( string $deprecation_version ): string {
		$class_name = 'Deprecation_Test_Class_' . md5( microtime() );

		$class_code = <<<PHP
class $class_name {
	/**
	 * @deprecated $deprecation_version
	 */
	public function deprecated_instance_method() {
		_deprecated_function(
			'$class_name::deprecated_instance_method',
			'$deprecation_version',
			'some_function'
		);
	}

	/**
	 * @deprecated $deprecation_version
	 */
	public function deprecated_static_method() {
		_deprecated_function(
			'$class_name::deprecated_static_method',
			'$deprecation_version',
			'some_static_function'
		);
	}
}
PHP;

		eval( $class_code );

		return $class_name;
	}


	public function major_version_diff_test_data_provider(): Generator {
		$current_plugin_major_version = substr( Tribe__Events__Main::VERSION, 0, strpos( Tribe__Events__Main::VERSION, '.' ) );

		$deprecation_version = $current_plugin_major_version - 1 . '.0.0';
		$class_name = $this->eval_class_template( $deprecation_version  );
		yield 'deprecation major lt plugin major, instance method' => [
			$class_name,
			false,
			'deprecated_instance_method',
			$deprecation_version,
			true
		];

		yield 'deprecation major lt plugin major, static method' => [
			$class_name,
			false,
			'deprecated_static_method',
			$deprecation_version,
			true
		];

		$deprecation_version = $current_plugin_major_version . '.0.0';
		$class_name = $this->eval_class_template( $deprecation_version );
		yield 'deprecation major same as plugin major, instance method' => [
			$class_name,
			false,
			'deprecated_instance_method',
			$deprecation_version,
			false
		];

		yield 'deprecation major same as plugin major, static method' => [
			$class_name,
			false,
			'deprecated_static_method',
			$deprecation_version,
			false
		];

		$deprecation_version = $current_plugin_major_version . '.2389.8923';
		$class_name = $this->eval_class_template( $deprecation_version );
		yield 'deprecation major same as plugin major, diff minor, instance method' => [
			$class_name,
			false,
			'deprecated_instance_method',
			$deprecation_version,
			false
		];

		yield 'deprecation major same as plugin major, diff minor, static method' => [
			$class_name,
			false,
			'deprecated_static_method',
			$deprecation_version,
			false
		]
		;

		$deprecation_version = $current_plugin_major_version + 1 . '.0.0';
		$class_name = $this->eval_class_template( $deprecation_version );
		yield 'deprecation major gt plugin major, instance method' => [
			$class_name,
			false,
			'deprecated_instance_method',
			$deprecation_version,
			true
		];

		yield 'deprecation major gt plugin major, static method' => [
			$class_name,
			false,
			'deprecated_static_method',
			$deprecation_version,
			true
		];
	}

	/**
	 * @dataProvider major_version_diff_test_data_provider
	 */
	public function test_major_version_check( string $class, bool $is_static, string $method, string $version, bool $expect_exception ): void {
		if ( $expect_exception ) {
			$this->expectException( AssertionFailedError::class );
		}

		$this->test_deprecated_methods_and_versions( $is_static, $class, $method, $version );
	}
}
