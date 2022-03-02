<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

class Phase_View_Renderer {
	protected $key;
	protected $template_path;
	protected $nodes = [];

	public function __construct( $key, $file_path, $vars = [] ) {
		$this->key           = $key;
		$this->template_path = $file_path;
		$this->vars          = $vars;
	}

	public function register_node( $key, $selector, $template, $vars = [] ) {
		$this->nodes[] = [ 'target' => $selector, 'template' => $template, 'key' => $key, 'vars' => $vars ];
	}

	protected function compile_nodes() {
		// Base on what nodes are registered, compile and return the structured data
		$nodes = [];
		foreach ( $this->nodes as $node ) {
			$html    = self::get_template_html( $node['template'] );
			$nodes[] = [
				'html'   => $html,
				'hash'   => sha1( $html ),
				'key'    => $node['key'],
				'target' => $node['target']
			];
		}

		return $nodes;
	}

// @todo move hooks in here
	public function compile() {
		return [
			'key'   => $this->key,
			// Based on what is registered, render the parent template
			'html'  => self::get_template_html( $this->template_path, $this->vars ),
			'nodes' => $this->compile_nodes()
		];
	}

	protected static function get_template_html( $template, $vars = [] ) {
		extract( $vars );
		ob_start();
		include $template;

		return ob_get_clean();
	}
}