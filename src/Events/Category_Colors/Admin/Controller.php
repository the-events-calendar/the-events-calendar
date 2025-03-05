<?php
/**
 * Controller class for handling the category colors feature.
 * This class acts as the main entry point for managing the lifecycle of
 * category colors, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Admin;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe__Events__Main;

/**
 * Class Controller
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors
 */
class Controller extends Controller_Contract {

	/**
	 * Register the provider.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		/*This may not be needed*/
		$this->container->singleton( Add_Category::class );
		$this->container->singleton( Edit_Category::class );
		$this->container->singleton( Quick_Edit::class );
		$this->add_filters();
		$this->enqueue_assets();
	}

	/**
	 * Adds the filters required.
	 *
	 * @since TBD
	 */
	protected function add_filters() {
		$taxonomy = Tribe__Events__Main::TAXONOMY;
		add_action( "{$taxonomy}_add_form_fields", [ $this, 'display_add_category_fields' ] );
		add_action( "{$taxonomy}_edit_form_fields", [ $this, 'display_edit_category_fields' ], 10, 2 );
		add_action( "created_{$taxonomy}", [ $this, 'save_add_category_fields' ] );
		add_action( "edited_{$taxonomy}", [ $this, 'save_edit_category_fields' ] );
		add_filter( "manage_edit-{$taxonomy}_columns", [ $this, 'add_columns' ] );
		add_filter( "manage_{$taxonomy}_custom_column", [ $this, 'add_column_data' ],10 , 3 );
		add_action( 'quick_edit_custom_box', [ $this, 'add_quick_edit_fields' ], 10, 2 );

	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since TBD
	 */
	public function unregister(): void {}

	public function enqueue_assets() {
		$this->container->make( Add_Category::class )->enqueue_assets();
	}

	public function display_add_category_fields( $taxonomy ) {
		$this->container->make( Add_Category::class )->display_category_fields( $taxonomy );
	}

	public function display_edit_category_fields( $tag, $taxonomy ) {
		$this->container->make( Edit_Category::class )->display_category_fields( $tag, $taxonomy );
	}

	public function save_add_category_fields( $taxonomy ) {
		$this->container->make( Add_Category::class )->save_category_fields( $taxonomy );
	}

	public function save_edit_category_fields( $taxonomy ) {
		$this->container->make( Edit_Category::class )->save_category_fields( $taxonomy );
	}

	public function add_columns( $columns ) {
		return $this->container->make( Quick_Edit::class )->add_columns( $columns );
	}
	public function add_column_data( $content, $column_name, $term_id ) {
		return $this->container->make( Quick_Edit::class )->add_custom_column_data( $content, $column_name, $term_id );
	}

	public function add_quick_edit_fields($column_name,$screen){
		$this->container->make( Quick_Edit::class )->add_quick_edit_fields( $column_name,$screen );
	}
}
