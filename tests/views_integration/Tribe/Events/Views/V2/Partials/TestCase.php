<?php
/**
 * The common test case for testing partials.
 *
 * @since   TBD
 * @package Tribe\Events\Views\V2\Partials
 */

namespace Tribe\Events\Views\V2\Partials;


use Codeception\TestCase\WPTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\Template;
use Tribe\Events\Views\V2\View;

class TestCase extends WPTestCase {
	use MatchesSnapshots;

	/**
	 * The path, relative to the Views v2 views root folder, to the partial.
	 * Extending test classes must override this.
	 *
	 * @var string
	 */
	protected $partial_path;

	/**
	 * The Template instance used to load and render partials.
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * Sets up the partial test case checking and refreshing its properties.
	 */
	public function setUp() {
		parent::setUp();
		if ( empty( $this->partial_path ) ) {
			throw new \RuntimeException( 'Any test case extending [' . __CLASS__ . '] must define the `$partial_path` property.' );
		}

		if ( preg_match( '/\\.php$/', $this->partial_path ) ) {
			throw new \RuntimeException( 'The `$partial_path` property should not contain the `.php` extension.' );
		}

		add_filter('tribe_events_views', static function(array $views = []){
			$views['partials'] = new class extends View{};
			return $views;
		});
		$this->template = new Template( 'partials' );
	}

	/**
	 * Renders the partial and returns its HTML.
	 *
	 * @param array $context An array that will be passed to the partial to render.
	 *
	 * @return string The partial rendered HTML.
	 */
	protected function get_partial_html( array $context = []) {
		return $this->template->template( $this->partial_path, $context );
	}
}
