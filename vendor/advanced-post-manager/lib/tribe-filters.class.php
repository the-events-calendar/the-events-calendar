<?php

/**
 * A class for providing WordPress filters in a manage "posts" view
 *
 **/

if ( ! class_exists( 'Tribe_Filters' ) ) {

class Tribe_Filters {

	const FILTER_POST_TYPE = 'tribe_filters'; // for storing filtersets.
	const FILTER_META = '_post_type';

	private $filtered_post_type;

	private $filters = array();
	private $active = array();
	private $orderby_cast;
	private $sortby; // takes an array

	private $url;
	private $nonce = '_tribe_filters';
	private $prefix = 'tribe_filters_';
	private $is_pre;
	private $val_pre;

	private $query_options;

	private $query_options_map;

	private $query_search_options;

	private $filters_example;

	private $active_example;

	/**
	 * Constructor function is critical.
	 * @param string $post_type The post type to be filtered.
	 * @param array $filters A multidimensional array of available filters with named keys and options for how to query them.
	 *
	 */

	private $saved_active = false;

	public function __construct( $post_type, $filters = array() ) {

		$this->query_options = array(
			'is' => __('Is','tribe-apm'),
			'not' => __('Is Not','tribe-apm'),
			'gt' => '>',
			'lt' => '<',
			'gte' => '>=',
			'lte' => '<='
		);

		$this->query_options_map = array( // turn into SQL comparison operators
			'is' => '=',
			'not' => '!=',
			'gt' => '>',
			'lt' => '<',
			'gte' => '>=',
			'lte' => '<=',
			'like' => __('LIKE','tribe-apm')
		);

		$this->query_search_options = array(
			'like' => __('Search','tribe-apm'),
			'is' => __('Is','tribe-apm'),
			'not' => __('Is Not','tribe-apm'),
			'gt' => '>',
			'lt' => '<',
			'gte' => '>=',
			'lte' => '<='
		);

		$this->filters_example = array(
			'filter_key' => array(
				'name' => __('Member Type','tribe-apm'), // text label
				'meta' => '_type', // the meta key to query
				'taxonomy' => 'some_taxonomy',// the taxonomy to query. Would never be set alongside meta above
				'options' => array( // options for a meta query. Restricts them.
					'cafe' => __('Cafe','tribe-apm'),
					'desk' => __('Private Desk','tribe-apm'),
					'office' => __('Office','tribe-apm')
				)
			)
		);

		$this->active_example = array(
			'filter_key' => array( // array key corresponds to key in $filters
				'value' => __('what i’m querying. probably a key in the options array in $filters','tribe-apm'),
				'query_option' => 'is/is not,etc.'
			)
		);

		$this->filtered_post_type = $post_type;
		$this->set_filters( $filters );

		$this->url = trailingslashit( plugins_url( '', __FILE__ ) );
		$this->add_actions_and_filters();

		$this->is_pre = $this->prefix . 'is_';
		$this->val_pre = $this->prefix . 'val_';
	}

	// PUBLIC API METHODS

	/**
	 * Set Filters with an array of filter arrays
	 *
	 * See documentation for the paramaters of a filter array
	 *
	 * @param $filters array
	 */
	public function set_filters( $filters = array() ) {
		if ( ! empty($filters) )
			$this->filters = $filters;
		$this->alphabetize_filters();
	}


	/**
	 * Get array of currently set filters
	 *
	 * @return array filters
	 */
	public function get_filters() {
		return $this->filters;
	}

	/**
	 * Set active filter state.
	 *
	 * Only use this to specifically set a particular set of filters that shouldn't be changed, as this will override filters set by the UI
	 *
	 * @param $active array multideminsional array. @see $active_example
	 */
	public function set_active( $active = null ) {
		if ( ! empty($active) ) {
			$this->active = $active;
			$this->cache_last_query($active);
		}
	}

	/**
	 * Merges a new active array into current active array
	 *
	 * @param $new_active array multideminsional array. @see $active_example
	 */
	public function add_active( $new_active = array() ) {
		$new_active = (array) $new_active;
		$this->active = array_merge( $this->active, $new_active );
	}

	/**
	 * Get currently active filters
	 *
	 * @return array current active filters array
	 */
	public function get_active() {
		return $this->active;
	}

	/**
	 * Outputs the drag & drop columns view.
	 * Expects to be inside a form
	 */
	public function output_form() {
		wp_nonce_field($this->nonce, $this->nonce, false);

		$this->saved_filters_dropdown();

		$this->inactive = array_diff_key($this->filters, $this->active);
		$this->inactive_dropdown();

		echo '<table id="tribe-filters-active" class="table-form">';
		foreach ( $this->active as $k => $v ) {
			echo $this->table_row($k, $v);
		}
		echo '</table>';
		$this->form_js();
	}


	// CALLBACKS

	protected function add_actions_and_filters() {
		add_action( 'admin_init', array($this, 'init_active'), 10 );
		add_action( 'admin_init', array($this, 'save_active'), 20 );
		add_action( 'admin_init', array($this, 'update_or_delete_saved_filters'), 21 );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue') );
		add_action( 'load-edit.php', array($this, 'add_query_filters'), 30 );
		add_action( 'init', array($this, 'register_post_type') );
		add_filter( 'admin_body_class', array($this, 'add_body_class') );
		add_action( 'tribe_after_parse_query', array($this, 'maybe_cast_for_ordering'), 10, 2 );
		add_action( 'tribe_after_parse_query', array($this, 'add_cast_helpers') );
		add_filter( 'tribe_filter_input_class', array($this, 'input_date_class'), 10, 2 );
		add_filter( 'tribe_query_options', array($this, 'input_date_options'), 10, 3 );
	}

	public function input_date_options($options, $key, $filter) {
		if ( self::is_date($filter) && isset($options['like']) ) {
			unset($options['like']);
		}
		return $options;
	}

	public function input_date_class($class, $filter) {
		if ( self::is_date($filter) ) {
			return 'date tribe-datepicker';
		}
		return $class;
	}

	public function add_cast_helpers() {
		add_filter( 'posts_request', array($this, 'help_decimal_cast'), 10, 1 );
	}

	public function maybe_cast_for_ordering($wp_query, $active) {
		// Only if it's sorting on meta
		if ( 'meta_value' !== $wp_query->get('orderby') )
			return;
		$meta_key = $wp_query->get('meta_key');
		$filter = $this->get_filter_by_field('meta', $meta_key);
		// Only if it's one of our filters
		if ( ! $filter )
			return;

		$this->orderby_cast = $this->map_meta_cast($filter);
		add_filter( 'posts_orderby', array($this, 'cast_orderby'), 10, 2 );
	}

	public function help_decimal_cast($query) {
		// Run once
		remove_filter( 'posts_request', array($this, 'help_decimal_cast'), 10, 1 );
		return preg_replace('/AS DECIMAL\)/', 'AS DECIMAL(6,2))', $query);
	}

	public function cast_orderby($orderby, $wp_query) {
		// Run once
		remove_filter( 'posts_orderby', array($this, 'cast_orderby'), 10, 2 );
		list( $by, $dir ) = explode( ' ', trim($orderby) );
		if ( ! empty($this->orderby_cast) && 'CAST' !== $this->orderby_cast ) {
			$by = sprintf('CAST(%s AS %s)', $by, $this->orderby_cast );
			return $by . ' ' . $dir;
		}
		return $orderby;
	}

	public function add_query_filters() {
		$screen = get_current_screen();

		// only filter our post type
		if ( $screen->post_type !== $this->filtered_post_type )
			return;

		add_action( 'parse_query', array($this, 'parse_query') );
	}

	public function parse_query($wp_query) {
		// Run once
		// If we just remove it though, without leaving something in its place
		// the next action that's supposed to run on parse query might be skipped.
		add_action('parse_query', '__return_true');
		remove_action('parse_query', array($this, 'parse_query') );

		do_action_ref_array('tribe_before_parse_query', array($wp_query, $this->active) );


		$tax_query = array();
		$meta_query = array();

		foreach ($this->active as $k => $v ) {
			$filter = $this->filters[$k];
			if ( isset($filter['taxonomy']) ) {
				$tax_query[] = $this->tax_query($k, $v);
			}
			else if ( isset($filter['meta'] ) ) {
				$meta_query[] = $this->meta_query($k, $v);
			}
		}
		$old_tax_query = $wp_query->get('tax_query');
		$old_tax_query =  ( empty($old_tax_query) ) ? array() : $old_tax_query;
		$tax_query = array_merge( $old_tax_query, $tax_query );
		$wp_query->set('tax_query', $tax_query);

		$old_meta_query = $wp_query->get('meta_query');
		$old_meta_query =  ( empty($old_meta_query) ) ? array() : $old_meta_query;
		$meta_query = array_merge( $old_meta_query, $meta_query );
		$wp_query->set('meta_query', $meta_query);

		$this->maybe_set_ordering($wp_query);

		do_action_ref_array('tribe_after_parse_query', array($wp_query, $this->active) );
	}

	public function debug() {
		$this->log($GLOBALS['wp_query']);
	}

	public function add_body_class($classes) {
		global $wp_query;
		// takes a string
		$ours = 'tribe-filters-active';

		// empty results?
		if ( 0 == $wp_query->found_posts ) {
			$ours .= ' empty-result-set';
		}

		$classes = $ours . ' ' . trim($classes);
		return trim($classes) . ' ';
	}

	// inits a saved filter set if one submitted
	public function init_active() {
		// saved filter active?
		if ( isset($_GET['saved_filter']) && $_GET['saved_filter'] > 0 ) {

			$filterset = get_post($_GET['saved_filter']);
			$active = unserialize($filterset->post_content);
			if ( $active ) {
				$this->set_active($active);
				$this->saved_active = $filterset;
			}
		} elseif ( !$_POST && $last_query = $this->last_query() ) {
			$this->set_active($last_query);
		}
	}

	/* Active Items set via a POST form.
	 *
	 * Or, active items can be saved for later retrieval and application.
	 */
	public function save_active() {
		if ( ! ( isset($_POST[$this->nonce]) && wp_verify_nonce( $_POST[$this->nonce], $this->nonce ) ) )
			return;

		// Clear button on frontend
		if ( isset($_POST['tribe-clear']) ) {
			$this->reset_active();
			return;
		}
		$active = array();

		foreach ( $this->filters as $key => $filter ) {
			$maybe_active = false;

			// meta fields
			if ( isset($filter['meta']) ) {
				$maybe_active = $this->maybe_active_meta($key, $filter);
			}
			// taxonomies
			else if ( isset($filter['taxonomy']) ) {
				$maybe_active = $this->maybe_active_taxonomy($key, $filter);
			}
			// Custom types
			else if ( isset($filter['custom_type']) ) {
				$maybe_active = apply_filters( 'tribe_maybe_active' . $filter['custom_type'], false, $key, $filter );
			}
			// Add em if ya got em
			if ( $maybe_active ) {
				$active[$key] = $maybe_active;
			}

		} // foreach

		if ( ! empty($active) ) {
			$this->set_active( $active );
		}

		if ( isset($_POST['tribe-save']) ) {
			$this->save_filter();
		}

	}

	public function update_or_delete_saved_filters() {
		if ( ! ( isset($_POST[$this->nonce]) && wp_verify_nonce( $_POST[$this->nonce], $this->nonce ) ) )
			return;

		// if there wasn't a saved filter ID, no point
		if ( ! isset( $_POST['tribe-saved-filter-active'] ) || empty( $_POST['tribe-saved-filter-active'] ) )
			return;

		// update the filter with currently active stuff
		if ( isset($_POST['tribe-update-saved-filter']) ) {
			$filter = get_post($_POST['tribe-saved-filter-active']);
			$filter->post_content = serialize($this->active);
			wp_update_post($filter);
		}

		// delete the saved filter
		if ( isset($_POST['tribe-delete-saved-filter']) ) {
			wp_delete_post($_POST['tribe-saved-filter-active'], true);
			// clear all filters while we're at it.
			$this->reset_active();
		}
	}

	public function register_post_type() {
		register_post_type( self::FILTER_POST_TYPE, array(
			'show_ui' => false,
			'rewrite' => false,
			'show_in_nav_menus' => false
		));
	}

	public function enqueue() {
		global $current_screen;
		$resources_url = apply_filters( 'tribe_apm_resources_url', $this->url . 'resources' );
		$resources_url = trailingslashit($resources_url);
		if ( $current_screen->id == 'edit-' . $this->filtered_post_type ) {
			wp_enqueue_style('tribe-jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/base/jquery-ui.css', array(), '1.8.10' );
			wp_enqueue_script('tribe-jquery-ui-datepicker', $resources_url . 'jquery-ui-datepicker.js', array('jquery-ui-core'), null, true );
			wp_enqueue_script('tribe-filters', $resources_url . 'tribe-filters.js', array('jquery-ui-sortable', 'tribe-jquery-ui-datepicker'), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ), true );
		}
	}


	// UTLITIES AND INTERNAL METHODS

	protected function last_query() {
		return get_user_meta(get_current_user_id(), 'last_used_filters_'.$this->filtered_post_type, TRUE);
	}

	protected function cache_last_query($query) {
		return update_user_meta(get_current_user_id(), 'last_used_filters_'.$this->filtered_post_type, $query);
	}

	protected function clear_last_query() {
		return delete_user_meta(get_current_user_id(), 'last_used_filters_'.$this->filtered_post_type);
	}

	protected function reset_active() {
		$this->active = array();
		$this->clear_last_query();
	}

	protected function alphabetize_filters() {
		$filters = (array) $this->filters;
		if ( empty($filters) )
			return;

		foreach ( $filters as $k => $v ) {
			if ( ! empty( $v['name']) )
				$temp[$k] = $v['name'];
		}
		asort($temp);

		foreach ( $temp as $k => $v ) {
			$alpha_filters[$k] = $filters[$k];
		}

		$this->filters = $alpha_filters;
		unset($alpha_filters, $temp);
	}

	protected function maybe_active_taxonomy($key, $filter) {
		$val = $this->prefix . $key;
		if ( isset($_POST[$val]) ) {
			return array('value' => $_POST[$val]);
		}
		return false;
	}

	protected function maybe_active_meta($key, $filter) {
		$val = $this->val_pre . $key;
		$is =  $this->is_pre . $key;
		if ( isset( $_POST[$val] ) && isset( $_POST[$is] ) && ( $_POST[$val] !== '' ) ) {
			return array('value' => $_POST[$val], 'query_option' => $_POST[$is] );
		}
		return false;
	}

	protected function save_filter() {
		if ( ! isset($_POST['filter_name']) || empty($this->active) )
			return;

		$filter = array(
			'post_content' => serialize($this->active),
			'post_title' => $_POST['filter_name'],
			'post_type' => self::FILTER_POST_TYPE,
			'post_status' => 'publish'
		);

		$post_id = wp_insert_post($filter);
		update_post_meta( $post_id, self::FILTER_META, $this->filtered_post_type );
	}

	public function log($data = array() ) {
		error_log(print_r($data,1));
	}

	protected function saved_filters_dropdown() {
		$filters = get_posts(array(
			'numberposts' => -1,
			'post_type' => self::FILTER_POST_TYPE,
			'meta_key' => self::FILTER_META,
			'meta_value' => $this->filtered_post_type
		));
		if (empty($filters) )
			return;

		$url = add_query_arg( array('post_type' => $this->filtered_post_type, 'saved_filter' => 'balderdash' ), admin_url('edit.php') );
		$url = str_replace('balderdash', '', $url);

		$sel = '<select id="tribe-saved-filters" name="tribe-saved-filters" data:submit_url="' . $url . '">';
		$sel .= '<option value="0">'. __('Choose a Saved Filter', 'tribe-apm') .'</option>';
		foreach ( $filters as $filter ) {
			$selected = ( $this->saved_active && $this->saved_active->ID == $filter->ID ) ? ' selected="selected" ' : '';
			$sel .= "<option value='{$filter->ID}' {$selected}>{$filter->post_title}</option>";
		}

		$sel .= '</select>';

		// Delete/Update Saved Query if one is active
		if ( ! empty( $this->saved_active ) && isset( $this->saved_active) ) {
			$sel .= '<span class="hide-if-no-js saved-filter-actions">';
			$sel .= '<input type="hidden" name="tribe-saved-filter-active" value="'. $this->saved_active->ID .'" />';
			$sel .= '<input type="submit" name="tribe-update-saved-filter" value="Update Filter" class="button-secondary button-small" />';
			$sel .= '<input type="submit" name="tribe-delete-saved-filter" value="Delete Filter" class="button-secondary button-small" />';
			$sel .= '</span>';
		}

		$sel .= '<br />';

		echo $sel;
	}

	protected function get_filter_by_field($field, $value) {
		foreach ( $this->filters as $k => $v ) {
			if ( isset($v[$field]) && $value === $v[$field] ) {
				return $this->filters[$k];
			}
		}
		return false;
	}

	// accepts a $this->active $key => value pair.
	protected function table_row($key, $value) {
		$filter = $this->filters[$key];
		$before = '<tr><th scope="row"><b class="close">×</b>'.$filter['name'].'</th><td>';
		$after = '</td></tr>';
		if ( isset( $filter['taxonomy'] ) ) {
			$ret = $this->taxonomy_row($key, $value, $filter);
		}
		else if ( isset( $filter['meta']) ) {
			$ret = $this->meta_row($key, $value, $filter);
		}
		else if ( isset( $filter['custom_type']) ) {
			$ret = apply_filters( 'tribe_custom_row' . $filter['custom_type'], '', $key, $value, $filter );
		}

		return $before . $ret . $after;
	}

	protected function taxonomy_row($key, $value, $filter) {
		$terms = get_terms($filter['taxonomy'], array('hide_empty' => 0) );
		$value = array_merge( array('value' => 0), (array) $value );
		$opts = array();
		foreach ( $terms as $term ) {
			$opts[$term->term_id] = $term->name;
		}
		return self::select_field($this->prefix . $key, $opts, $value['value'], true);
	}

	protected function meta_row($key, $value, $filter) {
		$ret = '';
		$is_key = $this->is_pre . $key;
		$val_key = $this->val_pre . $key;
		$value = array_merge( array('value' => 0, 'query_option' => 0 ), (array) $value );

		// We have explicit dropdown options.
		if ( isset($filter['options']) && ! empty($filter['options']) ) {
			$query_options = apply_filters( 'tribe_query_options', $this->query_options, $key, $filter );
			$ret .= self::select_field( $is_key, $query_options, $value['query_option'] );
			$ret .= self::select_field( $val_key, $filter['options'], $value['value'], true);
		}
		// No explicit options. We're showing a search field
		else {
			$query_options = apply_filters( 'tribe_query_options', $this->query_search_options, $key, $filter );
			$input_class = apply_filters( 'tribe_filter_input_class', 'text', $filter, $key, $value );
			$ret .= self::select_field( $is_key, $query_options, $value['query_option'] );
			$ret .= "<input type='text' name='{$val_key}' value='{$value['value']}' class='{$input_class}' >";
		}
		return $ret;
	}

	public static function select_field($name, $options = null, $active = '', $allow_multi = false ) {

		$is_multi = ( is_array($active) && count($active) > 1 ) ? true : false;
		if ( ! $allow_multi ) {
			$class = 'no-multi';
			$toggle = '';
		}
		else {
			$class = ( $is_multi ) ? 'multi-active' : false;
			$toggle = ( ! $class )
				? '<b class="multi-toggle on">+</b>'
				: '<b class="multi-toggle">-</b>';
		}
		$multi = ( $is_multi ) ? ' multiple="multiple"' : '';
		$name = ( $is_multi ) ? $name.'[]' : $name;

		// in case we only had a single value passed, we'll typecast to array to keep it DRY
		$active = (array) $active;
		$sel = '';
		if ( is_array($options) ) {
			$sel .= '<select id="'. $name .'" name="'. $name .'" class="' . $class . '"' . $multi . '>';

			foreach ( $options as $k => $v ) {
				$selected = selected(in_array( $k, $active ), true, false);
				$sel.= '<option value="'. $k .'"'. $selected .'>'. $v .'</option>';
			}
			$sel .= '</select>';
		}
		$sel .= $toggle;
		return $sel;
	}

	protected function inactive_dropdown() {
		$inactive = $this->inactive;
		echo '<select name="tribe-filters-inactive" id="tribe-filters-inactive">';
		echo '<option value="0">'.__('Add a Filter', 'tribe-apm').'</option>';
		foreach ( $inactive as $k => $v ) {
			echo $this->dropdown_row($k,$v);
		}
		echo '</select>';
	}

	protected function dropdown_row($k, $v) {
		return '<option value="'.$k.'">'.$v['name'].'</option>';
	}

	protected function form_js() {
		global $wp_query;

		$templates = array();
		$option_rows = array();

		foreach ( $this->filters as $k => $v ) {
			$templates[$k] = $this->table_row($k, '');
			$option_rows[$k] = $this->dropdown_row($k,$v);
		}

		$js = array(
			'filters' => $this->filters,
			'template' => $templates,
			'option' => $option_rows,
			'valPrefix' => $this->val_pre,
			'prefix' => $this->prefix,
			'displaying' => $wp_query->found_posts . ' found'
		);

		echo "\n<script type='text/javascript'>";
		echo "\n\tvar Tribe_Filters = " . json_encode($js);
		echo "\n</script>";
	}

	protected function maybe_set_ordering($wp_query) {
		$sort_prefix = apply_filters( 'tribe_sort_prefix', 'tribe_sort_' );
		$orderby = $wp_query->get('orderby');
		if ( empty($orderby) && isset($_POST['orderby']) ) {
			$orderby = $_POST['orderby'];
		}
		if ( ! empty($orderby) && 0 === strpos($orderby, $sort_prefix) ) {
			$orderby = preg_replace('/^'.$sort_prefix.'/', '', $orderby);
			// If it's a meta field, easy enough
			$meta_field = $this->get_filter_by_field('meta', $orderby);
			if ( $meta_field ) {
				$wp_query->set('orderby', 'meta_value');
				$wp_query->set('meta_key', $orderby);
				return;
			}
			// Custom Field?
			$custom_field = $this->get_filter_by_field('custom_type', $orderby);
			if ( $custom_field ) {
				do_action_ref_array( 'tribe_orderby_custom'.$orderby, array($wp_query, $custom_field) );
			}
		}
	}

	protected function tax_query($key, $val) {
		$filter = $this->filters[$key];
		$tax_query = array(
			'taxonomy' => $filter['taxonomy'],
			'field' => 'id',
			'terms' => $val['value'],
			'operator' => 'IN'
		);
		return apply_filters( 'tribe_filters_tax_query', $tax_query, $key, $val, $filter );
	}

	protected function meta_query($key, $val) {
		$filter = $this->filters[$key];
		$meta_query = array(
			'key' => $filter['meta'],
			'value' => $val['value'],
			'compare' => $this->map_meta_compare($val),
			'type' => $this->map_meta_cast($filter)
		);
		return apply_filters( 'tribe_filters_meta_query', $meta_query, $key, $val, $filter );
	}

	protected function map_meta_cast($filter) {
		$cast = ( isset($filter['cast']) ) ? strtoupper($filter['cast']) : 'CHAR';
		$cast = ( 'NUMERIC' === $cast ) ? 'SIGNED' : $cast;
		$allowed = array( 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' );
		return ( in_array($cast, $allowed) ) ? $cast : 'CHAR';
	}

	protected function map_meta_compare($val) {
		$compare = ( isset($val['query_option']) ) ? $val['query_option'] : 'is';
		if ( is_array($val['value']) ) {
			return ( 'not' === $compare ) ? 'NOT IN' : 'IN';
		}
		return $this->query_options_map[$compare];
	}

	public function map_query_option($option) {
		return $this->query_options_map[$option];
	}

	protected function is_date($filter) {
		if ( isset($filter['cast']) ) {
			$cast = ucwords( $filter['cast'] );
			if ( in_array( $cast, array( 'DATE', 'DATETIME') ) ) {
				return true;
			}
		}
		elseif ( isset( $filter['type']) && 'DATE' === ucwords($filter['type']) ) {
			return true;
		}
		return false;
	}
}

} // end if class_exists()