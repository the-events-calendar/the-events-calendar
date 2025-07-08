<?php

namespace TEC\Events\Block_Templates;

use TEC\Events\Block_Templates\Archive_Events\Archive_Block_Template;
use TEC\Events\Block_Templates\Single_Event\Single_Block_Template;
use WP_Block_Template;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller
 *
 * @since 6.3.3 Moved and decoupled from Block API requirements, focusing on Template requirements.
 * @since   6.2.7
 *
 * @package TEC\Events\Block_Templates
 */
class Controller extends Controller_Contract {
	/**
	 * Register the provider.
	 *
	 * @since 6.2.7
	 */
	public function do_register(): void {
		$this->add_filters();

		// Register the service provider itself on the container.
		$this->container->singleton( static::class, $this );
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since 6.2.7
	 */
	public function unregister(): void {
		$this->remove_filters();
	}

	/**
	 * Should only be active if we are in a Site Editor theme.
	 *
	 * @since 6.2.7
	 *
	 * @return bool Only active during FS theme.
	 */
	public function is_active(): bool {
		return tec_is_full_site_editor();
	}

	/**
	 * Internal FSE function for asset conditional testing.
	 *
	 * @since 5.14.2
	 *
	 * @return bool Whether The current theme supports full-site editing or not.
	 */
	public function is_full_site_editor(): bool {
		return tec_is_full_site_editor();
	}

	/**
	 * Adds the filters required by the FSE components.
	 *
	 * @since 5.14.2
	 * @since 6.2.7 Adding support for block templates.
	 */
	protected function add_filters() {
		add_filter( 'get_block_templates', [ $this, 'filter_include_templates' ], 25, 3 );
		add_filter( 'get_block_template', [ $this, 'filter_include_template_by_id' ], 10, 3 );
		add_filter( 'tribe_get_option_tribeEventsTemplate', [ $this, 'filter_events_template_setting_option' ] );
		add_filter( 'tribe_get_single_option', [ $this, 'filter_tribe_get_single_option' ], 10, 3 );
		add_filter( 'tribe_settings_save_option_array', [ $this, 'filter_tribe_save_template_option' ], 10, 2 );
		add_filter( 'archive_template_hierarchy', [ $this, 'filter_archive_template_hierarchy' ], 10, 1 );
		add_filter(
			'single_template_hierarchy',
			[
				$this,
				'filter_single_template_hierarchy',
			],
			10,
			1
		);
	}

	/**
	 * Removes registered filters.
	 *
	 * @since 6.2.7
	 */
	public function remove_filters() {
		remove_filter( 'get_block_templates', [ $this, 'filter_include_templates' ], 25 );
		remove_filter( 'get_block_template', [ $this, 'filter_include_template_by_id' ], 10 );
		remove_filter( 'tribe_get_option_tribeEventsTemplate', [ $this, 'filter_events_template_setting_option' ] );
		remove_filter( 'tribe_get_single_option', [ $this, 'filter_tribe_get_single_option' ], 10 );
		remove_filter( 'tribe_settings_save_option_array', [ $this, 'filter_tribe_save_template_option' ], 10 );
		remove_filter( 'archive_template_hierarchy', [ $this, 'filter_archive_template_hierarchy' ], 10 );
		remove_filter(
			'single_template_hierarchy',
			[
				$this,
				'filter_single_template_hierarchy',
			],
			10
		);
	}

	/**
	 * Redirect the post type template to our Events Archive slug, as that is what is used for lookup in the database.
	 *
	 * @since 6.2.7
	 *
	 * @param string[] $templates Templates in order of display hierarchy.
	 *
	 * @return string[] Adjusted file name that is parsed to match our block template.
	 */
	public function filter_archive_template_hierarchy( $templates ) {
		if ( empty( $templates ) ) {
			return $templates;
		}

		if ( ! is_array( $templates ) ) {
			return $templates;
		}

		// Is it our post type?
		$index = array_search( 'archive-tribe_events.php', $templates, true );
		if ( ! is_int( $index ) ) {
			return $templates;
		}

		// Switch to our faux template which maps to our slug.
		$templates[ $index ] = 'archive-events.php';

		return $templates;
	}

	/**
	 * Redirect the post type template to our Single Event slug, as that is what is used for lookup in the database.
	 *
	 * @since 6.2.7
	 *
	 * @param array $templates Templates in order of display hierarchy.
	 *
	 * @return array Adjusted file name that is parsed to match our block template.
	 */
	public function filter_single_template_hierarchy( $templates ) {
		if ( empty( $templates ) ) {
			return $templates;
		}

		if ( ! is_array( $templates ) ) {
			return $templates;
		}

		// Is it our post type?
		$index = array_search( 'single-tribe_events.php', $templates, true );
		if ( is_int( $index ) ) {
			// Switch to our faux template which maps to our slug.
			$templates[ $index ] = 'single-event.php';
		}

		return $templates;
	}

	/**
	 * Adds the archive template to the array of block templates.
	 *
	 * @since 5.14.2
	 * @since 6.2.7 Added support for single event templates.
	 * @since 6.14.0 Passing $query_result to get_filtered_block_templates().
	 *
	 * @param WP_Block_Template[] $query_result Array of found block templates.
	 * @param array               $query        {
	 *                                          Optional. Arguments to retrieve templates.
	 * @param string              $template_type The type of template being requested.
	 *
	 * @type array                $slug__in     List of slugs to include.
	 * @type int                  $wp_id        Post ID of customized template.
	 * }
	 *
	 * @return array The modified $query.
	 */
	public function filter_include_templates( $query_result, $query, $template_type ) {
		if ( ! is_array( $query_result ) ) {
			return $query_result;
		}
		// Get our block template services for this query.
		$template_services = $this->get_filtered_block_templates( $template_type, $query_result );
		foreach ( $template_services as $template ) {
			if ( empty( $query['slug__in'] ) || in_array( $template->slug(), $query['slug__in'], true ) ) {
				/**
				 * @var WP_Block_Template $wp_template
				 */
				$wp_template = $template->get_block_template();
				if ( $wp_template ) {
					$query_result[] = $wp_template;
				}
			}
		}

		return $query_result;
	}

	/**
	 * Fetch our Block Template by ID.
	 *
	 * @since 6.2.7
	 *
	 * @param null|WP_Block_Template $block_template The filtered template.
	 * @param string                 $id             The block template ID.
	 * @param string                 $template_type  The template type.
	 *
	 * @return null|WP_Block_Template
	 */
	public function filter_include_template_by_id( $block_template, $id, $template_type ) {
		if ( ! is_null( $block_template ) ) {
			return $block_template;
		}

		$template_services = $this->get_filtered_block_templates( $template_type );
		foreach ( $template_services as $template ) {
			if ( $id === $template->id() ) {
				return $template->get_block_template();
			}
		}

		return $block_template;
	}

	/**
	 * Filters and returns the available Event Block Template Services, used to locate
	 * WP_Block_Template instances.
	 *
	 * @since 6.2.7
	 * @since 6.14.0 Added $query_result parameter.
	 *
	 * @param string              $template_type The type of templates we are fetching.
	 * @param WP_Block_Template[] $query_result  The query result.
	 *
	 * @return Block_Template_Contract[] List of filtered Event Calendar templates.
	 */
	public function get_filtered_block_templates( $template_type = 'wp_template', $query_result = [] ): array {
		$templates = [];
		if ( $template_type === 'wp_template' ) {
			$theme_has_single_event_template  = false;
			$theme_has_archive_event_template = false;
			foreach ( $query_result as $template ) {
				if ( 'theme' !== $template->origin && 'theme' !== $template->source ) {
					continue;
				}

				if ( 'single-event' === $template->slug ) {
					$theme_has_single_event_template = true;
				}

				if ( 'archive-events' === $template->slug ) {
					$theme_has_archive_event_template = true;
				}
			}

			/**
			 * Filter whether the event archive block template should be used.
			 *
			 * @since 6.4.0
			 *
			 * @param bool $allow_archive Whether the event archive block template should be used.
			 */
			$allow_archive = apply_filters( 'tec_events_allow_archive_block_template', ! $theme_has_archive_event_template );
			if ( $allow_archive ) {
				$templates[] = tribe( Archive_Block_Template::class );
			}

			/**
			 * Filter whether the event single block template should be used.
			 *
			 * @since 6.4.0
			 *
			 * @param bool $allow_single Whether the single block template should be used.
			 */
			$allow_single = apply_filters( 'tec_events_allow_single_block_template', ! $theme_has_single_event_template );
			if ( $allow_single ) {
				$templates[] = tribe( Single_Block_Template::class );
			}

			return $templates;

		}

		/**
		 * Filter our available Full Site Block Template objects available. These are used in to define and store WP_Block_Template instances.
		 *
		 * @since 6.2.7
		 *
		 * @param Block_Template_Contract[] $templates     The list of our Block_Template_Contracts to be used to register and generate WP_Block_Template.
		 * @param string                    $template_type The type of template being requested.
		 */
		return apply_filters( 'tec_events_get_full_site_block_template_services', $templates, $template_type );
	}

	/**
	 * If we're using a FSE theme, we always use the full styling.
	 *
	 * @since 5.14.2
	 *
	 * @param string $value The value of the option.
	 *
	 * @return string $value The original value, or an empty string if FSE is active.
	 */
	public function filter_events_template_setting_option( $value ) {
		return tec_is_full_site_editor() ? '' : $value;
	}


	/**
	 * Override the get_single_option to return the default event template when FSE is active.
	 *
	 * @since 5.14.2
	 *
	 * @param mixed  $option      Results of option query.
	 * @param string $default     The default value.
	 * @param string $option_name Name of the option.
	 *
	 * @return mixed results of option query.
	 */
	public function filter_tribe_get_single_option( $option, $default, $option_name ) {
		if ( 'tribeEventsTemplate' !== $option_name ) {
			return $option;
		}

		if ( tec_is_full_site_editor() ) {
			return '';
		}

		return $option;
	}

	/**
	 * Overwrite the template option on save if FSE is active.
	 * We only support the default events template for now.
	 *
	 * @since 5.14.2
	 *
	 * @param array<string, mixed> $options   The array of values to save. In the format option key => value.
	 * @param string               $option_id The main option ID.
	 *
	 * @return array<string, mixed> $options   The array of values to save. In the format option key => value.
	 */
	public function filter_tribe_save_template_option( $options, $option_id ) {
		if ( tec_is_full_site_editor() ) {
			$options['tribeEventsTemplate'] = '';
		}

		return $options;
	}
}
