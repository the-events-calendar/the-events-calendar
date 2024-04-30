<?php
/**
 * Elementor Assets Manager.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor
 */

namespace TEC\Events\Integrations\Plugins\Elementor;

use TEC\Events\Integrations\Plugins\Elementor\Template\Documents\Event_Single_Static;
use TEC\Events\Integrations\Plugins\Elementor\Template\Importer;
use Tribe\Events\Views\V2\Template_Bootstrap;
use TEC\Common\Contracts\Provider\Controller;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Assets_Manager
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor
 */
class Assets_Manager extends Controller {
	/**
	 * The group key for the assets.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	public static $group_key = 'tec-events-elementor';

	/**
	 * The group key for the icon assets.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	public static $icon_group_key = 'tec-events-elementor-icons';

	/**
	 * Register and enqueue the hooks for the plugin.
	 *
	 * @since 6.4.0
	 */
	public function do_register(): void {
		$this->add_actions();
	}

	/**
	 * Unregister the hooks for the plugin.
	 *
	 * @since 6.4.0
	 */
	public function unregister(): void {
		$this->remove_actions();
	}

	/**
	 * Adds the actions required by the assets manager.
	 *
	 * @since 6.4.0
	 */
	public function add_actions(): void {
		add_action( 'init', [ $this, 'register_widget_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_resources' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_single_event_template_styles' ] );

		// Enqueue the widget styles when the widget is rendered. Both in the editor preview and on the frontend.
		add_action( 'elementor/widget/before_render_content', [ $this, 'action_enqueue_widget_styles' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'enqueue_preview_styles' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'action_enqueue_editor_styles' ] );

		// register and enqueue the icon styles for our widgets.
		add_action( 'elementor/editor/before_enqueue_styles', [ $this, 'action_register_editor_styles' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'action_enqueue_editor_styles' ] );
	}

	/**
	 * Removes the actions required by the assets manager.
	 *
	 * @since 6.4.0
	 */
	public function remove_actions() {
		remove_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_resources' ] );
		remove_action( 'wp_enqueue_scripts', [ $this, 'enqueue_single_event_template_styles' ] );

		// register and enqueue the icon styles for our widgets.
		remove_action( 'elementor/editor/before_enqueue_styles', [ $this, 'action_register_icon_styles' ] );
		remove_action( 'elementor/editor/after_enqueue_styles', [ $this, 'action_enqueue_icon_styles' ] );
	}

	/**
	 * Registers the assets for the widgets.
	 * To be enqueued later based on widget render.
	 *
	 * Note: we *register* them manually.
	 * When we enqueue, we loop through the widgets and just try to enqueue for all of them.
	 *
	 * @since 6.4.0
	 */
	public function register_widget_assets() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Register the base widget styles first.
		tribe_asset(
			tribe( 'tec.main' ),
			'tec-events-elementor-widgets-base-styles',
			'integrations/plugins/elementor/widgets/widget-base.css',
			[],
		);

		foreach ( $this->get_widgets() as $widget ) {
			tribe( $widget )->register_style();
		}

		// setting this to enqueue on elementor/editor/after_enqueue_styles fails, so we run it separately, below.
		tribe_asset(
			tribe( 'tec.main' ),
			'tec-events-elementor-icons',
			'integrations/plugins/elementor/icons.css',
			[],
			null,
			[
				'groups' => [ static::$icon_group_key ],
			]
		);


		do_action( 'tec_events_elementor_register_widget_assets', $this );
	}

	/**
	 * Returns the widgets that need Elementor assets registered.
	 */
	public function get_widgets() {
		$widgets = tribe( Widgets_Manager::class )->get_widgets();

		return apply_filters( 'tec_events_elementor_widget_asset_widgets', $widgets );
	}

	/**
	 * Enqueues the assets for the widgets in the Elementor preview.
	 * Just enqueues them all, as any could be added/removed while editing.
	 *
	 * @since 6.4.0
	 */
	public function enqueue_preview_styles() {
		foreach ( $this->get_widgets() as $widget ) {
			tribe( $widget )->enqueue_style();
		}
	}

	/**
	 * Enqueue frontend resources.
	 *
	 * @since 6.4.0
	 */
	public function enqueue_frontend_resources(): void {
		tribe_asset_enqueue( 'tribe-events-v2-single-skeleton' );
		tribe_asset_enqueue( 'tribe-events-v2-single-skeleton-full' );
		tribe_asset_enqueue( 'tec-events-elementor-widgets-base-styles' );

		do_action( 'tec_events_elementor_enqueue_frontend_assets', $this );
	}

	/**
	 * Dynamically enqueues the styles for the rendered TEC widget.
	 *
	 * @since 6.4.0
	 *
	 * @param Abstract_Widget $widget The widget instance.
	 */
	public function action_enqueue_widget_styles( $widget ) {
		$name = $widget->get_name();
		if ( ! $name ) {
			return;
		}

		$widgets = $this->get_widgets();

		// Not one of ours.
		if ( strpos( $name, 'tec_events_elementor_widget_' ) === false ) {
			return;
		}

		if ( ! method_exists( $widget, 'enqueue_style' ) ) {
			return;
		}

		tribe( $widget )->enqueue_style();
	}

	/**
	 * Registers icon styles for Elementor.
	 *
	 * @since 6.4.0
	 */
	public function action_register_editor_styles(): void {
		// setting this to enqueue on elementor/editor/after_enqueue_styles fails, so we run it separately, below.
		tribe_asset(
			tribe( 'tec.main' ),
			'tec-events-elementor-icons',
			'integrations/plugins/elementor/icons.css',
			[],
			null,
			[
				'groups' => [ static::$icon_group_key ],
			]
		);

		/**
		 * Fires after the Elementor editor (icon) styles have been registered.
		 *
		 * @since 6.4.0
		 *
		 * @param Assets_Manager $this The assets manager instance.
		 */
		do_action( 'tec_events_elementor_register_editor_styles', $this );
	}

	/**
	 * Enqueues icon styles for Elementor.
	 *
	 * @since 6.4.0
	 */
	public function action_enqueue_editor_styles(): void {
		tribe_asset_enqueue_group( static::$icon_group_key );
	}

	/**
	 * Enqueues the Elementor stylesheet for a single event page if a specific Elementor template is set.
	 * Note this stylesheet is programmatically created by Elementor and stored in the uploads directory.
	 *
	 * @since 6.4.0
	 */
	public function enqueue_single_event_template_styles(): void {
		// Bail if we are not on a single event page.
		if ( ! tribe( Template_Bootstrap::class )->is_single_event() ) {
			return;
		}

		$event_id = get_the_ID();

		// No event ID? Bail.
		if ( ! is_numeric( $event_id ) ) {
			return;
		}

		$template = tribe( Importer::class )->get_template( Event_Single_Static::class );
		if ( null === $template ) {
			return;
		}

		$upload_dir = wp_upload_dir();

		wp_enqueue_style(
			'elementor-event-template-' . $template->ID,
			$upload_dir['baseurl'] . '/elementor/css/post-' . $template->ID . '.css',
			[],
			\Tribe__Events__Main::VERSION
		);
	}
}
