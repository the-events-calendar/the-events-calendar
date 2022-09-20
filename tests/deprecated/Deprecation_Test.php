<?php
/**
 * Tests for deprecated functions.
 *
 * This test case will not require modification when new methods are added to a deprecation trait or new
 * deprecation traits are added. As lons as the deprecation traits are in the `src/deprecated/Traits` directory
 * and follow the `<class>_Deprecated.php` naming convention, this test will automatically pick them up.
 */

use Codeception\TestCase\WPTestCase;

class Expected_Deprecation_Signal extends Exception {
}

class Deprecation_Test extends WPTestCase {

	public function deprecated_methods_and_versions_data_provider(): Generator {
		foreach ( glob( dirname( __DIR__, 2 ) . '/src/deprecated/Traits/*_Deprecated.php' ) as $file ) {
			$trait = basename( $file, '.php' );
			$methods = ( new ReflectionClass( $trait ) )->getMethods( ReflectionMethod::IS_PUBLIC );
			foreach ( $methods as $method ) {
				$doc_block = $method->getDocComment();
				$name = $method->getName();
				if ( ! preg_match( '/@deprecated\s+([0-9.]+)/', $doc_block, $matches ) ) {
					throw new RuntimeException(
						'Could not find @deprecated version number in doc block for ' . $trait . '::' . $name
					);
				}
				$version = $matches[1];
				$class = str_replace( '_Deprecated', '', $trait );

				yield sprintf( '%s::%s', $trait, $method->name ) => [ $method->isStatic(), $class, $name, $version ];
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
		add_action( 'deprecated_function_run', function ( $function, $replacement, $deprecation_version ) use ( $class, $method, $version ) {
			$this->assertContains( $class . '::' . $method, $function );
			$this->assertEquals( $version, $deprecation_version );
		}, 10, 3 );
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
}
