<?php
/**
 * Integration tests for request query vars sanitization.
 *
 * @since TBD
 *
 * @package TEC\Events\Tests\Integration\Events\Request
 */

/**
 * Class Query_Vars_Test
 *
 * @since TBD
 */
class Query_Vars_Test extends \Codeception\TestCase\WPTestCase {

    /**
     * Holds the Query_Vars instance to manage hooks lifecycle.
     *
     * @since TBD
     *
     * @var \TEC\Events\Request\Query_Vars
     */
    protected $query_vars;

    /**
     * Set up the test case.
     *
     * @since TBD
     */
    function setUp() {
        parent::setUp();

        $this->query_vars = new \TEC\Events\Request\Query_Vars();
        $this->query_vars->register();
    }

    /**
     * Tear down the test case.
     *
     * @since TBD
     */
    function tearDown() {
        remove_filter( 'request', [ $this->query_vars, 'sanitize_query_vars' ], 0 );

        parent::tearDown();
    }

    /**
     * It should leave vars unchanged when 'ical' is not present.
     *
     * @since TBD
     */
    public function test_it_leaves_unchanged_when_ical_not_present() {
        $vars   = [ 'foo' => 'bar' ];
        $result = apply_filters( 'request', $vars );

        $this->assertEquals( $vars, $result );
        $this->assertArrayNotHasKey( 'ical', $result );
    }

    /**
     * It should set ical to 1 when truthy values are provided.
     *
     * @since TBD
     * @dataProvider truthy_values_provider
     */
    public function test_it_sets_ical_to_one_when_truthy( $value ) {
        $_GET['ical']     = $value;
        $_POST['ical']    = $value;
        $_REQUEST['ical'] = $value;

        $vars   = [ 'ical' => $value ];
        $result = apply_filters( 'request', $vars );

        $this->assertArrayHasKey( 'ical', $result, 'Expected ical to be present for value: ' . var_export( $value, true ) );
        $this->assertSame( 1, $result['ical'], 'Expected ical to be 1 for value: ' . var_export( $value, true ) );
        $this->assertSame( 1, $_GET['ical'] );
        $this->assertSame( 1, $_POST['ical'] );
        $this->assertSame( 1, $_REQUEST['ical'] );
    }

    /**
     * It should remove ical when falsey or invalid values are provided.
     *
     * @since TBD
     * @dataProvider falsey_values_provider
     */
    public function test_it_unsets_ical_when_falsey_or_invalid( $value ) {
        $_GET['ical']     = $value;
        $_POST['ical']    = $value;
        $_REQUEST['ical'] = $value;

        $vars   = [ 'ical' => $value ];
        $result = apply_filters( 'request', $vars );

        $this->assertArrayNotHasKey( 'ical', $result, 'Expected ical to be removed for value: ' . var_export( $value, true ) );
        $this->assertArrayNotHasKey( 'ical', $_GET );
        $this->assertArrayNotHasKey( 'ical', $_POST );
        $this->assertArrayNotHasKey( 'ical', $_REQUEST );
    }

    /**
     * It should handle array input gracefully (take first value and sanitize).
     *
     * @since TBD
     * @dataProvider array_values_provider
     */
    public function test_it_handles_array_values( $input, $should_set, $expected_value ) {
        $_GET['ical']     = $input;
        $_POST['ical']    = $input;
        $_REQUEST['ical'] = $input;

        $vars   = [ 'ical' => $input ];
        $result = apply_filters( 'request', $vars );

        if ( $should_set ) {
            $this->assertArrayHasKey( 'ical', $result );
            $this->assertSame( $expected_value, $result['ical'] );
        } else {
            $this->assertArrayNotHasKey( 'ical', $result );
        }

        if ( $should_set ) {
            $this->assertArrayHasKey( 'ical', $_GET );
            $this->assertArrayHasKey( 'ical', $_POST );
            $this->assertArrayHasKey( 'ical', $_REQUEST );
            $this->assertSame( $expected_value, $_GET['ical'] );
            $this->assertSame( $expected_value, $_POST['ical'] );
            $this->assertSame( $expected_value, $_REQUEST['ical'] );
        } else {
            $this->assertArrayNotHasKey( 'ical', $_GET );
            $this->assertArrayNotHasKey( 'ical', $_POST );
            $this->assertArrayNotHasKey( 'ical', $_REQUEST );
        }
    }

    /**
     * Provides truthy values for the ical query var.
     *
     * @return array
     */
    public static function truthy_values_provider() {
        return [
            // Presence only (?ical)
            [ '' ],
            [ null ],
            [ '1' ],
            [ 1 ],
            [ true ],
            [ 'true' ],
            [ 'TRUE' ],
            [ 'yes' ],
            [ 'y' ],
            [ 'on' ],
        ];
    }

    /**
     * Provides falsey/invalid values for the ical query var.
     *
     * @return array
     */
    public static function falsey_values_provider() {
        return [
            [ '0' ],
            [ 0 ],
            [ false ],
            [ 'false' ],
            [ 'no' ],
            [ 'off' ],
            [ '' ],
            [ 'random-string' ],
        ];
    }

    /**
     * Provides array inputs and expectations.
     *
     * @return array
     */
    public static function array_values_provider() {
        return [
            // Truthy first element sets ical to 1.
            [ [ '1', '0' ], true, 1 ],
            // Falsey first element unsets ical.
            [ [ 'no', '1' ], false, null ],
        ];
    }
}


