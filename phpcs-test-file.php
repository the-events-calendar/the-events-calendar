<?php
// Missing `/**` style docblock for file.
// Missing end-of comment punctuation

// Missing `/**` style comment for class.
class phpcsTest { // Incorrect camel-case class name. Class name must begin with a capital letter.
	public function no_docblock() {
		// Missing `/**` style docblock for function.
	}

	/**
	 * We don't use camelCase in PHP.
	 */
	public function camelCaseFunction() {} // Camel-case function name.

	/**
	 * Should trigger an incorrect array syntax issue.
	 */
	public function incorrect_array_syntax() {
		$foo = array( 'foo' => 'bar', 'bar' => 'foo' ); // Incorrect long-syntax array.

	}

	/**
	 * Should trigger a missing params issue.
	 * All params need to be in the docblock.
	 */
	public function missing_params_in_docblock( $foo ) {
		$foo = 'bar';
	}

	/**
	 * Should trigger a params missing comment/description issue.
	 *
	 * @param string $foo Some string.
	 * @param string $bar
	 */
	public function params_missing_description_in_docblock( $foo, $bar ) {
		$foo = $bar;
	}

	/**
	 * Should trigger a params missing type issue.
	 * This will get reported as "missing parameter name" as it reads the name as the type.
	 *
	 * @param $foo Some string.
	 * @param $bar Another string.
	 */
	public function params_missing_type_in_docblock( $foo, $bar ) {
		$foo = $bar;
	}

	/**
	 * Should trigger a params missing name issue.
	 * This will also get reported as missing the parameters entirely.
	 *
	 * @param string Some string.
	 * @param string Another string.
	 */
	public function params_missing_name_in_docblock( $foo, $bar ) {
		$foo = $bar;
	}

	/**
	 * Should trigger a missing return issue.
	 * All non-void returns need to be in the docblock.
	 */
	public function missing_return_in_docblock() {
		return 'bar';
	}

	/**
	 * Should trigger a mismatched return issue.
	 * The return type in the docblock needs to match the actual return type.
	 *
	 * @return array
	 */
	public function mismatched_return_in_docblock() {
		return 'bar';
	}

	/**
	 * Should trigger a mismatched return issue.
	 * The return type in the docblock needs to match the type cast.
	 * @return string
	 */
	public function mismatched_return_in_typecast(): array {
		return 'bar';
	}

	/**
	 * Should trigger a whitespace issue.
	 * We use tabs, not spaces.
	 */
	public function using_spaces_instead_of_tabs() {
	    return 'bar';
	}

	/**
	 * Should NOT trigger a Yoda conditional issue.
	 * We no longer require these.
	 *
	 * @param string $foo Some string.
	 */
	public function no_more_yoda( $foo ) {
		if ( $foo !== 'bar' ) {
			$foo = 'bar';
		}

		return $foo;
	}

	/**
	 * Visibility MUST be declared on all properties and methods.
	 */
	function missing_visibilty() {
		return 'foo';
	}

	/**
	 * The soft limit on line length MUST be 120 characters;
	 * automated style checkers MUST warn but MUST NOT error at the soft limit.
	 */
	public function line_soft_limit() {
		$foo = 'The soft limit on line length MUST be 120 characters; automated style checkers MUST warn but MUST NOT error at the soft limit.';
	}
}

