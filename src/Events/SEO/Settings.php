<?php
/**
 * SEO & URL Handling settings for The Events Calendar.
 *
 * Registers a "SEO & URL Handling" sub-tab in the Display settings tab,
 * exposing options that control how TEC handles URL parameters and SEO
 * directives to reduce duplicate-content crawl problems.
 *
 * @since 6.16.5
 *
 * @package TEC\Events\SEO
 */

namespace TEC\Events\SEO;

use Tribe__Settings_Tab;
use Tribe\Events\Admin\Settings as Admin_Settings;
use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Plain_Text;
use TEC\Common\Admin\Entities\H3;
use Tribe\Utils\Element_Classes;
use TEC\Common\Admin\Entities\Paragraph;

/**
 * Class Settings
 *
 * Manages the admin-facing settings for TEC SEO & URL Handling.
 *
 * @since 6.16.5
 *
 * @package TEC\Events\SEO
 */
class Settings {

	/**
	 * Option key: behaviour when a dated event-list URL falls outside the
	 * range of known events (before earliest or after latest).
	 *
	 * Accepted values:
	 *   'hard_404'    — return HTTP 404 Not Found (default, recommended for SEO).
	 *   'soft_noindex' — return HTTP 200 with a noindex robots directive instead.
	 *
	 * @since 6.16.5
	 *
	 * @var string
	 */
	public const OPT_OUT_OF_RANGE_BEHAVIOR = 'tec_seo_out_of_range_behavior';

	/**
	 * Option key: whether to add a noindex directive to any List view URL that
	 * carries the ?tribe-bar-date query parameter.
	 *
	 * The canonical tag already points to the base /events/list/ URL, so these
	 * parameterised URLs add no independent SEO value.
	 *
	 * @since 6.16.5
	 *
	 * @var string
	 */
	public const OPT_NOINDEX_DATED_LIST_URLS = 'tec_seo_noindex_dated_list_urls';

	/**
	 * Option key: whether to return HTTP 404 when a URL specifies a view slug
	 * (via ?eventDisplay) that is currently disabled in TEC settings.
	 *
	 * @since 6.16.5
	 *
	 * @var string
	 */
	public const OPT_DISABLED_VIEW_404 = 'tec_seo_disabled_view_404';

	/**
	 * Identifier used to register the sub-tab inside the Display tab.
	 *
	 * @since 6.16.5
	 *
	 * @var string
	 */
	public static string $tab_slug = 'display-seo-settings';

	/**
	 * Registers the hooks needed to inject the settings tab.
	 *
	 * @since 6.16.5
	 *
	 * @return void
	 */
	public function add_hooks(): void {
		add_action( 'tec_events_settings_tab_display', [ $this, 'register_tab' ] );
	}

	/**
	 * Removes the hooks added by add_hooks().
	 *
	 * @since 6.16.5
	 *
	 * @return void
	 */
	public function unregister_hooks(): void {
		remove_action( 'tec_events_settings_tab_display', [ $this, 'register_tab' ] );
	}

	/**
	 * Creates the "SEO & URL Handling" sub-tab and attaches it to the Display tab.
	 *
	 * @since 6.16.5
	 *
	 * @param Tribe__Settings_Tab $display_tab The parent Display settings tab object.
	 *
	 * @return void
	 */
	public function register_tab( Tribe__Settings_Tab $display_tab ): void {
		// Only show on the TEC events settings page, not the Tickets settings page.
		if ( ! tribe( Admin_Settings::class )->is_tec_events_settings() ) {
			return;
		}

		$tab = new Tribe__Settings_Tab(
			self::$tab_slug,
			esc_html__( 'SEO & URL Handling', 'the-events-calendar' ),
			[
				'priority' => 5.40,
				'fields'   => apply_filters(
					'tec_events_settings_display_seo_section',
					$this->generate_settings()
				),
			]
		);

		/**
		 * Fires after the SEO & URL Handling settings tab has been created, allowing
		 * add-ons or site-specific code to attach additional fields or sub-tabs.
		 *
		 * @since 6.16.5
		 *
		 * @param Tribe__Settings_Tab $tab The SEO settings tab instance.
		 */
		do_action( 'tec_events_settings_tab_display_seo', $tab );

		$display_tab->add_child( $tab );
	}

	/**
	 * Builds the settings fields array for the SEO & URL Handling tab.
	 *
	 * @since 6.16.5
	 *
	 * @return array<string,mixed> Settings fields keyed by option name.
	 */
	public function generate_settings(): array {
		$title_block = new Div(
			new Element_Classes( [ 'tec-settings-form__header-block', 'tec-settings-form__header-block--horizontal' ] )
		);

		$title_block->add_child(
			new H3(
				_x( 'SEO & URL Handling', 'SEO settings section header', 'the-events-calendar' ),
				new Element_Classes( 'tec-settings-form__section-header' )
			)
		);

		$title_block->add_child(
			( new Paragraph( new Element_Classes( 'tec-settings-form__section-description' ) ) )->add_children(
				[
					new Plain_Text(
						esc_html__( 'Manage SEO and URL handling settings to prevent duplicate-content crawl problems.', 'the-events-calendar' )
					),
				]
			)
		);

		$header = [
			'tec-events-seo-settings-header' => $title_block,
		];

		$fields = [
			'tec-events-seo-settings-section-header' => ( new Div( new Element_Classes( [ 'tec-settings-form__header-block' ] ) ) )->add_children(
				[
					new H3(
						_x( 'URL & Crawl Protection', 'SEO settings sub-section header', 'the-events-calendar' ),
						new Element_Classes( [ 'tec-settings-form__section-header' ] )
					),
					new Plain_Text(
						esc_html__( 'Control how The Events Calendar handles URL parameters and robots directives to reduce duplicate-content crawl problems.', 'the-events-calendar' )
					),
				]
			),
			self::OPT_OUT_OF_RANGE_BEHAVIOR          => [
				'type'            => 'radio',
				'label'           => esc_html__( 'Out-of-range date URL behavior', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'What to do when a visitor or search engine requests an event listing page with a date that falls before the earliest event or after the latest event on record.', 'the-events-calendar' ),
				'default'         => 'hard_404',
				'validation_type' => 'options',
				'options'         => [
					'hard_404'     => esc_html__( 'Return HTTP 404 Not Found — recommended for SEO, prevents these URLs from being indexed.', 'the-events-calendar' ),
					'soft_noindex' => esc_html__( 'Return HTTP 200 with a noindex directive — softer behaviour, the events page is still shown to visitors.', 'the-events-calendar' ),
				],
			],
			self::OPT_NOINDEX_DATED_LIST_URLS        => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Add noindex to dated List view URLs', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Adds a noindex robots directive to any List view URL that carries a ?tribe-bar-date query parameter. The canonical tag already points to the base /events/list/ URL, so these parameterised URLs produce duplicate content. Disable if you intentionally expose dated list views as standalone pages.', 'the-events-calendar' ),
				'default'         => true,
				'validation_type' => 'boolean',
			],
			self::OPT_DISABLED_VIEW_404              => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Return 404 for disabled view URLs', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Returns HTTP 404 when a URL specifies a view (e.g. ?eventDisplay=photo) that is currently disabled under Events > Settings > Display. This prevents accumulation of disabled-view URLs in search indexes.', 'the-events-calendar' ),
				'default'         => true,
				'validation_type' => 'boolean',
			],
		];

		$fields = tribe( 'settings' )->wrap_section_content( 'tec-events-settings-seo', $fields );

		return array_merge( $header, $fields );
	}
}
