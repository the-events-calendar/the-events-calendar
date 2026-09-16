<?php

namespace TEC\Events\Hooks;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Calendar_Embeds\Admin\List_Page;
use TEC\Events\Calendar_Embeds\Admin\Singular_Page;
use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use TEC\Events\Calendar_Embeds\Frontend;
use TEC\Events\Category_Colors\Admin\Controller as Category_Colors_Admin_Controller;
use TEC\Events\Integrations\Plugins\Elementor\Controller as Elementor_Controller;
use TEC\Events\Integrations\Themes\Avada\Provider as Avada_Provider;
use TEC\Events\SEO\Headers\Controller as SEO_Headers_Controller;
use Tribe\Events\Integrations\WP_Rocket;
use Tribe\Events\Views\V2\Hooks as Views_V2_Hooks;
use Tribe\Events\Views\V2\Template\Title as Views_V2_Title;
use Tribe__Events__Admin_List;
use Tribe__Events__Integrations__WPML__Language_Switcher;
use TypeError;
use WP_Query;

/**
 * A callback attached to a filter we do not own must never constrain what that filter
 * can hand it.
 *
 * WordPress passes a filter's value through every callback in the chain, so the value
 * that reaches ours is whatever core started with *and* whatever the plugins ahead of us
 * returned. A scalar type declaration on such a callback turns another plugin's sloppy
 * value into an uncaught TypeError that takes down the whole request, before our code
 * gets a chance to ignore it.
 *
 * Reported as SMTNC-2761: SG Optimizer's purge queue calls wp_remote_get( null, ... ),
 * WP_Http::request() forwards that null to `pre_http_request`, and
 * Harbor\PUE::filter_pre_http_request()'s `string $url` fatals on it.
 *
 * @see https://linear.app/nexcess/issue/SMTNC-2761
 */
class Core_Filter_Callback_Types_Test extends WPTestCase {

	/**
	 * Every callback this plugin attaches to a filter it does not own, paired with the
	 * wrong-typed value that filter can realistically deliver.
	 *
	 * @return array<string,array{0:callable,1:array<mixed>}>
	 */
	public function foreign_filter_callback_provider(): array {
		$query = new WP_Query();

		return [
			'the_content: null content'                      => [
				[ tribe( Frontend::class ), 'overwrite_content' ],
				[ null ],
			],
			'embed_template: null template'                  => [
				[ tribe( Frontend::class ), 'overwrite_embed_template' ],
				[ null ],
			],
			'the_content: array content (Elementor)'         => [
				[ tribe( Elementor_Controller::class ), 'disable_blocks_on_display' ],
				[ [] ],
			],
			'submenu_file: array file (list page)'           => [
				[ tribe( List_Page::class ), 'keep_parent_menu_open' ],
				[ [] ],
			],
			'submenu_file: array file (singular page)'       => [
				[ tribe( Singular_Page::class ), 'keep_parent_menu_open' ],
				[ [] ],
			],
			'get_terms: count int and null taxonomies'       => [
				[ tribe( Calendar_Embeds::class ), 'modify_term_count_on_term_list_table' ],
				[ 5, null ],
			],
			'wp_insert_post_data: null data'                 => [
				[ tribe( Calendar_Embeds::class ), 'disable_slug_changes' ],
				[ null, [], [], false ],
			],
			'manage_edit-tax_columns: null columns'          => [
				[ tribe( Category_Colors_Admin_Controller::class ), 'add_columns' ],
				[ null ],
			],
			'avada_options_sections: null sections'          => [
				[ tribe( Avada_Provider::class ), 'append_settings_notice' ],
				[ null ],
			],
			'pre_handle_404: null preempt'                   => [
				[ tribe( SEO_Headers_Controller::class ), 'prevent_list_view_paged_404' ],
				[ null, $query ],
			],
			'posts_clauses: null clauses (event date sort)'  => [
				[ Tribe__Events__Admin_List::class, 'sort_by_event_date' ],
				[ null, $query ],
			],
			'posts_clauses: null clauses (taxonomy sort)'    => [
				[ Tribe__Events__Admin_List::class, 'sort_by_tax' ],
				[ null, $query ],
			],
			'posts_clauses: null clauses (aggregator)'       => [
				[ Tribe__Events__Admin_List::class, 'filter_by_aggregator_record' ],
				[ null, $query ],
			],
			'icl_ls_languages: null languages'               => [
				[ new Tribe__Events__Integrations__WPML__Language_Switcher(), 'filter_icl_ls_languages' ],
				[ null ],
			],
			'rocket_excluded_inline_js_content: null list'   => [
				[ new WP_Rocket(), 'filter_excluded_inline_js_concat' ],
				[ null ],
			],
			'query_vars: null vars'                          => [
				[ tribe( Views_V2_Hooks::class ), 'filter_query_vars' ],
				[ null ],
			],
			'document_title_parts: null parts'               => [
				[ tribe( Views_V2_Title::class ), 'filter_document_title_parts' ],
				[ null ],
			],
		];
	}

	/**
	 * @test
	 * @dataProvider foreign_filter_callback_provider
	 *
	 * @param callable     $callback The callback attached to the foreign filter.
	 * @param array<mixed> $args     The arguments that filter can deliver.
	 */
	public function should_not_fatal_when_a_foreign_filter_delivers_an_unexpected_type( callable $callback, array $args ): void {
		try {
			$callback( ...$args );
		} catch ( TypeError $e ) {
			$this->fail(
				sprintf(
					'A callback attached to a filter we do not own rejected a value that filter can deliver: %s',
					$e->getMessage()
				)
			);
		}

		// Surviving the call is the whole assertion.
		$this->assertTrue( true );
	}
}
