<?php
// Missing `/**` style docblock for file.
// Based on https://docs.theeventscalendar.com/developer/code-standards/php/.
// This serves to "test our tests" AND to ensure we have test coverage for every internal rule.

// Missing `/**` style comment for class.
class phpcsTest
{















	// Incorrect camel-case class name. Class name must begin with a capital letter.
	public function no_docblock()
    {
        // Missing `/**` style docblock for function.
    }

    // Missing end-of comment punctuation

    /**
     * We don't use camelCase in PHP.
     */
    public function camelCaseFunction()
    {
    } // Camel-case function name.

    /**
     * Should trigger an incorrect array syntax issue.
     */
    public function incorrect_array_syntax()
    {
        $foo = [
        'foo' => 'bar',
        'bar' => 'foo',
        ]; // Incorrect long-syntax array.

        $foo = [
            'foo' => 'bar',
            'bar' => 'foo',
        ]; // Also incorrect long-syntax array.
    }

    /**
     * Should trigger a missing params issue.
     * All params need to be in the docblock.
     */
    public function missing_params_in_docblock( $foo )
    {
        $foo = 'bar';
    }

    /**
     * Should trigger a params missing comment/description issue.
     *
     * @param string $foo Some string.
     * @param string $bar
     */
    public function params_missing_description_in_docblock( $foo, $bar )
    {
        $foo = $bar;
    }

    /**
     * Should trigger a params missing type issue.
     * This will get reported as "missing parameter name" as it reads the name as the type.
     *
     * @param $foo Some string.
	 * @param string $bar Another string.
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
	 * The return type in the docblock needs to match the method type cast.
	 *
	 * @return string
	 */
	public function mismatched_return_in_typecast(): array {
		return [];
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
	function missing_visibility() {
		return 'foo';
	}

	/**
	 * The soft limit on line length MUST be 120 characters;
	 * automated style checkers MUST warn but MUST NOT error at the soft limit.
	 */
	public function line_soft_limit() {
		$foo = 'The soft limit on line length MUST be 120 characters; automated style checkers MUST warn but MUST NOT error at the soft limit.';
	}

	/**
	 * The PHP constants true, false, and null MUST be in lowercase.
	 */
	public function lowercase_constants() {
		$foo = NULL;
		$bar = FALSE;
		$baz = TRUE;
	}

	/**
	 * Method names SHOULD NOT be prefixed with a single underscore to indicate protected or private visibility.
	 */
	protected function _no_underscore_for_visibility() {
		$foo = '';
	}

	/**
	 * Method names MUST NOT be declared with a space after the method name.
	 * Fixed as this causes phpcs to throw an internal error on PHP 8
	 */
	public function no_space_after_method_name() {
		$foo = '';
	}

	/**
	 * Argument lists MAY be split across multiple lines, where each subsequent line is indented once.
	 *
	 * @param string $foo Some string.
	 * @param string $bar Another string.
	 * @param string $baz Yet another string.
	 * @param string $booze A final string.
	 */
	public function multi_line_no_indent(
	$foo,
	$bar,
	$baz,
	$booze
	) {
		// do stuff.
	}

	/**
	 * Argument lists MAY be split across multiple lines.
	 * When doing so, the first item in the list MUST be on the next line.
	 *
	 * @param string $foo Some string.
	 * @param string $bar Another string.
	 * @param string $baz Yet another string.
	 * @param string $booze A final string.
	 */
	public function multi_line_first_param( $foo,
		$bar,
		$baz,
		$booze
	) {
		// do stuff.
	}

	/**
	 * Argument lists MAY be split across multiple lines.
	 * When doing so, there MUST be only one argument per line.
	 *
	 * @param string $foo Some string.
	 * @param string $bar Another string.
	 * @param string $baz Yet another string.
	 * @param string $booze A final string.
	 */
	public function multi_line_param_per_line(
		$foo, $bar,
		$baz, $booze
	) {
		// do stuff.
	}

	/**
	 * When the argument list is split across multiple lines,
	 * the closing parenthesis and opening brace MUST be placed together on their own line.
	 *
	 * @param string $foo Some string.
	 * @param string $bar Another string.
	 */
	public function multi_line_closing_line(
		$foo,
		$bar ) {
		// do stuff.
	}

	/**
	 * When the argument list is split across multiple lines,
	 * the closing parenthesis and opening brace MUST be placed together with one space between them.
	 *
	 * @param string $foo Some string.
	 * @param string $bar Another string.
	 */
	public function multi_line_closing_space(
		$foo,
		$bar
	){
		// do stuff.
	}

	/**
	 * Argument lists MAY be split across multiple lines, where each subsequent line is indented once.
	 */
	public function bad_argument_indentation() {
		return $this->star_wars(
		'IV',
		'Greedo',
		'Rodian',
		'DT-12'
		);
	}

	/**
	 * Argument lists MAY be split across multiple lines.
	 * When doing so, the first item in the list MUST be on the next line,
	 * and there MUST be only one argument per line
	 */
	public function bad_argument_per_line() {
		return $this->star_wars(
			'IV', 'Greedo',
			'Rodian', 'DT-12'
		);
	}

	/**
	 * Function to call for tests above.
	 */
	public function star_wars(
		$episode,
		$character,
		$species,
		$weapon
	) {
		// do stuff.
	}

	/**
	 * Conditionals MAY be split across multiple lines, where each subsequent line is indented once.
	 * When doing so, the first conditional in the list MUST be on the next line, and there MUST be only one conditional per line.
	 */
	public function multiline_control_structures() {
		$a = $b = $c = $d = $q = $banana_pancake = 'foo'; // phpcs:ignore-line

		// alignment beyond a single tab can be fraught with differences between devs.
		if ( $a == $b
			 && $b == $c
		) {
			$banana_pancake = false;
		}

		// this one causes needless churn in Git as conditionals are added to the control structure.
		if (
			$a == $b &&
			$b == $c
		) {
			$banana_pancake = false;
		}

		// since we use tabs, we could be bitten by the tab/space nature of alignment here.
		if (
		   $a == $b
		&& $b == $c
		) {
			$banana_pancake = false;
		}

		// something a bit more complex - this violates the 1 conditional per line rule
		if (
			( $a == $b || $a == $c )
			&& $b == $d
			&& $q == $banana_pancake
		) {
			$banana_pancake = false;
		}
	}
}

// Should trigger "no closing tag for pure PHP files".
?>
