<?php
/**
 * Elementor Assets Manager.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor
 */

namespace TEC\Events\Integrations\Plugins\Elementor;

use TEC\Events\Integrations\Plugins\Elementor\Template\Importer;
use Tribe\Events\Views\V2\Template_Bootstrap;
use TEC\Common\Contracts\Provider\Controller;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Assets_Manager
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor
 */
class Assets_Manager extends Controller {
	/**
	 * The group key for the assets.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $group_key = 'tec-elementor';

	/**
	 * The group key for the icon assets.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $icon_group_key = 'tec-elementor-icons';

	/**
	 * The widgets to register and enqueue assets for.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected array $widgets = [
		'event_categories' => 'event-categories.css',
		'event_export'     => 'event-export.css',
		'event_navigation' => 'event-navigation.css',
		'event_organizer'  => 'event-organizer.css',
		'event_related'    => 'related-events.css',
		'event_series'     => 'event-series.css',
		'event_status'     => 'event-status.css',
		'event_tags'       => 'event-tags.css',
		'event_venue'      => 'event-venue.css',
		'event_website'    => 'event-website.css',
	];

	/**
	 * Register and enqueue the hooks for the plugin.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		$this->add_actions();
	}

	/**
	 * Unregister the hooks for the plugin.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		$this->remove_actions();
	}

	/**
	 * Adds the actions required by the assets manager.
	 *
	 * @since TBD
	 */
	public function add_actions(): void {
		add_action( 'init', [ $this, 'register_widget_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_resources' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_single_event_template_styles' ] );

		// Enqueue the widget styles when the widget is rendered. Both in the editor preview and on the frontend.
		add_action( 'elementor/widget/before_render_content', [ $this, 'action_enqueue_widget_styles' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'enqueue_preview_styles' ] );

		// register and enqueue the icon styles for our widgets.
		add_action( 'elementor/editor/before_enqueue_styles', [ $this, 'action_register_editor_styles' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'action_enqueue_editor_styles' ] );
	}

	/**
	 * Removes the actions required by the assets manager.
	 *
	 * @since TBD
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
	 * @since TBD
	 */
	public function register_widget_assets() {
		$pro = tribe( 'events-pro.main' );

		tribe_asset(
			$pro,
			'tec-elementor-event_categories-widget-styles',
			'integrations/plugins/elementor/widgets/event-categories.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		tribe_asset(
			$pro,
			'tec-elementor-event_export-widget-styles',
			'integrations/plugins/elementor/widgets/event-export.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		tribe_asset(
			$pro,
			'tec-elementor-event_navigation-widget-styles',
			'integrations/plugins/elementor/widgets/event-navigation.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		tribe_asset(
			$pro,
			'tec-elementor-event_organizer-widget-styles',
			'integrations/plugins/elementor/widgets/event-organizer.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		tribe_asset(
			$pro,
			'tec-elementor-event_related-widget-styles',
			'integrations/plugins/elementor/widgets/related-events.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		tribe_asset(
			$pro,
			'tec-elementor-event_series-widget-styles',
			'integrations/plugins/elementor/widgets/event-series.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		tribe_asset(
			$pro,
			'tec-elementor-event_status-widget-styles',
			'integrations/plugins/elementor/widgets/event-status.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		tribe_asset(
			$pro,
			'tec-elementor-event_tags-widget-styles',
			'integrations/plugins/elementor/widgets/event-tags.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		tribe_asset(
			$pro,
			'tec-elementor-event_venue-widget-styles',
			'integrations/plugins/elementor/widgets/event-venue.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		tribe_asset(
			$pro,
			'tec-elementor-event_website-widget-styles',
			'integrations/plugins/elementor/widgets/event-website.css',
			[],
			null,
			[
				'groups' => [ static::$group_key ],
			]
		);

		do_action( 'tec_events_elementor_register_widget_assets', $this );
	}

	/**
	 * Returns the widgets that need Elementor assets registered.
	 */
	public function get_widgets() {
		return apply_filters( 'tec_events_elementor_widget_asset_widgets', $this->widgets );
	}

	/**
	 * Enqueues the assets for the widgets in the Elementor preview.
	 * Just enqueues them all, as any could be added/removed while editing.
	 *
	 * @since TBD
	 */
	public function enqueue_preview_styles() {
		foreach ( $this->get_widgets() as $slug => $file ) {
			tribe_asset_enqueue( 'tec-elementor-' . $slug . '-widget-styles' );
		}
	}

	/**
	 * Enqueue frontend resources.
	 *
	 * @since TBD
	 */
	public function enqueue_frontend_resources(): void {
		tribe_asset_enqueue( 'tribe-events-v2-single-skeleton' );
		tribe_asset_enqueue( 'tribe-events-v2-single-skeleton-full' );
	}

	/**
	 * Dynamically enqueues the styles for the rendered TEC widget.
	 *
	 * @since TBD
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
		if ( ! method_exists( $widget, 'get_slug' ) ) {
			return;
		}

		$slug = $widget::get_slug();

		if ( ! in_array( $slug, array_keys( $widgets ), true ) ) {
			return;
		}

		tribe_asset_enqueue( 'tec-elementor-' . $slug . '-widget-styles' );
	}

	/**
	 * Registers icon styles for Elementor.
	 *
	 * @since TBD
	 */
	public function action_register_editor_styles(): void {
		// setting this to enqueue on elementor/editor/after_enqueue_styles fails, so we run it separately, below.
		tribe_asset(
			tribe( 'events-pro.main' ),
			'tec-elementor-icons',
			'integrations/plugins/elementor/icons.css',
			[],
			null,
			[
				'groups' => [ static::$icon_group_key ],
			]
		);
	}

	/**
	 * Enqueues icon styles for Elementor.
	 *
	 * @since TBD
	 */
	public function action_enqueue_editor_styles(): void {
		tribe_asset_enqueue_group( static::$icon_group_key );
	}

	/**
	 * Enqueues the Elementor stylesheet for a single event page if a specific Elementor template is set.
	 * Note this stylesheet is programmatically created by Elementor and stored in the uploads directory.
	 *
	 * @since TBD
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

		$template = tribe( Importer::class )->get_template();
		if ( null === $template ) {
			return;
		}

		$upload_dir = wp_upload_dir();

		wp_enqueue_style(
			'elementor-event-template-' . $template->ID,
			$upload_dir['baseurl'] . '/elementor/css/post-' . $template->ID . '.css',
			[],
			\Tribe__Events__Pro__Main::VERSION
		);
	}
}
