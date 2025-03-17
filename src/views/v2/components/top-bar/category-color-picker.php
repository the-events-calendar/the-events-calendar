<?php

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;

// Fetch all categories with colors.
$categories = get_terms([
	'taxonomy'   => Tribe__Events__Main::TAXONOMY,
	'hide_empty' => false,
]);

// Retrieve category colors & priorities, filtering only those with a primary color.
$meta_instance = tribe(Event_Category_Meta::class);
$category_colors = array_filter(array_map(
	fn($category) => [
		'slug'     => $category->slug,
		'name'     => $category->name,
		'priority' => is_numeric($priority = $meta_instance->set_term($category->term_id)->get(Meta_Keys::get_key('priority'))) ? (int) $priority : -1,
		'primary'  => $meta_instance->set_term($category->term_id)->get(Meta_Keys::get_key('primary')),
	],
	$categories
), fn($category) => !empty($category['primary'])); // Only keep categories with primary color

// Sort by priority (highest first).
usort($category_colors, fn($a, $b) => $b['priority'] <=> $a['priority']);

?>
<div class="tec-category-color-picker">
	<!-- Selected Colors Display -->
	<div class="tec-category-color-picker__colors">
		<?php foreach (array_slice($category_colors, 0, 5) as $category) : ?>
			<span class="tec-category-color-picker__color-circle"
				style="background-color: <?php echo esc_attr($category['primary']); ?>;">
			</span>
		<?php endforeach; ?>
	</div>
	<!-- Dropdown Icon -->
	<span class="tec-category-color-picker__dropdown-icon">
		<?php $this->template('components/icons/caret-down', ['classes' => ['tec-category-color-picker__dropdown-icon-svg']]); ?>
	</span>

	<!-- Dropdown List -->
	<div class="tec-category-color-picker__dropdown">
		<div class="tec-category-color-picker__dropdown-header">
			<span>Highlight a category</span>
			<button class="tec-category-color-picker__dropdown-close">✕</button>
		</div>
		<ul class="tec-category-color-picker__dropdown-list">
			<?php foreach ($category_colors as $category) : ?>
				<li class="tec-category-color-picker__dropdown-item">
					<label>
						<input type="checkbox" class="tec-category-color-picker__checkbox" data-category="<?php echo esc_attr($category['slug']); ?>">
						<span class="tec-category-color-picker__label"><?php echo esc_html($category['name']); ?></span>
						<span class="tec-category-color-picker__color-dot" style="background-color: <?php echo esc_attr($category['primary']); ?>;"></span>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
