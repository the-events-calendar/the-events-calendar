<?php
/**
 * Create meta box for editing pages in WordPress
 *
 * Compatible with custom post types since WordPress 3.0
 * Support input types: text, textarea, checkbox, checkbox list, radio box, select, wysiwyg, file, image, date, time, color
 *
 * @author Rilwis <rilwis@gmail.com>
 * @author Matt Wiebe
 * @author Modern Tribe, Inc.
 * @link http://www.deluxeblogtips.com/p/meta-box-script-for-wordpress.html
 * @example meta-box-usage.php Sample declaration and usage of meta boxes
 * @version: 3.2.2
 *
 * @license GNU General Public License v2.0
 */
if ( ! class_exists( 'Tribe_Meta_Box' ) ) {
/**
 * Meta Box class
 */
class Tribe_Meta_Box {

	protected $_meta_box;
	protected $_fields;

	// Create meta box based on given data
	public function __construct($meta_box) {
		// run script only in admin area
		if (!is_admin()) return;

		// assign meta box values to local variables and add it's missed values
		$this->_meta_box = $meta_box;
		// Cast pages to array
		$this->_meta_box['pages'] = (array) $meta_box['pages'];
		$this->_fields = $this->_meta_box['fields'];
		$this->add_missed_values();
		$this->register_scripts_and_styles();

		add_action('add_meta_boxes', array($this, 'add'));	// add meta box, using 'add_meta_boxes' for WP 3.0+
		add_action('save_post', array($this, 'save'));		// save meta box's data

		// check for some special fields and add needed actions for them
		$this->check_field_upload();
		$this->check_field_color();
		$this->check_field_date();
		$this->check_field_time();
		$this->check_field_wysiwyg();

		// load common js, css files
		// must enqueue for all pages as we need js for the media upload, too
		add_action('admin_enqueue_scripts', array(__CLASS__, 'js_css'));
	}

	function register_scripts_and_styles() {
		// change '\' to '/' in case using Windows
		$content_dir = str_replace('\\', '/', WP_CONTENT_DIR);
		$script_dir = str_replace('\\', '/', dirname(__FILE__));

		// get URL of the directory of current file, this works in both theme or plugin
		$base_url = trailingslashit( str_replace($content_dir, WP_CONTENT_URL, $script_dir) );
		$resources_url = apply_filters( 'tribe_apm_resources_url', $base_url . 'resources' );
		$resources_url = trailingslashit($resources_url);

		wp_register_style( 'tribe-meta-box', $resources_url . 'meta-box.css');
		wp_register_script('tribe-meta-box', $resources_url . 'meta-box.js', array('jquery'), null, true);

		wp_register_style('tribe-jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/' . self::get_jqueryui_ver() . '/themes/base/jquery-ui.css');
		wp_register_script('tribe-jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/' . self::get_jqueryui_ver() . '/jquery-ui.min.js', array('jquery'));
		wp_register_script('tribe-timepicker', 'https://github.com/trentrichardson/jQuery-Timepicker-Addon/raw/master/jquery-ui-timepicker-addon.js', array('tribe-jquery-ui'));
	}

	// Load common js, css files for the script
	static function js_css() {
		wp_enqueue_script( 'tribe-meta-box' );
		wp_enqueue_style( 'tribe-meta-box' );
	}

	/******************** BEGIN UPLOAD **********************/

	// Check field upload and add needed actions
	function check_field_upload() {
		if (!$this->has_field('image') && !$this->has_field('file')) return;

		add_action('post_edit_form_tag', array($this, 'add_enctype'));				// add data encoding type for file uploading

		// make upload feature works even when custom post type doesn't support 'editor'
		wp_enqueue_script('media-upload');
		add_thickbox();
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');

		add_filter('media_upload_gallery', array($this, 'insert_images'));			// process adding multiple images to image meta field
		add_filter('media_upload_library', array($this, 'insert_images'));
		add_filter('media_upload_image', array($this, 'insert_images'));

		// add_action('delete_post', array($this, 'delete_attachments'));			// delete all attachments when delete post
		add_action('wp_ajax_tribe_delete_file', array($this, 'delete_file'));			// ajax delete files
		add_action('wp_ajax_tribe_reorder_images', array($this, 'reorder_images'));	// ajax reorder images
	}

	// Add data encoding type for file uploading
	function add_enctype() {
		echo ' enctype="multipart/form-data"';
	}

	// Process adding images to image meta field, modifiy from 'Faster image insert' plugin
	function insert_images() {
		if (!isset($_POST['tribe-insert']) || empty($_POST['attachments'])) return;

		check_admin_referer('media-form');

		$nonce = wp_create_nonce('tribe_ajax_delete');
		$post_id = $_POST['post_id'];
		$id = $_POST['field_id'];

		// modify the insertion string
		$html = '';
		foreach ($_POST['attachments'] as $attachment_id => $attachment) {
			$attachment = stripslashes_deep($attachment);
			if (empty($attachment['selected']) || empty($attachment['url'])) continue;

			$li = "<li id='item_$attachment_id'>";
			$li .= "<img src='{$attachment['url']}' />";
			$li .= "<a title='" . __('Delete this image', 'tribe-apm') . "' class='tribe-delete-file' href='#' rel='$nonce|$post_id|$id|$attachment_id'>" . __('Delete', 'tribe-apm') . "</a>";
			$li .= "<input type='hidden' name='{$id}[]' value='$attachment_id' />";
			$li .= "</li>";
			$html .= $li;
		}

		media_send_to_editor($html);
	}

	// Delete all attachments when delete post
	function delete_attachments($post_id) {
		$attachments = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'attachment',
			'post_parent' => $post_id
		));
		if (!empty($attachments)) {
			foreach ($attachments as $att) {
				wp_delete_attachment($att->ID);
			}
		}
	}

	// Ajax callback for deleting files. Modified from a function used by "Verve Meta Boxes" plugin (http://goo.gl/LzYSq)
	function delete_file() {
		if (!isset($_POST['data'])) die();

		list($nonce, $post_id, $key, $attach_id) = explode('|', $_POST['data']);

		if (!wp_verify_nonce($nonce, 'tribe_ajax_delete')) die('1');

		// wp_delete_attachment($attach_id);
		delete_post_meta($post_id, $key, $attach_id);

		die('0');
	}

	// Ajax callback for reordering images
	function reorder_images() {
		if (!isset($_POST['data'])) die();

		list($order, $post_id, $key, $nonce) = explode('|',$_POST['data']);

		if (!wp_verify_nonce($nonce, 'tribe_ajax_reorder')) die('1');

		parse_str($order, $items);
		$items = $items['item'];
		$order = 1;
		foreach ($items as $item) {
			wp_update_post(array(
				'ID' => $item,
				'post_parent' => $post_id,
				'menu_order' => $order
			));
			$order++;
		}

		die('0');
	}

	/******************** END UPLOAD **********************/

	/******************** BEGIN OTHER FIELDS **********************/

	// Check field color
	function check_field_color() {
		if ($this->has_field('color') && self::is_edit_page()) {
			wp_enqueue_style('farbtastic');		// enqueue built-in script and style for color picker
			wp_enqueue_script('farbtastic');
		}
	}

	// Check field date
	function check_field_date() {
		if ($this->has_field('date') && self::is_edit_page()) {
			wp_enqueue_style('tribe-jquery-ui-css');
			wp_enqueue_script('tribe-jquery-ui');
		}
	}

	// Check field time
	function check_field_time() {
		if ($this->has_field('time') && self::is_edit_page()) {
			// add style and script, use proper jQuery UI version
			wp_enqueue_style('tribe-jquery-ui-css');
			wp_enqueue_script('tribe-jquery-ui');
			wp_enqueue_script('tribe-timepicker');
		}
	}

	// Check field WYSIWYG
	function check_field_wysiwyg() {
		if ($this->has_field('wysiwyg') && self::is_edit_page()) {
			add_action('admin_print_footer_scripts', 'wp_tiny_mce', 25);
		}
	}

	/******************** END OTHER FIELDS **********************/

	/******************** BEGIN META BOX PAGE **********************/

	// Add meta box for multiple post types
	function add() {
		foreach ( (array) $this->_meta_box['pages'] as $page) {
			add_meta_box($this->_meta_box['id'], $this->_meta_box['title'], array($this, 'show'), $page, $this->_meta_box['context'], $this->_meta_box['priority']);
		}
	}

	// Callback function to show fields in meta box
	function show() {
		global $post;

		wp_nonce_field(basename(__FILE__), 'tribe_meta_box_nonce');
		echo '<table class="form-table tribe-meta">';

		foreach ($this->_fields as $field) {
			$meta = $this->retrieve_meta_for_field($field, $post);
			echo '<tr>';
			// call separated methods for displaying each type of field
			call_user_func(array($this, 'show_field_' . $field['type']), $field, $meta);
			echo '</tr>';
		}
		echo '</table>';
	}

	function retrieve_meta_for_field($field, $post) {
		$meta = get_post_meta($post->ID, $field['meta'], !$field['multiple']);
		$meta = ! empty($meta) ? $meta : $field['std'];
		$meta = ( is_array($meta) ) ? self::array_map_deep('esc_attr',$meta) : esc_attr($meta);
		return $meta;
	}

	function array_map_deep( $callback, $data ) {
		$results =	array();
		$args = array();
		if(func_num_args() > 2)
			$args =	(array) array_shift(array_slice(func_get_args(),2));
		foreach($data as $key=>$value) {
			if(is_array($value)) {
				array_unshift($args,$value);
				array_unshift($args,$callback);
				$results[$key]	=	call_user_func_array(array('self','array_map_deep'),$args);
			}
			else {
				array_unshift($args,$value);
				$results[$key]	=	call_user_func_array($callback,$args);
			}
		}
		return $results;
	}

	/******************** END META BOX PAGE **********************/

	/******************** BEGIN META BOX FIELDS **********************/

	function show_field_begin($field, $meta) {
		if ( isset($field['span']) && 'full' === $field['span'] ) {
			echo "<td colspan='2' class='full-span {$field['type']}'><label for='{$field['meta']}'>{$field['name']}</label>";
		} else {
			echo "<th scope='row' class='label-row'><label for='{$field['meta']}'>{$field['name']}</label></th><td class='{$field['type']}'>";
		}
	}

	function show_field_end($field, $meta) {
		if ( isset($field['desc']) ) {
			echo "<p class='description'>{$field['desc']}</p>";
		}
		echo "</td>";
	}

	function show_field_text($field, $meta) {
		$this->show_field_begin($field, $meta);
		echo "<input type='text' class='tribe-text' name='{$field['meta']}' id='{$field['meta']}' value='$meta' size='30' />";
		$this->show_field_end($field, $meta);
	}

	function show_field_textarea($field, $meta) {
		$this->show_field_begin($field, $meta);
		echo "<textarea class='tribe-textarea large-text' name='{$field['meta']}' id='{$field['meta']}' cols='60' rows='10'>$meta</textarea>";
		$this->show_field_end($field, $meta);
	}

	function show_field_select($field, $meta) {
		if (!is_array($meta)) $meta = (array) $meta;
		$this->show_field_begin($field, $meta);
		echo "<select class='tribe-select' name='{$field['meta']}" . ($field['multiple'] ? "[]' id='{$field['meta']}' multiple='multiple'" : "'") . ">";
		foreach ($field['options'] as $key => $value) {
			echo "<option value='$key'" . selected(in_array($key, $meta), true, false) . ">$value</option>";
		}
		echo "</select>";
		$this->show_field_end($field, $meta);
	}

	function show_field_radio($field, $meta) {
		$this->show_field_begin($field, $meta);
		foreach ($field['options'] as $key => $value) {
			echo "<input type='radio' class='tribe-radio' name='{$field['meta']}' value='$key'" . checked($meta, $key, false) . " /> $value ";
		}
		$this->show_field_end($field, $meta);
	}

	function show_field_checkbox($field, $meta) {
		$this->show_field_begin($field, $meta);
		echo "<label><input type='checkbox' class='tribe-checkbox' name='{$field['meta']}' id='{$field['meta']}'" . checked(!empty($meta), true, false) . " /> {$field['desc']}</label></td>";
	}

	function show_field_wysiwyg($field, $meta) {
		$this->show_field_begin($field, $meta);
		echo "<textarea class='tribe-wysiwyg theEditor large-text' name='{$field['meta']}' id='{$field['meta']}' cols='60' rows='10'>$meta</textarea>";
		$this->show_field_end($field, $meta);
	}

	function show_field_file($field, $meta) {
		global $post;

		if (!is_array($meta)) $meta = (array) $meta;

		$this->show_field_begin($field, $meta);
		if ( isset($field['desc']) ) {
			echo "<p class='description'>{$field['desc']}</p>";
		}

		if (!empty($meta)) {
			$nonce = wp_create_nonce('tribe_ajax_delete');
			echo '<div style="margin-bottom: 10px"><strong>' . __('Uploaded files', 'tribe-apm') . '</strong></div>';
			echo '<ol class="tribe-upload">';
			foreach ($meta as $att) {
				// if (wp_attachment_is_image($att)) continue; // what's image uploader for?
				echo "<li>" . wp_get_attachment_link($att, '' , false, false, ' ') . " (<a class='tribe-delete-file' href='#' rel='$nonce|{$post->ID}|{$field['meta']}|$att'>" . __('Delete', 'tribe-apm') . "</a>)</li>";
			}
			echo '</ol>';
		}

		// show form upload
		echo "<div style='clear: both'><strong>" . __('Upload new files', 'tribe-apm') . "</strong></div>
			<div class='new-files'>
				<div class='file-input'><input type='file' name='{$field['meta']}[]' /></div>
				<a class='tribe-add-file' href='#'>" . __('Add another file', 'tribe-apm') . "</a>
			</div>
		</td>";
	}

	function show_field_image($field, $meta) {
		global $wpdb, $post;

		if (!is_array($meta)) $meta = (array) $meta;

		$this->show_field_begin($field, $meta);
		if ( isset($field['desc']) ) {
			echo "<p class='description'>{$field['desc']}</p>";
		}

		$nonce_delete = wp_create_nonce('tribe_ajax_delete');
		$nonce_sort = wp_create_nonce('tribe_ajax_reorder');

		echo "<input type='hidden' class='tribe-images-data' value='{$post->ID}|{$field['meta']}|$nonce_sort' />
			  <ul class='tribe-images tribe-upload' id='tribe-images-{$field['meta']}'>";

		// re-arrange images with 'menu_order', thanks Onur
		if (!empty($meta)) {
			$meta = implode(',', $meta);
			$images = $wpdb->get_col("
				SELECT ID FROM $wpdb->posts
				WHERE post_type = 'attachment'
				AND ID in ($meta)
				ORDER BY menu_order ASC
			");
			foreach ($images as $image) {
				$src = wp_get_attachment_image_src($image);
				$src = $src[0];

				echo "<li id='item_$image'>
						<img src='$src' />
						<a title='" . __('Delete this image', 'tribe-apm') . "' class='tribe-delete-file' href='#' rel='$nonce_delete|{$post->ID}|{$field['meta']}|$image'>" . __('Delete', 'tribe-apm') . "</a>
						<input type='hidden' name='{$field['meta']}[]' value='$image' />
					</li>";
			}
		}
		echo '</ul>';

		echo "<a href='#' class='tribe-upload-button button' rel='{$post->ID}|{$field['meta']}'>" . __('Add more images', 'tribe-apm') . "</a>";
		echo '</td>';
	}

	function show_field_color($field, $meta) {
		if (empty($meta)) $meta = '#';
		$this->show_field_begin($field, $meta);
		echo "<input class='tribe-color' type='text' name='{$field['meta']}' id='{$field['meta']}' value='$meta' size='8' />
			  <a href='#' class='tribe-color-select' rel='{$field['meta']}'>" . __('Select a color', 'tribe-apm') . "</a>
			  <div style='display:none' class='tribe-color-picker' rel='{$field['meta']}'></div>";
		$this->show_field_end($field, $meta);
	}

	function show_field_checkbox_list($field, $meta) {
		if (!is_array($meta)) $meta = (array) $meta;
		$this->show_field_begin($field, $meta);
		$html = array();
		foreach ($field['options'] as $key => $value) {
			$html[] = "<input type='checkbox' class='tribe-checkbox_list' name='{$field['meta']}[]' value='$key'" . checked(in_array($key, $meta), true, false) . " /> $value";
		}
		echo implode('<br />', $html);
		$this->show_field_end($field, $meta);
	}

	function show_field_date($field, $meta) {
		$this->show_field_begin($field, $meta);
		echo "<input type='text' class='tribe-date' name='{$field['meta']}' id='{$field['meta']}' rel='{$field['format']}' value='$meta' size='30' />";
		$this->show_field_end($field, $meta);
	}

	function show_field_time($field, $meta) {
		$this->show_field_begin($field, $meta);
		echo "<input type='text' class='tribe-time' name='{$field['meta']}' id='{$field['meta']}' rel='{$field['format']}' value='$meta' size='30' />";
		$this->show_field_end($field, $meta);
	}

	function show_field_text_multi($field, $meta) {
		$this->show_field_begin($field, $meta);
		$meta = (array) $meta;
		$hide_remove = ( count($meta) < 2 ) ? ' hide-remove' : '';
		$size = floor( 36 / count($field['ids']) );

		echo '<div class="tribe-multi-text-wrap'.$hide_remove.'">';
		foreach ( $meta as $k => $v ) {
			echo '<div class="tribe-multi-text">';
			foreach ( $field['ids'] as $key => $id ) {
				$val = (isset($v[$id])) ? $v[$id] : '';
				$name = "{$field['meta']}[{$id}][]";
				$ph = $field['placeholders'][$key];
				echo "<input type='text' name='{$name}' value='{$val}' size='{$size}' placeholder='{$ph}' /> ";
			}
			echo "<a class='tribe-add'>+</a><a class='tribe-remove'>-</a></div>";
		}
		echo '</div>';
		$this->show_field_end($field, $meta);
	}

	function show_field_html($field, $meta) {
		$this->show_field_begin($field, $meta);
		echo $field['html'];
		$this->show_field_end($field, $meta);
	}

	function show_field_post2post($field, $meta) {
		$this->show_field_begin($field, $meta);
		if ( ! isset($field['dropdown_title']) ) {
			$post_type_object = get_post_type_object($field['post_type']);
			$field['dropdown_title'] = sprintf( 'Select %s', $post_type_object->labels->singular_name );
		}

		$this->dropdown_posts(array(
			'post_type' => $field['post_type'],
			'show_option_none' => $field['dropdown_title'],
			'name' => $field['meta'],
			'class' => 'p2p-drop'
		));

		$list_items = '';
		$list_item_template = '<li><label><input type="checkbox" name="'.$field['meta'].'[]" value="%s" checked="checked" /> %s</label></li>';
		if ( ! empty($meta) ) {
			foreach ( (array) $meta as $post_id ) {
				$p = get_post($post_id);
				$list_items .= sprintf($list_item_template, $p->ID, $p->post_title);
			}
		}

		echo '<ul class="p2p-connected">'.$list_items.'</ul>';
		$this->show_field_end($field, $meta);
	}

	/******************** END META BOX FIELDS **********************/

	/******************** BEGIN META BOX SAVE **********************/

	// Save data from meta box
	function save($post_id) {
		global $post_type;
		$post_type_object = get_post_type_object($post_type);

		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)						// check autosave
		|| (!isset($_POST['post_ID']) || $post_id != $_POST['post_ID'])			// check revision
		|| (!in_array($post_type, $this->_meta_box['pages']))					// check if current post type is supported
		|| (!check_admin_referer(basename(__FILE__), 'tribe_meta_box_nonce'))		// verify nonce
		|| (!current_user_can($post_type_object->cap->edit_post, $post_id))) {	// check permission
			return $post_id;
		}

		foreach ($this->_fields as $field) {
			$name = $field['meta'];
			$type = $field['type'];
			$old = get_post_meta($post_id, $name, !$field['multiple']);
			$new = isset($_POST[$name]) ? $_POST[$name] : ($field['multiple'] ? array() : '');

			// validate meta value
			if (class_exists('Tribe_Meta_Box_Validate') && method_exists('Tribe_Meta_Box_Validate', $field['validate_func'])) {
				$new = call_user_func(array('Tribe_Meta_Box_Validate', $field['validate_func']), $new);
			}

			// call defined method to save meta value, if there's no methods, call common one
			$save_func = 'save_field_' . $type;
			if (method_exists($this, $save_func)) {
				call_user_func(array($this, 'save_field_' . $type), $post_id, $field, $old, $new);
			} else {
				$this->save_field($post_id, $field, $old, $new);
			}
		}
	}

	// Common functions for saving field
	function save_field($post_id, $field, $old, $new) {
		$name = $field['meta'];

		delete_post_meta($post_id, $name);
		if ($new === '' || $new === array()) return;

		if ($field['multiple']) {
			foreach ($new as $add_new) {
				add_post_meta($post_id, $name, $add_new, false);
			}
		} else {
			update_post_meta($post_id, $name, $new);
		}

	}

	function save_field_wysiwyg($post_id, $field, $old, $new) {
		$new = wpautop($new);
		$this->save_field($post_id, $field, $old, $new);
	}

	function save_field_file($post_id, $field, $old, $new) {
		$name = $field['meta'];
		if (empty($_FILES[$name])) return;

		self::fix_file_array($_FILES[$name]);

		foreach ($_FILES[$name] as $position => $fileitem) {
			$file = wp_handle_upload($fileitem, array('test_form' => false));

			if (empty($file['file'])) continue;
			$filename = $file['file'];

			$attachment = array(
				'post_mime_type' => $file['type'],
				'guid' => $file['url'],
				'post_parent' => $post_id,
				'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
				'post_content' => ''
			);
			$id = wp_insert_attachment($attachment, $filename, $post_id);
			if (!is_wp_error($id)) {
				wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $filename));
				add_post_meta($post_id, $name, $id, false);	// save file's url in meta fields
			}
		}
	}

	function save_field_text_multi($post_id, $field, $old, $new) {
		$data = array();
		$new = (array) $new;
		foreach ( $field['ids'] as $id ) {
			foreach ( $new[$id] as $key => $value ) {
				$data[$key][$id] = $value;
			}
		}
		if ( ! empty($data) ) {
			update_post_meta($post_id, $field['meta'], $data);
		}
	}

	function save_field_html() {
		// do nothing
	}

	function save_field_post2post($post_id, $field, $old, $new) {
		delete_post_meta($post_id, $field['meta']);
		$new = (array) $new;
		$new = array_unique($new);
		foreach ( $new as $id ) {
			add_post_meta($post_id, $field['meta'], $id);
		}
	}

	/******************** END META BOX SAVE **********************/

	/******************** BEGIN HELPER FUNCTIONS **********************/

	function dropdown_posts( $args = '' ) {
		$defaults = array(
			'numberposts' => -1, 'post_type' => 'post',
			'depth' => 0, 'selected' => 0, 'echo' => 1,
			'name' => 'page_id', 'id' => '', 'class' => '',
			'show_option_none' => '', 'show_option_no_change' => '',
			'option_none_value' => ''
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		$get_posts_args = compact( 'post_type', 'numberposts' );
		$pages = get_posts($get_posts_args);
		$output = '';
		$name = esc_attr($name);
		// Back-compat with old system where both id and name were based on $name argument
		if ( empty($id) )
			$id = $name;

		if ( ! empty($pages) ) {
			$output = "<select name=\"$name\" id=\"$id\" class=\"$class\">\n";
			if ( $show_option_no_change )
				$output .= "\t<option value=\"-1\">$show_option_no_change</option>";
			if ( $show_option_none )
				$output .= "\t<option value=\"" . esc_attr($option_none_value) . "\">$show_option_none</option>\n";
			$output .= walk_page_dropdown_tree($pages, $depth, $r);
			$output .= "</select>\n";
		}

		$output = apply_filters('dropdown_posts-'.$post_type, $output);

		if ( $echo )
			echo $output;

		return $output;
	}

	// Add missed values for meta box
	function add_missed_values() {
		// default values for meta box
		$this->_meta_box = array_merge(array(
			'context' => 'normal',
			'priority' => 'high',
			'pages' => array('post')
		), $this->_meta_box);

		// default values for fields
		foreach ($this->_fields as &$field) {
			$multiple = in_array($field['type'], array('checkbox_list', 'file', 'image'));
			$std = $multiple ? array() : '';
			$format = 'date' == $field['type'] ? 'yy-mm-dd' : ('time' == $field['type'] ? 'hh:mm' : '');

			$field = array_merge(array(
				'multiple' => $multiple,
				'std' => $std,
				'desc' => '',
				'format' => $format,
				'validate_func' => ''
			), $field);
		}
	}

	// Check if field with $type exists
	function has_field($type) {
		foreach ($this->_fields as $field) {
			if ($type == $field['type']) return true;
		}
		return false;
	}

	// Check if current page is edit page
	static function is_edit_page() {
		global $pagenow;
		return in_array($pagenow, array('post.php', 'post-new.php'));
	}

	/**
	 * Fixes the odd indexing of multiple file uploads from the format:
	 *	 $_FILES['field']['key']['index']
	 * To the more standard and appropriate:
	 *	 $_FILES['field']['index']['key']
	 */
	static function fix_file_array(&$files) {
		$output = array();
		foreach ($files as $key => $list) {
			foreach ($list as $index => $value) {
				$output[$index][$key] = $value;
			}
		}
		$files = $output;
	}

	// Get proper jQuery UI version to not conflict with WP admin scripts
	static function get_jqueryui_ver() {
		global $wp_version;
		if (version_compare($wp_version, '3.5', '>=')) {
			return '1.9.2';
		}

		if (version_compare($wp_version, '3.1', '>=')) {
			return '1.8.10';
		}

		return '1.7.3';
	}

	/******************** END HELPER FUNCTIONS **********************/
}

} // end if class_exists()