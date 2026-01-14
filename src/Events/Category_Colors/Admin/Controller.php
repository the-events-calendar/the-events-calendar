<?php
/**
 * Controller class for handling the category colors feature.
 * This class acts as the main entry point for managing the lifecycle of
 * category colors, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Admin;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Category_Colors\CSS\Generator;
use Tribe__Events__Main;
use WP_Term;
use TEC\Common\StellarWP\Assets\Config;

/**
 * Class Controller
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors
 */
class Controller extends Controller_Contract {

	/**
	 * Register the provider.
	 *
	 * @since 6.14.0
	 */
	protected function do_register(): void {
		Config::add_group_path( 'tec-events-category-colors', tribe( 'tec.main' )->plugin_path . 'build/', 'category-colors' );
		$this->add_filters();
		$this->enqueue_assets();
	}

	/**
	 * Adds the filters required.
	 *
	 * @since 6.14.0
	 */
	protected function add_filters() {
		$taxonomy = Tribe__Events__Main::TAXONOMY;
		add_action( "{$taxonomy}_add_form_fields", [ $this, 'display_add_category_fields' ] );
		add_action( "{$taxonomy}_edit_form_fields", [ $this, 'display_edit_category_fields' ], 10, 2 );
		add_action( "created_{$taxonomy}", [ $this, 'save_add_category_fields' ] );
		add_action( "edited_{$taxonomy}", [ $this, 'save_edit_category_fields' ] );
		add_action( 'quick_edit_custom_box', [ $this, 'add_quick_edit_fields' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'maybe_add_inline_styles' ] );

		add_filter( "manage_edit-{$taxonomy}_columns", [ $this, 'add_columns' ] );
		add_filter( "manage_{$taxonomy}_custom_column", [ $this, 'add_column_data' ], 10, 3 );

		add_action( "created_{$taxonomy}", [ $this, 'regenerate_css' ] );
		add_action( "edited_{$taxonomy}", [ $this, 'regenerate_css' ] );
		add_action( 'delete_term', [ $this, 'regenerate_css' ], 10, 4 );
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since 6.14.0
	 */
	public function unregister(): void {
		$taxonomy = Tribe__Events__Main::TAXONOMY;

		remove_action( "{$taxonomy}_add_form_fields", [ $this, 'display_add_category_fields' ] );
		remove_action( "{$taxonomy}_edit_form_fields", [ $this, 'display_edit_category_fields' ] );
		remove_action( "created_{$taxonomy}", [ $this, 'save_add_category_fields' ] );
		remove_action( "edited_{$taxonomy}", [ $this, 'save_edit_category_fields' ] );
		remove_action( 'quick_edit_custom_box', [ $this, 'add_quick_edit_fields' ] );
		remove_action( 'admin_enqueue_scripts', [ $this, 'maybe_add_inline_styles' ] );

		remove_filter( "manage_edit-{$taxonomy}_columns", [ $this, 'add_columns' ] );
		remove_filter( "manage_{$taxonomy}_custom_column", [ $this, 'add_column_data' ] );

		remove_action( "created_{$taxonomy}", [ $this, 'regenerate_css' ] );
		remove_action( "edited_{$taxonomy}", [ $this, 'regenerate_css' ] );
		remove_action( 'delete_term', [ $this, 'regenerate_css' ] );
	}

	/**
	 * Enqueues assets required for category colors functionality.
	 *
	 * @since 6.14.0
	 */
	public function enqueue_assets() {
		/** @var Category_Colors_Styles $instance */
		$instance = $this->container->make( Category_Colors_Styles::class );
		$instance->enqueue_assets();
	}

	/**
	 * Displays the category color fields when adding a new category.
	 *
	 * @since 6.14.0
	 *
	 * @param string $taxonomy The taxonomy slug.
	 */
	public function display_add_category_fields( $taxonomy ) {
		/** @var Add_Category $instance */
		$instance = $this->container->make( Add_Category::class );
		$instance->display_category_fields( $taxonomy );
	}

	/**
	 * Displays the category color fields when editing an existing category.
	 *
	 * @since 6.14.0
	 *
	 * @param WP_Term $tag      The term object.
	 * @param string  $taxonomy The taxonomy slug.
	 */
	public function display_edit_category_fields( $tag, $taxonomy ) {
		/** @var Edit_Category $instance */
		$instance = $this->container->make( Edit_Category::class );
		$instance->display_category_fields( $tag, $taxonomy );
	}

	/**
	 * Saves the category color fields when adding a new category.
	 *
	 * @since 6.14.0
	 *
	 * @param string $taxonomy The taxonomy slug.
	 */
	public function save_add_category_fields( $taxonomy ) {
		/** @var Add_Category $instance */
		$instance = $this->container->make( Add_Category::class );
		$instance->save_category_fields( $taxonomy );
	}

	/**
	 * Saves the category color fields when editing an existing category.
	 * This method runs for both Edit and Quick Edit.
	 *
	 * @since 6.14.0
	 *
	 * @param string $taxonomy The taxonomy slug.
	 */
	public function save_edit_category_fields( $taxonomy ) {
		/** @var Edit_Category $instance */
		$instance = $this->container->make( Edit_Category::class );
		$instance->save_category_fields( $taxonomy );
	}

	/**
	 * Adds custom columns to the category table.
	 *
	 * @since 6.14.0
	 *
	 * @param array $columns The existing columns.
	 * @return array Modified columns array.
	 */
	public function add_columns( $columns ) {
		/** @var Quick_Edit $instance */
		$instance = $this->container->make( Quick_Edit::class );
		return $instance->add_columns( $columns );
	}

	/**
	 * Adds custom column data to the category table.
	 *
	 * @since 6.14.0
	 * @since 6.15.14 Cast data before sending to add_custom_column_data().
	 *
	 * @param string $content     The column content.
	 * @param string $column_name The name of the column.
	 * @param int    $term_id     The term ID.
	 * @return string Modified column content.
	 */
	public function add_column_data( $content, $column_name, $term_id ) {
		/** @var Quick_Edit $instance */
		$instance = $this->container->make( Quick_Edit::class );
		return $instance->add_custom_column_data( (string) $content, (string) $column_name, (int) $term_id );
	}

	/**
	 * Adds custom fields to the Quick Edit interface.
	 *
	 * @since 6.14.0
	 *
	 * @param string        $column_name The name of the column.
	 * @param string|object $screen      The current screen.
	 */
	public function add_quick_edit_fields( $column_name, $screen ) {
		/** @var Quick_Edit $instance */
		$instance = $this->container->make( Quick_Edit::class );
		$instance->add_quick_edit_fields( $column_name, $screen );
	}

	/**
	 * Maybe adds inline styles for category colors.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public function maybe_add_inline_styles(): void {
		/** @var Category_Colors_Styles $instance */
		$instance = $this->container->make( Category_Colors_Styles::class );
		$instance->maybe_add_inline_styles();
	}

	/**
	 * Regenerates the category colors CSS stylesheet.
	 *
	 * @since 6.14.0
	 *
	 * @param int    $term_id     The term ID.
	 * @param int    $tt_id       The term taxonomy ID.
	 * @param string $taxonomy    The taxonomy slug.
	 * @param mixed  $deleted_term The deleted term object.
	 */
	public function regenerate_css( $term_id = 0, $tt_id = 0, $taxonomy = '', $deleted_term = null ): void {
		// If this is a delete_term action, verify the taxonomy matches.
		if ( ! empty( $taxonomy ) && $taxonomy !== Tribe__Events__Main::TAXONOMY ) {
			return;
		}

		$generator = tribe( Generator::class );
		$generator->generate_and_save_css();
	}
}
