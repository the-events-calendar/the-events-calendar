<?php

/**
 * Class TribeEventsPro_RewriteRuleGenerator
 */
class TribeEventsPro_RewriteRuleGenerator {
	/** @var WP_Rewrite */
	private $rewrite = null;
	private $base = '';
	private $cat_base = '';
	private $tag_base = '';

	public function __construct( WP_Rewrite $wp_rewrite ) {
		$this->rewrite = $wp_rewrite;
	}

	public function set_base( $base ) {
		$this->base = $base;
	}

	public function set_cat_base( $base ) {
		$this->cat_base = $base;
	}

	public function set_tag_base( $base ) {
		$this->tag_base = $base;
	}

	public function get_week_rules( $week_base ) {
		$rules = array(
			$this->base . $week_base . '/?$'                                    => 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=week',
			$this->base . $week_base . '/(\d{2})/?$'                            => 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=week' . '&eventDate=' . $this->rewrite->preg_index( 1 ),
			$this->base . $week_base . '/(\d{4}-\d{2}-\d{2})/?$'                => 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=week' . '&eventDate=' . $this->rewrite->preg_index( 1 ),
			$this->cat_base . '([^/]+)/' . $week_base . '/?$'                   => 'index.php?tribe_events_cat=' . $this->rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=week',
			$this->cat_base . '([^/]+)/' . $week_base . '/(\d{4}-\d{2}-\d{2})$' => 'index.php?tribe_events_cat=' . $this->rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=week' . '&eventDate=' . $this->rewrite->preg_index( 3 ),
			$this->tag_base . '([^/]+)/' . $week_base . '/?$'                   => 'index.php?tag=' . $this->rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=week',
			$this->tag_base . '([^/]+)/' . $week_base . '/(\d{4}-\d{2}-\d{2})$' => 'index.php?tag=' . $this->rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=week' . '&eventDate=' . $this->rewrite->preg_index( 3 )
		);

		return $rules;
	}

	public function get_photo_rules( $photo_base ) {
		$rules = array(
			$this->base . $photo_base . '/?$'                     => 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=photo',
			$this->base . $photo_base . '/(\d{4}-\d{2}-\d{2})/?$' => 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=photo' . '&eventDate=' . $this->rewrite->preg_index( 1 ),
			$this->cat_base . '([^/]+)/' . $photo_base . '/?$'    => 'index.php?tribe_events_cat=' . $this->rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=photo',
			$this->tag_base . '([^/]+)/' . $photo_base . '/?$'    => 'index.php?tag=' . $this->rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=photo',
		);

		return $rules;
	}

	public function get_taxonomy_rules() {
		$rules = array(
			$this->cat_base . '([^/]+)/(\d{4}-\d{2}-\d{2})/?$' => 'index.php?tribe_events_cat=' . $this->rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=day' . '&eventDate=' . $this->rewrite->preg_index( 3 ),
			$this->tag_base . '([^/]+)/(\d{4}-\d{2}-\d{2})/?$' => 'index.php?tag=' . $this->rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=day' . '&eventDate=' . $this->rewrite->preg_index( 3 ),
		);

		return $rules;
	}
}
 