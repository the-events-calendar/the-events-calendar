<?php

/**
 * A class to set columns in a WordPress edit page.
 *
 *
 */

if ( ! class_exists( 'Tribe_Columns' ) ) {

class Tribe_Columns {

	private $post_type;
	private $columns = array();
	private $active = array();
	private $override = false;
	private $fallback;
	private $column_headers = array();

	private $url;

	private $prefix = 'tribe_col_';
	private $user_meta = 'tribe_columns_';
	private $nonce = 'tribe_columns';

	private $columns_example;

	/**
	 * Sets up the class to work. Duh.
	 *
	 * @param string $post_type The type of post_type to modify the edit/manage view
	 * @param array $columns The columns that should be added
	 * @param array $active The active columns from $columns. Priority: 1) instantiation argument 2) user meta 3) all $columns
	 * @param array $fallback If no columns are active, showing all of them might suck. These are the fallbacks for that case.
	 *
	 * @return void
	 * @author Matt Wiebe
	 **/

	public function __construct( $post_type, $columns = array(), $active = array(), $fallback = array() ) {

		$this->columns_example = array(
			'column_id' => array(
				'name' => __('Column Name', 'tribe-apm'),
				'meta' => '_some_meta' // in most cases, this piece of meta will be queried to provide column contents
			)
		);

		$this->nonce .= $post_type; // keep it tidy
		$this->user_meta .= $post_type;

		$this->post_type = $post_type;
		$this->set_active($active);
		$this->set_columns($columns);
		$this->set_fallback($fallback);

		$this->add_actions_and_filters();

		$this->url = trailingslashit( plugins_url( '', __FILE__ ) );
	}

	// PUBLIC API METHODS

	/**
	 * Sets the columns to be shown in the list view for associated post type
	 * See documentation for column array construction
	 * @param $columns array of column arrays
	 */
	public function set_columns($columns = array() ) {
		if ( ! empty($columns) ) {
			$this->columns = $columns;
			$this->alphabetize_columns();
		}
	}

	/**
	 * Adds columns to the current set of registered columns
	 * See documentation for column array construction
	 * @param $columns array of column arrays
	 */
	public function add_columns ( $columns = array() ) {
		if ( ! empty($columns) ) {
			$this->columns = array_merge( $this->columns, $columns );
			$this->alphabetize_columns();
		}
	}

	/**
	 * Whether registered columns should be shown after or before WordPress defaults
	 * @param $append bool 'before' or 'after'
	 */
	public function set_override($override) {
		$this->override = (bool) $override;
	}

	/**
	 * Set fallback defaults for when custom column are cleared
	 * When fallback is empty, all columns will be shown on reset
	 * @param $fallback array one-dimensional array of column keys
	 */
	public function set_fallback($fallback = array() ) {
		$this->fallback = (array) $fallback;
	}

	/**
	 * Explicitly set an active set of columns. Usually not set.
	 * This will override any other internal method of retrieving active columns
	 * @param $active array one-dimensional array of column keys
	 */
	public function set_active($active = array() ) {
		$this->active = (array) $active;
	}

	/**
	 * Outputs the drag & drop columns view.
	 * Expects to be inside a form
	 */
	public function output_form() {
		wp_nonce_field($this->nonce, $this->nonce, false);
		$headers = $this->get_column_headers();

		// make sure there are no strays
		$this->sweep_empties();

		// key 'em up
		$active = array();
		foreach ( $this->active as $v) {
			$active[$v] = $v;
		}
		$inactive = array_diff_key($headers, $active);
		unset($active); ?>

		<select name="tribe-cols-drop" id="tribe-cols-drop"><?php
			echo '<option value="0">'. __('Add a Column', 'tribe-apm').'</option>';
			foreach ( $inactive as $key => $value ) {
				$name = ( is_string($value) ) ? $value : $value['name'];
				if ( empty($name) ) {
					continue;
				}
				if ( strstr( $name, 'img alt="Comments"' ) !== false ) {
					$name = __( 'Comments' );
				}
				echo '<option value="'.$key.'">'.$name.'</option>';
			}
		?></select>

		<ul id="tribe-cols-active"><?php
			$i = 1;
			foreach( $this->active as $v ) {
				echo '<li>';
				echo '<input type="hidden" name="'.$this->prefix.$i.'" value="'.$v.'" />';
				echo $headers[$v];
				echo '<b class="close">×</b>';
				echo '</li>';
				$i++;
			}

		?>

		</ul>
<script> var Tribe_Columns = <?php echo json_encode( array(
		'prefix' => $this->prefix,
		'item' => '<li>%name%<b class="close">×</b></li>',
		'input' => '<input type="hidden" name="" value="%value%" />'
		)); ?>; </script><?php	echo "\n";

	}


	// CALLBACKS

	private function add_actions_and_filters() {
		add_action( 'manage_'.$this->post_type.'_posts_custom_column', array($this, 'custom_columns'), 10, 2);
		add_filter( 'manage_'.$this->post_type.'_posts_columns', array($this, 'column_headers'));
		add_filter( 'manage_edit-'.$this->post_type.'_sortable_columns', array($this, 'sortable_columns') );
		add_action( 'load-edit.php', array($this, 'init_active'), 10 );
		add_action( 'load-edit.php', array($this, 'save_active'), 20 );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue') );
		add_filter( 'tribe_columns_column', array($this, 'date_column'), 10, 3 );
		// Needs to be executed when quick edit is saved, hence the following line.
		add_action( 'save_post', array( $this, 'init_active' ) );
	}

	public function date_column($value, $column_id, $column) {
		if ( ! empty($value) && self::is_date($column) && isset($column['date_format']) ) {
			$value = date( $column['date_format'], strtotime($value) );
		}
		return $value;
	}

	public function sortable_columns($columns) {
		$sort_prefix = apply_filters( 'tribe_sort_prefix', 'tribe_sort_' );
		$new_cols = array();
		foreach ( $this->columns as $k => $v ) {
			$sort_key = false;
			// Custom Type must have the sortable flag set
			if ( isset($v['custom_type']) && isset($v['sortable']) && $v['sortable'] ) {
				$sort_key = $v['custom_type'];
			}
			// Meta is always sortable
			else if ( isset($v['meta']) ) {
				$sort_key = $v['meta'];
			}

			// Got it?
			if ( $sort_key ) {
				$new_cols[$k] = $sort_prefix . $sort_key;
			}

		} // endforeach
		$columns = array_merge($columns, $new_cols);
		return $columns;
	}

	public function enqueue() {
		global $current_screen;
		$resources_url = apply_filters( 'tribe_apm_resources_url', $this->url . 'resources' );
		$resources_url = trailingslashit($resources_url);
		if ( $current_screen->id == 'edit-' . $this->post_type ) {
			wp_enqueue_script('tribe-columns', $resources_url . 'tribe-columns.js', array('jquery-ui-sortable'), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ) );
		}
	}

	public function init_active() {
		global $userdata;

		// get active from usermeta
		if ( empty( $this->active ) ) {
			$this->active = get_user_meta( $userdata->ID, $this->user_meta, true );
		}
		// if empty, try the fallback
		if ( empty( $this->active) ) {
			$this->active = $this->fallback;
		}
		// if still empty, show everything. God help us.
		if ( empty($this->active) ) {
			$this->active = array_keys( $this->get_column_headers() );
		}
	}

	public function save_active() {
		if ( ! isset($_REQUEST[$this->nonce]) || ! wp_verify_nonce( $_REQUEST[$this->nonce], $this->nonce ) ) {
			return;
		}

		// Clear button on frontend
		if ( isset($_POST['tribe-clear']) ) {
			$this->reset_active();
			return;
		}

		$data = $_REQUEST;
		$i = 1;
		while ( $i <= count( $this->get_column_headers() ) ) {
			if ( isset( $data[$this->prefix . $i] ) ) {
				$to_save[] = $data[$this->prefix . $i];
			}
			$i++;
		}

		if ( ! empty($to_save) ) {
			global $userdata;
			update_user_meta( $userdata->ID, $this->user_meta, $to_save );
			$this->using_fallbacks = false;
			$this->active = $to_save;
		}

	}

	public function column_headers( $columns ) {
		if ( ! $this->is_our_post_type() )
			return $columns;

		if ( ! empty( $this->active ) ) {
			$headers = $this->get_column_headers(false);
			$columns = array('cb' => $headers['cb']);
			foreach ( $this->active as $v ) {
				$columns[$v] = $headers[$v];
			}
		}
		do_action( 'tribe_apm_headers_' . $this->post_type, $columns );
		return $columns;
	}

	public function custom_columns( $column_id, $post_id ) {
		if ( ! $this->is_our_post_type() )
			return;
		$post = get_post($post_id);
		if ( array_key_exists( $column_id, $this->columns ) ) {
			$column = $this->columns[$column_id];
			// meta ?
			if ( isset( $column['meta'] ) ) {
				$value = get_post_meta( $post_id, $column['meta'], true );
				// Do an options map for prettiness
				if ( isset($column['options']) && isset($column['options'][$value]) ) {
					$value = $column['options'][$value];
				}
			}
			// taxonomy?
			else if ( isset( $column['taxonomy'] ) ) {
				$value = $this->taxonomy_column($post_id, $column['taxonomy'] );
			}
			// custom type?
			else if ( isset( $column['custom_type'] ) ) {
				$value = apply_filters( 'tribe_custom_column'.$column['custom_type'], '', $column_id, $post_id );
			}

			// filter time
			$value = apply_filters('tribe_columns_column', $value, $column_id, $column, $post_id);
			$value = apply_filters('tribe_columns_column_'.$column_id, $value, $post_id);

			// if name isn't set, and first in $this->columns, let's link it up
			if ( ! array_key_exists( 'title', $this->active ) && reset($this->active) == $column_id ) {
				// if value is empty, we've got a problem for, you know, clicking.
				$value = ( empty($value) ) ? '–Blank–' : $value;
				$value = sprintf('<strong><a href="%s" title="%s">%s</a>%s</strong>',
					get_admin_url(null, "post.php?post={$post_id}&action=edit"),
					'Edit &lsquo;'.strip_tags(esc_attr(get_the_title($post_id))) . '&rsquo;',
					$value,
					// Post status, if not published
					( $post->post_status !== 'publish' ) ? " - {$post->post_status}" : ''
				);
			}

			echo $value;
		}
	}


	// UTLITIES AND INTERNAL METHODS

	protected function sweep_empties() {
		$headers = $this->get_column_headers();
		foreach ( $headers as $k => $v ) {
			if ( empty($v) ) {
				unset( $this->active[$k] );
			}
		}
	}

	private function get_column_headers($omit_checkbox = true) {
		if ( ! empty( $this->column_headers) ) {
			$headers = $this->column_headers;
		}
		else {
			// If we're nuking the existing columns, still provide checkboxes
			if ( $this->override ) {
				$headers = array('cb' => '<input type="checkbox" />');
			}
			else {
				// Cause infinite loops get boring after a while
				remove_filter( 'manage_'.$this->post_type.'_posts_columns', array($this, 'column_headers'));
				$this->load_list_table();

				$list = new WP_Posts_List_Table();
				$headers = $list->get_columns();

				add_filter( 'manage_'.$this->post_type.'_posts_columns', array($this, 'column_headers'));
			}
			foreach ( $this->columns as $key => $value ) {
				$headers[$key] = $value['name'];
			}
			$this->column_headers = $headers;
		}
		if ( $omit_checkbox && isset($headers['cb'])) {
			unset($headers['cb']);
		}
		return $headers;
	}

	private function load_list_table() {
		if ( ! class_exists('WP_Posts_List_Table') ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );
		}
	}

	private function alphabetize_columns() {
		$columns = (array) $this->columns;
		if ( empty($columns) )
			return;

		foreach ( $columns as $k => $v ) {
			if ( ! empty( $v['name']) )
				$temp[$k] = $v['name'];
		}
		asort($temp);

		foreach ( $temp as $k => $v ) {
			$alpha_columns[$k] = $columns[$k];
		}

		$this->columns = $alpha_columns;
		unset($alpha_columns, $temp);
	}

	public function log($data = array() ) {
		error_log(print_r($data,1));
	}

	protected function taxonomy_column($post_id, $taxonomy) {
		$terms = get_the_terms($post_id, $taxonomy);
		if ( ! $terms || empty($terms) ) return '&ndash;';
		$ret = array();
		$post = get_post($post_id);
		foreach ( $terms as $term ) {
			$url = add_query_arg(array(
				'post_type' => $post->post_type,
				$taxonomy => $term->slug
			), admin_url('edit.php') );
			$ret[] = sprintf('<a href="%s">%s</a>', $url, $term->name);
		}
		return implode(', ', $ret);
	}

	private function reset_active() {
		global $userdata;
		delete_user_meta( $userdata->ID, $this->user_meta );
		$this->active = array();
		$this->init_active();
	}

	protected function is_date($column) {
		if ( isset($column['cast']) ) {
			$cast = ucwords( $column['cast'] );
			if ( in_array( $cast, array( 'DATE', 'DATETIME') ) ) {
				return true;
			}
		}
		elseif ( isset( $column['type']) && 'DATE' === ucwords($column['type']) ) {
			return true;
		}
		return false;
	}

	protected function is_our_post_type() {
		$screen = get_current_screen();
		if ( empty($screen) ) {
			global $typenow;
			$post_type = empty($typenow) ? 'post' : $typenow;
		}
		else {
			$post_type = $screen->post_type;
		}
		if ( $post_type === $this->post_type )
			return true;
		else
			return false;
	}
}

} // end if class_exists()