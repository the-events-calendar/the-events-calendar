<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

class Phase_View_Renderer {
	/**
	 * Our template key.
	 *
	 * @since 6.0.0
	 *
	 * @var string $key
	 */
	private $key;

	/**
	 * Path to the primary template.
	 *
	 * @since 6.0.0
	 *
	 * @var string $template_path
	 */
	private $template_path;

	/**
	 * Path to the base migration view directory.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	private $template_directory;

	/**
	 * Vars we need to pass down to the primary template.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>
	 */
	private $vars = [];

	/**
	 * List of node definitions.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>
	 */
	private $nodes = [];

	/**
	 * Passthrough options which will be output to the response.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>
	 */
	private $options = [];

	/**
	 * A flag variable indicating whether the JS code receiving the data should
	 * start, or keep, polling the backend for data or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $poll = false;

	/**
	 * Phase_View_Renderer constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param string              $key       Our template key.
	 * @param string              $file_path Path to the primary template.
	 * @param array<string,mixed> $vars      Vars we need to pass down to the primary template.
	 * @param array<string,mixed> $options   Vars to passthrough to the frontend output.
	 */
	public function __construct( $key, $file_path, array $vars = [], array $options = [] ) {
		$this->options       = $options;
		$this->key           = $key;
		$this->template_path = $file_path;
		// Our root template directory for all migration templates.
		$this->template_directory = tribe( 'tec.main' )->plugin_path . 'src/Events/Custom_Tables/V1/admin-views/migration';
		// Add the vars we already have, in case template relies on it.
		$this->vars = array_merge( [
			'phase'              => $key,
			'template_directory' => $this->template_directory,
			'text'               => tribe( String_Dictionary::class ),
		], $vars );
	}

	/**
	 * Adds a node, with the template definitions to be rendered separately from the primary template.
	 * This is used to decouple stateful nodes from static nodes that rarely render.
	 *
	 * @since 6.0.0
	 *
	 * @param string              $key      Our node key.
	 * @param string              $selector The selector used to target where this node will be rendered. Often
	 *                                      will be a target in the primary template.
	 * @param string              $template Path to the node template.
	 * @param array<string,mixed> $vars     A map from context variable names to their values.
	 * @param array<string,mixed> $options  Frontend options to apply to this node.
	 */
	public function register_node( $key, $selector, $template, $vars = [], $options = [] ) {
		$this->nodes[] = [
			'target'   => $selector,
			'template' => $template,
			'key'      => $key,
			'vars'     => $vars,
			'options'  => $options,
		];
	}

	/**
	 * Compile the list of nodes into the format needed for consumption. Will render the html and store various meta
	 * details.
	 *
	 * @since 6.0.0
	 *
	 * @return array The list of nodes.
	 */
	protected function compile_nodes() {
		// Base on what nodes are registered, compile and return the structured data
		$nodes = [];
		foreach ( $this->nodes as $node ) {
			$html    = $this->get_template_html( $node['template'], $node['vars'] );
			// No need to handle the error just yet.
			if ( is_wp_error( $html ) ) {
				continue;
			}

			$nodes[] = array_merge( $node['options'], [
				'html'   => $html,
				'hash'   => sha1( $html ),
				'key'    => $node['key'],
				'target' => $node['target']
			] );
		}

		return $nodes;
	}

	/**
	 * Will compile the nodes and primary template into a structured array.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string, mixed> The compiled output.
	 */
	public function compile() {
		$html = $this->get_template_html( $this->template_path, $this->vars );
		if ( is_wp_error( $html ) ) {
			$html = '';
		}

		return array_merge( $this->options, [
			'key'   => $this->key,
			// Based on what is registered, render the parent template
			'html'  => $this->pre_post_content( $html ),
			'nodes' => $this->compile_nodes(),
			'poll'  => $this->poll,
		] );
	}

	/**
	 * Prepends and appends any custom HTML content to the output.
	 *
	 * @since 6.0.0
	 *
	 * @param $html string The HTML to be surrounded.
	 *
	 * @return string The HTML content with any generated pre post HTML added.
	 */
	protected function pre_post_content( $html ) {

		/**
		 * Fires at the top of the upgrade step 1 on Settings > Upgrade.
		 *
		 * @since 6.0.0
		 *
		 * @param string Opening HTML tag surrounding content.
		 * @param string The key for this phase of migration.
		 */
		$pre = (string) apply_filters( 'tec_events_custom_tables_v1_upgrade_before', "<div class='tec-ct1-upgrade--" . esc_attr( $this->key ) . "'>", $this->key );

		/**
		 * Fires at the bottom of the upgrade step 1 on Settings > Upgrade.
		 *
		 * @since 6.0.0
		 *
		 * @param string Opening HTML tag surrounding content.
		 * @param string The key for this phase of migration.
		 */
		$post = (string) apply_filters( 'tec_events_custom_tables_v1_upgrade_after', "</div>", $this->key );

		return $pre . $html . $post;
	}

	/**
	 * Will include and buffer any output generated by a template file, and return as a string.
	 *
	 * @since 6.0.0
	 *
	 * @param string $template Relative path to the migration template file.
	 * @param array  $vars     Variables to be put into local scope for the template.
	 *
	 * @return false|string|\WP_Error
	 */
	protected function get_template_html( $template, $vars = [] ) {
		$get_template_html_file_path = $this->template_directory . $template;
		extract( $vars, EXTR_OVERWRITE );
		if ( ! file_exists( $get_template_html_file_path ) ) {
			do_action( 'tribe_log',
				'error',
				'Error locating template file.', [
					'source'    => __METHOD__ . ':' . __LINE__,
					'file_path' => $get_template_html_file_path,
					'template'  => $template
				] );

			return new \WP_Error( 'tec-ct1-migration-phase-non-existent-template',
				null,
				[ 'template' => $template, 'phase_renderer' => $this ]
			);
		}

		ob_start();
		include $get_template_html_file_path;
		return ob_get_clean();
	}

	/**
	 * Sets the flag value indicating whether this renderer should tell
	 * the JS code to keep polling or not.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $should_poll Whether this rendered instance should tell
	 *                          the JS code to start, or keep, polling or not.
	 */
	public function should_poll( $should_poll ) {
		$this->poll = (bool) $should_poll;
	}
}