<?php
/**
 * The main controller responsible for the New Editor feature.
 *
 * @since   TBD
 *
 * @package TEC\Events\New_Editor;
 */

namespace TEC\Events\New_Editor;

use TEC\Common\Contracts\Provider\Controller as ControllerContract;
use TEC\Common\StellarWP\Assets\Asset;
use Tribe__Events__Main as TEC;
use WP_Block_Editor_Context;
use WP_Post;

/**
 * Class Controller.
 *
 * @since   TBD
 *
 * @package TEC\Events\New_Editor;
 */
class Controller extends ControllerContract {
	/**
	 * A flag property used during exploration to toggle between different approches.
	 *
	 * @since TBD
	 *
	 * @var string One of `block-editor` or `metabox`.
	 */
	private string $__experimental_approach = 'metabox';

	/**
	 * Returns whether this feature is active or not.
	 *
	 * @since TBD
	 *
	 * @return bool True if not deactivated via environment variable or a constant.
	 */
	public function is_active(): bool {
		return tec_using_new_editor();
	}

	/**
	 * Binds the implementations required by the feature and hooks the controller to the actions and filters required.
	 *
	 * @since TBD
	 *
	 * @return void Bindings are registered, the controller is hooked to actions and filters.
	 */
	protected function do_register(): void {
		// Register the `editor` binding replacement for back-compatibility purposes.
		$back_compatible_editor = new Back_Compatible_Editor();
		$this->container->singleton( 'editor', $back_compatible_editor );
		$this->container->singleton( 'events.editor', $back_compatible_editor );
		$this->container->singleton( 'events.editor.compatibility', $back_compatible_editor );

		// Tell Common, TEC, ET and so on NOT to load blocks.
		add_filter( 'tribe_editor_should_load_blocks', '__return_false' );

		// We're using TEC new editor.
		add_filter( 'tec_using_new_editor', '__return_true' );

		add_filter('block_editor_settings_all', [$this,'filter_block_editor_settings'],100,2);

		// Apprach 1: leverage the Block Editor and customize it.
		// Register the main assets entry point.
		Asset::add(
			'tec-new-editor',
			'new-editor.js'
		)->add_to_group_path( TEC::class . '-packages' )
		     ->add_to_group( 'tec-new-editor' )
		     ->enqueue_on( 'enqueue_block_editor_assets' )
		     ->add_localize_script( 'tec.newEditor', [
			     '__experimentalApproach' => $this->__experimental_approach,
			     'eventCategoryTaxonomyName' => TEC::TAXONOMY
		     ] )
		     ->register();

		Asset::add(
			'tec-new-editor-style',
			'style-new-editor.css'
		)->add_to_group_path( TEC::class . '-packages' )
		     ->add_to_group( 'tec-new-editor' )
		     ->enqueue_on( 'enqueue_block_editor_assets' )
		     ->register();

		// Approach 2: metabox.
		if ($this->__experimental_approach === 'metabox') {
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		}
	}

	/**
	 * Unhooks the controller from the actions and filters required by the feature.
	 *
	 * @since TBD
	 *
	 * @return void The hooked actions and filters are removed.
	 */
	public function unregister(): void {
		remove_filter( 'tribe_editor_should_load_blocks', '__return_false' );
		remove_filter( 'tec_using_new_editor', '__return_true' );
		remove_action('add_meta_boxes', [$this, 'add_meta_boxes']);
		remove_filter('block_editor_settings_all', [$this,'filter_block_editor_settings'],100);
	}

	/**
	 * Returns whether the given Post uses the New Editor.
	 *
	 * @since TBD
	 *
	 * @param WP_Post $post The Post to check.
	 *
	 * @return bool Whether the given Post uses the New Editor.
	 */
	public function post_uses_new_editor( WP_Post $post ): bool {
		$supported_post_types = $this->get_supported_post_types();

		return in_array( $post->post_type, $supported_post_types, true );
	}

	public function filter_block_editor_settings(array $settings, WP_Block_Editor_Context $context){
		if(!(
			$context->post instanceof WP_Post
			&& $this->post_uses_new_editor($context->post)
		)){
			return $settings;
		}

		// Lock the template.
		$settings['templateLock'] = true;

		// Ensure metaboxes are active for the post.
		$settings['enableCustomFields'] = true;

		return $settings;
	}

	/**
	 * Returns the filtered list of Post Types that should be using the New Editor.
	 *
	 * @since TBD
	 *
	 * @return list<string> The filtered list of Post Types that should be using the New Editor.
	 */
	protected function get_supported_post_types(): array {
		/**
		 * Filters the list of post types that use the new editor.
		 *
		 * @since TBD
		 *
		 * @param array<string> The list of post types that use the new editor.
		 */
		$supported_post_types = apply_filters(
			'tec_events_new_editor_post_types',
			[ TEC::POSTTYPE ]
		);

		return (array)$supported_post_types;
	}

	public function get_metabox(): string {
		return <<< HTML
<div>
	<div >
	<h3 style="text-align: center">TEC New Editor</h3>
	    <table class="form-table" style="display: flex; justify-content: center">
	        <tr>
	            <th><label for="event_title">Event Title:</label></th>
	            <td><input type="text" id="event_title" name="event_title" value="" class="regular-text"></td>
	        </tr>
	        <tr>
	            <th><label for="event_description">Event Description:</label></th>
	            <td><textarea id="event_description" name="event_description" rows="4" cols="50"></textarea></td>
	        </tr>
	        <tr>
	            <th><label for="event_date">Event Date:</label></th>
	            <td><input type="date" id="event_date" name="event_date" value=""></td>
	        </tr>
	        <tr>
	            <th><label for="event_time">Event Time:</label></th>
	            <td><input type="time" id="event_time" name="event_time" value=""></td>
	        </tr>
	        <tr>
	            <th><label for="event_location">Event Location:</label></th>
	            <td><input type="text" id="event_location" name="event_location" value="" class="regular-text"></td>
	        </tr>
	    </table>
	</div>
</div>
HTML;
	}

	public function the_metabox(): void {
		echo $this->get_metabox();
	}

	public function add_meta_boxes():void{
		add_meta_box(
			'tec-new-editor',
			'TEC NEW EDITOR',
			[$this,'the_metabox'],
			$this->get_supported_post_types(),
			'normal',
			'high'
		);
	}
}
