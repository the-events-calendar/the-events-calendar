<?php
/**
 * Abstract class for category colors.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Category
 */

namespace TEC\Events\Category_Colors\Category;

/**
 * Abstract class representing category colors.
 * This class provides a structured way to handle category-based colors,
 * including background, border, and text colors.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Category
 */
abstract class Category_Abstract {
	/**
	 * Category term ID.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected $term_id;

	/**
	 * Category priority.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected $priority = -1;

	/**
	 * Background color.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $background = '';

	/**
	 * Border color.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $border = '';

	/**
	 * Text color.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $text = '';

	/**
	 * Taxonomy name.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static $taxonomy = '';

	/**
	 * Constructs the category color object.
	 *
	 * @since TBD
	 *
	 * @param int   $term_id The category term ID.
	 * @param array $data    The category color data.
	 */
	public function __construct( int $term_id, array $data ) {
		$this->term_id    = $term_id;
		$this->priority   = $data['priority'] ?? -1;
		$this->border     = $data['primary'] ?? '';
		$this->background = $data['secondary'] ?? '';
		$this->text       = $data['text'] ?? '';
	}

	/**
	 * Retrieves the taxonomy this class is handling.
	 *
	 * @since TBD
	 *
	 * @return string The taxonomy name.
	 */
	public static function get_taxonomy(): string {
		return static::$taxonomy;
	}

	/**
	 * Returns the CSS class name for the category.
	 *
	 * @since TBD
	 *
	 * @return string The generated CSS class name.
	 */
	public function get_css_class(): string {
		return 'tribe-events-category-' . $this->term_id;
	}

	/**
	 * Retrieves the structured schema for the category.
	 *
	 * @since TBD
	 *
	 * @return array The structured category data.
	 */
	public function get_schema(): array {
		return [
			'class'      => $this->get_css_class(),
			'priority'   => $this->priority,
			'background' => $this->background,
			'border'     => $this->border,
			'text'       => $this->text,
		];
	}
}
