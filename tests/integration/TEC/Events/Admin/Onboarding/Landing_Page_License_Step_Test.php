<?php
/**
 * Tests for the license activation step in the Setup Guide checklist, and for
 * the licensing questions behind it.
 *
 * @package TEC\Events\Admin\Onboarding
 * @since   TBD
 */

namespace TEC\Events\Admin\Onboarding;

use Codeception\TestCase\WPTestCase;
use RuntimeException;

/**
 * Class Landing_Page_License_Step_Test
 *
 * The step is driven by licensing state no test can arrange for real: whether
 * the bundled Harbor library has the activation URL API, and whether this domain
 * holds an activated license. Both live behind License_Data, so a stand-in bound
 * into the container covers every combination.
 *
 * @since TBD
 */
class Landing_Page_License_Step_Test extends WPTestCase {

	/**
	 * A stand-in for a URL built by Harbor's Activation_Url service.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected const ACTIVATION_URL = 'https://portal.example.com/subscriptions/?portal-referral=plugin';

	/**
	 * A stand-in for the portal's subscriptions screen.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected const PORTAL_URL = 'https://portal.example.com/subscriptions/';

	/**
	 * The Landing_Page instance.
	 *
	 * @since TBD
	 *
	 * @var Landing_Page
	 */
	protected $landing_page;

	/**
	 * Set up test environment.
	 *
	 * @before
	 *
	 * @since TBD
	 */
	public function before() {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->landing_page = tribe( Landing_Page::class );
	}

	/**
	 * Hand the container back a real License_Data, so a stand-in bound by one
	 * test cannot answer for the next.
	 *
	 * @after
	 *
	 * @since TBD
	 */
	public function restore_license_data() {
		tribe_singleton( License_Data::class, License_Data::class );
	}

	/**
	 * Bind a License_Data that reports the licensing state a test needs.
	 *
	 * Only the three questions that read the outside world are answered here.
	 * get_activation_url() is deliberately left alone so its real gating logic
	 * runs against these answers rather than being stubbed out with them.
	 *
	 * @since TBD
	 *
	 * @param string $activation_url The URL Harbor can build, or '' when it cannot.
	 * @param bool   $activated      Whether the calendar is activated on this site.
	 *
	 * @return void
	 */
	protected function bind_license_data( string $activation_url, bool $activated ): void {
		$stand_in = new class( $activation_url, $activated, self::PORTAL_URL ) extends License_Data {

			/**
			 * @var string
			 */
			private $activation_url;

			/**
			 * @var bool
			 */
			private $activated;

			/**
			 * @var string
			 */
			private $management_url;

			public function __construct( string $activation_url, bool $activated, string $management_url ) {
				$this->activation_url = $activation_url;
				$this->activated      = $activated;
				$this->management_url = $management_url;
			}

			public function can_build_activation_url(): bool {
				return '' !== $this->activation_url;
			}

			public function build_activation_url( string $return_url ): string {
				return $this->activation_url;
			}

			public function is_activated(): bool {
				return $this->activated;
			}

			public function get_management_url(): string {
				return $this->management_url;
			}
		};

		tribe_singleton( License_Data::class, $stand_in );
	}

	/**
	 * Render the checklist section and hand back its markup.
	 *
	 * @since TBD
	 *
	 * @param string $activation_url The URL Harbor can build, or '' when it cannot.
	 * @param bool   $activated      Whether the calendar is activated on this site.
	 *
	 * @return string The rendered markup.
	 */
	protected function render_checklist( string $activation_url, bool $activated ): string {
		$this->bind_license_data( $activation_url, $activated );

		ob_start();
		$this->landing_page->admin_content_checklist_section();

		return (string) ob_get_clean();
	}

	/**
	 * Pull the opening anchor tag pointing at a given URL out of rendered markup,
	 * so attributes can be asserted on that link alone rather than on the whole
	 * checklist, which carries plenty of other links.
	 *
	 * @since TBD
	 *
	 * @param string $markup The rendered markup.
	 * @param string $url    The URL the anchor points at, unescaped.
	 *
	 * @return string The opening tag.
	 */
	protected function open_tag_for( string $markup, string $url ): string {
		$pattern = '/<a\s[^>]*' . preg_quote( esc_url( $url ), '/' ) . '[^>]*>/';

		preg_match( $pattern, $markup, $matches );

		$this->assertNotEmpty( $matches, "No anchor found pointing at {$url}." );

		return $matches[0];
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function it_should_offer_the_activation_link_when_the_license_is_not_activated() {
		$output = $this->render_checklist( self::ACTIVATION_URL, false );

		$this->assertStringContainsString( 'tec-events-onboarding-wizard-license-item', $output );
		$this->assertStringContainsString( 'License activated', $output );
		$this->assertStringContainsString( 'Activate license', $output );
		$this->assertStringContainsString( esc_url( self::ACTIVATION_URL ), $output );
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function it_should_not_mark_the_step_complete_when_the_license_is_not_activated() {
		$output = $this->render_checklist( self::ACTIVATION_URL, false );

		$this->assertNotRegExp(
			'/id="tec-events-onboarding-wizard-license-item"[^>]*onboarding-step--completed/',
			$output
		);
	}

	/**
	 * The step stays on show once the license is activated, marked complete, so
	 * the user sees a finished checklist rather than one that quietly shrinks.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_mark_the_step_complete_once_activated() {
		$output = $this->render_checklist( self::ACTIVATION_URL, true );

		$this->assertStringContainsString( 'tec-events-onboarding-wizard-license-item', $output );
		$this->assertStringContainsString( 'License activated', $output );
		$this->assertRegExp(
			'/id="tec-events-onboarding-wizard-license-item"[^>]*onboarding-step--completed/',
			$output
		);
	}

	/**
	 * There is nothing left to activate once the license is live, so the step
	 * points at the portal rather than back through the activation flow.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_swap_the_link_for_license_management_once_activated() {
		$output = $this->render_checklist( self::ACTIVATION_URL, true );

		$this->assertStringContainsString( 'Manage license', $output );
		$this->assertStringContainsString( esc_url( self::PORTAL_URL ), $output );
		$this->assertStringNotContainsString( 'Activate license', $output );
		$this->assertStringNotContainsString( esc_url( self::ACTIVATION_URL ), $output );
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function it_should_not_offer_license_management_before_activation() {
		$output = $this->render_checklist( self::ACTIVATION_URL, false );

		$this->assertStringNotContainsString( 'Manage license', $output );
	}

	/**
	 * Managing a license has no return trip, so the page has to stay open behind
	 * it. Activating does have one, and sending that round trip to a tab the user
	 * has left behind would strand them on a stale step.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_open_only_the_management_link_in_a_new_tab() {
		$activating = $this->open_tag_for( $this->render_checklist( self::ACTIVATION_URL, false ), self::ACTIVATION_URL );
		$activated  = $this->open_tag_for( $this->render_checklist( self::ACTIVATION_URL, true ), self::PORTAL_URL );

		$this->assertStringNotContainsString( 'target=', $activating );
		$this->assertStringContainsString( 'target="_blank"', $activated );
		$this->assertStringContainsString( 'rel="nofollow noopener"', $activated );
	}

	/**
	 * Both destinations leave WordPress, so both carry the external-link
	 * affordance every other off-site link on this page uses.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_mark_both_destinations_as_external() {
		foreach ( [ false, true ] as $activated ) {
			$output = $this->render_checklist( self::ACTIVATION_URL, $activated );

			$this->assertStringContainsString( 'tec-admin-page__link--external', $output );
		}
	}

	/**
	 * Activating is the first thing a user should do, so the step leads the list.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_place_the_license_step_first_in_the_list() {
		$output = $this->render_checklist( self::ACTIVATION_URL, false );

		$license_position = strpos( $output, 'tec-events-onboarding-wizard-license-item' );
		$views_position   = strpos( $output, 'tec-events-onboarding-wizard-views-item' );

		$this->assertNotFalse( $license_position, 'The license step should be rendered.' );
		$this->assertNotFalse( $views_position, 'The calendar views step should be rendered.' );
		$this->assertLessThan( $views_position, $license_position );
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function it_should_omit_the_step_when_harbor_cannot_build_a_url() {
		$output = $this->render_checklist( '', false );

		$this->assertStringNotContainsString( 'tec-events-onboarding-wizard-license-item', $output );
		$this->assertStringNotContainsString( 'License activated', $output );
		$this->assertStringNotContainsString( 'Activate license', $output );
	}

	/**
	 * An activated license on a site whose Harbor is too old to build a URL. The
	 * step could arguably show as complete, but there is nowhere to send the user
	 * and no way to refresh what is shown, so it stays out of the list entirely.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_omit_the_step_when_activated_but_harbor_cannot_build_a_url() {
		$output = $this->render_checklist( '', true );

		$this->assertStringNotContainsString( 'tec-events-onboarding-wizard-license-item', $output );
		$this->assertStringNotContainsString( 'License activated', $output );
		$this->assertStringNotContainsString( 'Manage license', $output );
		$this->assertRegExp( '#\d+/5 steps completed#', $output );
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function it_should_count_the_step_in_the_total_when_it_is_shown() {
		$output = $this->render_checklist( self::ACTIVATION_URL, false );

		$this->assertRegExp( '#\d+/6 steps completed#', $output );
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function it_should_leave_the_total_alone_when_the_step_is_hidden() {
		$output = $this->render_checklist( '', false );

		$this->assertRegExp( '#\d+/5 steps completed#', $output );
	}

	/**
	 * An activated license is a completed step, so it has to move the tally too.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_count_an_activated_license_as_a_completed_step() {
		$incomplete = $this->render_checklist( self::ACTIVATION_URL, false );
		$complete   = $this->render_checklist( self::ACTIVATION_URL, true );

		preg_match( '#(\d+)/6 steps completed#', $incomplete, $before );
		preg_match( '#(\d+)/6 steps completed#', $complete, $after );

		$this->assertNotEmpty( $before, 'The incomplete render should report a tally out of 6.' );
		$this->assertNotEmpty( $after, 'The complete render should report a tally out of 6.' );
		$this->assertSame( (int) $before[1] + 1, (int) $after[1] );
	}

	/**
	 * The wizard button is gated by get_activation_url(). These pin the two ways
	 * it can come back empty, and most of all that it does so on a site which has
	 * already activated.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_give_the_wizard_a_url_while_there_is_something_to_activate() {
		$this->bind_license_data( self::ACTIVATION_URL, false );

		$this->assertSame(
			self::ACTIVATION_URL,
			tribe( License_Data::class )->get_activation_url( 'https://example.com/return' )
		);
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function it_should_hide_the_wizard_button_once_the_license_is_activated() {
		$this->bind_license_data( self::ACTIVATION_URL, true );

		$this->assertSame( '', tribe( License_Data::class )->get_activation_url( 'https://example.com/return' ) );
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function it_should_hide_the_wizard_button_when_harbor_cannot_build_a_url() {
		$this->bind_license_data( '', false );

		$this->assertSame( '', tribe( License_Data::class )->get_activation_url( 'https://example.com/return' ) );
	}

	/**
	 * The cheap gate short-circuits before any licensing state is read. A
	 * stand-in that explodes if asked proves the ordering, not just the result.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_not_read_licensing_state_when_no_url_can_be_built() {
		$stand_in = new class extends License_Data {

			public function can_build_activation_url(): bool {
				return false;
			}

			public function is_activated(): bool {
				throw new RuntimeException( 'Licensing state should not be read when no URL can be built.' );
			}
		};

		tribe_singleton( License_Data::class, $stand_in );

		$this->assertSame( '', tribe( License_Data::class )->get_activation_url( 'https://example.com/return' ) );
	}

	/**
	 * The portal's base URL carries no trailing slash, so the path has to supply
	 * its own. Guards against that assumption drifting and producing a double
	 * slash, or the path being dropped altogether.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_point_license_management_at_the_subscriptions_screen() {
		$url = ( new License_Data() )->get_management_url();

		if ( '' === $url ) {
			$this->markTestSkipped( 'The bundled Harbor library predates the portal Config class.' );
		}

		$this->assertStringStartsWith( 'http', $url );
		$this->assertStringEndsWith( '/subscriptions/', $url );
		$this->assertStringNotContainsString( '//subscriptions/', $url );
	}
}
