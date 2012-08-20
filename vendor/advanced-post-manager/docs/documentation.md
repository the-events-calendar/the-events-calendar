Title: Documentation for Advanced Post Manager
Author: Matt Wiebe
Date: Aug 16, 2011
CSS: docs.css


# Advanced Post Manager #

## About ##

This is a tool for developers who want to turbo-charge their custom post type listings with metadata, taxonomies, and more. An intuitive interface for adding (and saving) complex filtersets is provided, along with a drag-and-drop interface for choosing and ordering columns to be displayed. Metaboxes are also automatically generated for all your metadata-entry needs.

## Installation ##

Put the plugin in your plugins directory in the normal manner. Activate it. Nothing will immediately happen, because this is a tool for developers, and you're going to have to write some code. But not much.

## How to Use ##

You're going to want to write your own plugin, or possibly put code in your theme's functions.php file, whichever makes sense for your needs. What you won't want to do is modify the core Advanced Post Manager files in any way.

## Filter Terminology ##

You might get confused about the term "filters" in this document. I talk about two different types:

1. The WordPress's plugin system type that get used via `add_filter()` and `apply_filters()`. APM uses these filters extensively to do its work.
2. The APM filter type, which says "filter this list of posts according to a query."

This document mostly talks about the 2nd type, but discusses the first type as well. The context should hopefully make it clear.

### Initialization ###

Run your code attached to the WordPress `init` hook, like so:

	add_action('init', 'setup_cpt_filters');
	function setup_cpt_filters() {
		// globalize it so that we can call methods on the returned object
		global $my_cpt_filters;
		// We'll show you what goes in this later
		$filter_array = array();
		$my_cpt_filters = tribe_setup_apm('my_post_type', $filter_array );
	}

Now, you're asking yourself, *what's in that `$filter_array`*? I thought you'd never ask. This array is where you tell APM what you want to make available for filters. This is important. So important it gets its own heading.

### Filter Array ###

Once you've mastered everything here, `scratch.php` should serve as a handy quick reference. 

#### Meta Filters ####

Filtering on metadata can be extremely powerful, and much of the power of APM lies here. *Note that meta fields that contain multiple entries per post will behave erratically.*

	$filter_array = array(
		// The key is pretty essential. It's used in many places. Choose a unique key, preferably prefixed
		'my_meta_filter_key' => array(
			// required
			'name' => 'Name or Title for display purposes',
			// Tells us this is a metadata filter
			'meta' => 'meta_key'
		)
	);

That would be enough to add a filter for just the `meta_key` meta field. But we can do more:

	$filter_array = array(
		'my_meta_filter_key' => array(
			'name' => 'Name or Title for display purposes',
			'meta' => 'meta_key',
			// The options field restricts the filter to a specific dropdown of values to query
			'options' => array(
				'meta_value' => 'Display Title',
				'another_meta_value' => 'Another Title'
			)
		)
	);

Pretty cool. There's all kinds of metadata in the world though. Maybe my metadata is number-ish. I want my ordering done right:

	$filter_array = array(
		'my_meta_filter_key' => array(
			'name' => 'Name or Title for display purposes',
			'meta' => 'meta_key',
			'options' => array(
				'meta_value' => 'Display Title',
				'another_meta_value' => 'Another Title'
			),
			// NUMERIC is translated to SIGNED in MySQL-speak.
			'cast' => 'NUMERIC'
		)
	);

The above will ensure that 2 comes before 10 when you use ordering or using < or > filters. Allowed values include `BINARY, CHAR, DATE, DATETIME, DECIMAL, SIGNED, TIME, UNSIGNED, NUMERIC`. Consult your nearest database [manual](http://dev.mysql.com/doc/refman/5.1/en/cast-functions.html) and/or nerd for what those mean.

That takes care of meta filters, probably the most common thing you'd use this for. Let's also take a look at taxonomy filters.

#### Taxonomy Filters ####

This could be totally unnecessary, since `tribe_setup_apm` automatically adds associated taxonomies with the `show_ui` flag set to `true`. If, for some reason, you have taxonomies that you're showing the UI for but don't want a filter for, simply do the following on initialization:

	add_action('init', 'setup_cpt_filters');
	function setup_cpt_filters() {
		// globalize it so that we can call methods on the returned object
		global $my_cpt_filters;
		$my_cpt_filters = tribe_setup_apm('my_post_type', $filter_array );
		// Disable automatic taxonomy registration
		$my_cpt_filters->add_taxonomies = false;
	}

And that's all there is to it. Now, either you've disabled automatic taxonomies, or you have taxonomies without the UI showing that you want to add. Let's dive in, assuming we're using `$filter_array` as the second argument on `tribe_setup_apm`:

	$filter_array = array(
		// maybe a bunch of meta filters here
		'taxonomy_key' => array(
			// seriously, do you need that documented?
			'name' => 'Taxonomy Name',
			// The taxonomy. First arg of register_taxonomy()
			'taxonomy' => 'my_taxonomy',
		)
	);

If you're thinking *that's too easy*, you're right. But it is that easy. The UI will expose the ability to query multiple taxonomy entries at once, making the admin UI much more powerful. (The multiple taxonomies follow the OR pattern, meaning that you'll view "posts" in any of the multiple taxonomy terms selected.)

#### Custom Filters ####

You're a smart, good-looking developer. You're saying, *yes, but the filtering I want to do doesn't fit into your predefined meta and taxonomy filters.* But of course, we anticipated your needs and provided hooks and filters out the proverbial, um, something. Registration is simple:

	$filter_array = array(
		// maybe a bunch of meta and/or taxonomy filters here
		'custom_query_key' = array(
			'name' => 'My Custom Query',
			// Your custom_type
			'custom_type' => 'my_custom_type',
		)
	);

Now, this won't do much except show a label that does nothing in some places. But you're smart and good-looking, you're ready to code. Let's dive in. We're going to build a post status filter.

We're going to encapsulate our funcitonality within its own class. This makes namespacing simpler. There are other ways to do this, and if you're smart and/or good-looking enough to do this differently, use your preferred methodology and feel superior to me. We're going to piece this custom_type plugin together. Here's how we register it:

	$filter_array = array(
		'tribe_post_status' => array(
			'name' => 'Status',
			'custom_type' => 'post_status'
		)
	);
	
And here's how we start off our custom_type class:

	class Tribe_Status_Type {
		
		protected $key = 'tribe_post_status';
		protected $type = 'post_status';
		
		public function __construct() {
			// I'm aliasing this here so I have to type less for actions to follow.
			// Lazy programming = good programming. Usually.
			$type = $this->type;
		}
	}
	new Tribe_Status_Type; // no need to assign this to a variable since it has no methods involved. Just instatiating, haters.

This does nothing at all yet. Let's remedy that. Let's sort out the column display function first, as that will be easy and lure us into the idea that this is simple. Remembers, this stuff is inside the class above. We'll show the whole thing when we're done.
	
	// this belongs inside the __construct() function
	// remember that $type is aliased to $this->type which is the custom_type
	add_filter( 'tribe_custom_column'.$type, array($this, 'column_value'), 10, 3 );
	
	// every filter instance receives a blank $value.
	// $column_id aliases the filter key
	// $post_id is the "post" ID
	public function column_value($value, $column_id, $post_id) {
		// what's our post status?
		$status = get_post_status($post_id);
		// get the WP post status object. We might get a nicer label.
		$status_object = get_post_status_object($status);
		// return the nicer label, or just the raw status if it's not there
		return ( isset($status_object->label) ) ? $status_object->label : $status;
	}

Now, our columns work, huzzah! Our filter dropdown sadly consists of nothing at all though. Let's fix that.

	// inside __construct() . If you don't want all 4 variables provided, change '4' to the appropriate number.
	add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
	
	/** the callback.
	 * @var $return string is blank until we provide something. We need to return something for displaying.
	 * @var $key string The filter key. We may or may not use it.
	 * @var $value array The active array. Contains various data regarding the active state of our filter. We will provide this data later. 
	 * @var $filter array The filter array defined earlier. You might have provided additional data in the filter registration that you want now. 
	 */
	public function form_row($return, $key, $value, $filter) {
		// Getting the publically available post statuses, or stati, if you will.
		$stati = get_post_stati(array('show_in_admin_status_list'=>true), 'objects');
		$args = array();
		// Set up the $args array for our dropdown
		foreach ( $stati as $k => $object ) {
			$args[$k] = $object->label;
		}
		/**
		 * tribe_select_field() is your friend
		 *
		 * @var $key string The name attribute of the <select> dropdown. You'll be looking for this later in determining active state.
		 * @var $args array $key => $value pairs of value => display for the dropdown
		 * $var $value['value'] string|array The active value(s) corresponding to the $args keys above
		 * @var $multiselect bool We say true here because we want an optional multi-select field. Defaults to false.
		 */
		return tribe_select_field($key, $args, $value['value'], true);
	}

Whew, that was a lot of code to display a dropdown (even if most of it was helpful comments). Next, we'll need to figure out how to set the active state, or the code above will never reflect the active state.

	// inside __construct() as you'd expect by now
	add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );
	
	/**
	 * @param $return array|bool The possible return value for the active state. (bool) false by default
	 * @param $key The $key from the $filters_array.
	 * @param $filter The filter registered filter array.
	 */
	public function maybe_set_active($return, $key, $filter) {
		// Normally we'd want to check nonces, but this function has already checked them.
		if ( isset($_POST[$key]) && ! empty($_POST[$key]) ) {
			// We're returning an $active array here.
			// You might choose to return more information if it was heplful to you.
			return array('value' => $_POST[$key]);
		}
		// return the default because we're not active
		return $return;
	}

Now we're setting an active state! The state we submitted in is reflected in our display state. But it's not actually modifying our query. Let's sort that out. We can hook into the `tribe_before_parse_query` or `tribe_after_parse_query` action, whichever is most appropriate. As the names indicated, they fire before and after the default APM functionality. We're going to use `tribe_after_parse_query`, which will generally be most useful.

	// inside __construct(), in case you've slept through this whole tutorial.
	add_action( 'tribe_after_parse_query', array($this, 'parse_query'), 10, 2 );
	
	/**
	 * Let's determine whether to add some WP_Query vars or not.
	 *
	 * If you're really hardcore, you'll know how to optionally register some query filters like 'posts_where' and 'posts_join' at this point.
	 *
	 * @var $wp_query object The currently active $WP_Query object. It has methods you might use.
	 * @var $active array The array of active filters. In our case, we've decide what information might reside in our keyed $active state.
	 */
	public function parse_query($wp_query, $active) {
		// Is our custom type active? If not, let's get out of here.
		if ( ! isset($active[$this->key]) ) {
			return;
		}
		// Oh, we're active. We use the 'value' key in our active state.
		$status = $active[$this->key]['value'];
		// post_status is a native query, so we cheated a bit.
		// WP_Query accepts a string or array for multiple statuses
		$wp_query->set('post_status', $status);
	}

Finally, let's also make this sortable. Just add 'sortable' => true to your $column_array:

	$filter_array = array(
		'tribe_post_status' => array(
			'name' => 'Status',
			'custom_type' => 'post_status',
			'sortable' => true
		)
	);

Well, it'll take a bit more than that. But you can click on it and do absolutely nothing! Let's change that.

	// Inside __construct()
	add_action( 'tribe_orderby_custom'.$type, array($this, 'orderby'), 10, 2 );
	
	/**
	 * Called when our custom type is being sorted on.
	 *
	 * @param $wp_query object The currently active $wp_query object
	 * @param $filter array Our custom_type array
	 */
	public function orderby($wp_query, $filter) {
		// Do something useful for ordering
	}

Unfortunately, WP does not accept `$wp_query->set('orderby', 'post_status')`. We're going to have to hook into WP's `posts_orderby` filter and be a little more tricky:

	public function orderby($wp_query, $filter) {
		// register our filter
		add_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
	}
	
	/**
	 * Our orderby filter handler
	 * 
	 * @param $orderby string The orderby MySQL string
	 * @param $wp_query object The currently active $wp_query object
	 * @return string New orderby MySQL string
	 */
	public function set_orderby($orderby, $wp_query) {
		// Be nice, clean up after yourself. Run once only.
		remove_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
		// We need this for the posts table name
		global $wpdb;
		// $orderby has ASC or DESC appended by this point. Save $order for later
		list($by, $order) = explode(' ', trim($orderby) );
		// post_status is a column on the posts table
		$by = "{$wpdb->posts}.post_status";
		// put it back together again
		return $by . ' ' . $order;
	}

That wasn't so bad was it? That's it for our out post_status functionality. The verbosity largely derived from the inline comments. Otherwise, the whole thing looks like:

	class Tribe_Status_Type {

		protected $key = 'tribe_post_status';
		protected $type = 'post_status';

		public function __construct() {
			$type = $this->type;

			add_filter( 'tribe_custom_column'.$type, array($this, 'column_value'), 10, 3 );
			add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
			add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );
			add_action( 'tribe_after_parse_query', array($this, 'parse_query'), 10, 2 );
			add_action( 'tribe_orderby_custom'.$type, array($this, 'orderby'), 10, 2 );
		}

		public function orderby($wp_query, $filter) {
			add_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
		}

		public function set_orderby($orderby, $wp_query) {
			// run once
			remove_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
			global $wpdb;
			list($by, $order) = explode(' ', trim($orderby) );
			$by = "{$wpdb->posts}.post_status";
			return $by . ' ' . $order;
		}

		public function parse_query($wp_query, $active) {
			if ( ! isset($active[$this->key]) ) {
				return;
			}
			$status = $active[$this->key]['value'];
			$wp_query->set('post_status', $status);
		}
		
		public function maybe_set_active($return, $key, $filter) {
			if ( isset($_POST[$key]) && ! empty($_POST[$key]) ) {
				return array('value' => $_POST[$key]);
			}
			return $return;
		}
		
		public function form_row($return, $key, $value, $filter) {
			$stati = get_post_stati(array('show_in_admin_status_list'=>true), 'objects');
			$args = array();
			foreach ( $stati as $k => $object ) {
				$args[$k] = $object->label;
			}
			return tribe_select_field($key, $args, $value['value'], true);
		}
		
		public function column_value($value, $column_id, $post_id) {
			$status = get_post_status($post_id);
			$status_object = get_post_status_object($status);
			return ( isset($status_object->label) ) ? $status_object->label : $status;
		}
		
	}
	new Tribe_Status_Type;

And that's it for creating a Post Status filter. It might look like a lot of code, but it's *a whole lot less* than it would be without this plugin. You probably wouldn't even try.

There are loads of other hooks and filters that haven't been documented here. Read the source. You'll see the post status filter integrated in the included demo plugin inside the `demo/` directory.

### Metabox Magic ###

You have all these fields to order on. You don't want to use Wordpress' custom fields for data entry, of course. But, making your own metabox using `add_meta_box` is tedious. Good news is, we've already made a metabox for you that includes every `meta` filter created. If you already have your own metabox code, and would like to opt out of metabox generation, that's simple:

	// Make sure you assign the returned object to a variable
	$my_filters = tribe_setup_apm( 'my_post_type', $filter_array );
	// Turn off metabox generation
	$my_filters->do_metaboxes = false;

On with the show. By default, the generated metabox has the boring title of **Extended Information**. Changing that is easy, via the 3rd argument to tribe_setup_apm:

	tribe_setup_apm('my_post_type', $filter_array, 'My Snappy Data' );
	
Now your single metabox has your snappy title. But what if your data is better suited to be split into more than one metabox? Never fear!

	$filter_array = array(
		'my_meta_filter_key' => array(
			'name' => 'Just a default kinda field',
			'meta' => 'meta_key'
		),
		'another_key' => array(
			'name' => 'Standoffish',
			'meta' => '_allbymyself',
			// Explicitly associate with another metabox
			'metabox' => 'unique_box'
		)
	);
	
	$metaboxes = array( 'unique_box' => 'A Unique Box' );
	
	tribe_setup_apm('my_post_type', $filter_array, $metaboxes );
	
Now the second filter will be placed in its own metabox, with every non-explicitly defined key being placed in the default metabox. The key thing is to define the metabox title within the `$metaboxes` array, otherwise we won't know where to put it and it'll just get put in the default metabox.

If, for some reason, the order you declare your filters in is *not* the order they should appear in the metabox in, use the extra `metabox_order` field to provide ordering.

#### Metabox Data Types ####

The default field types will work in most cases. A text field for standard metadata, or a dropdown field if you provided any `options`. There's a lot more types under the hood:

	$filter_array = array(
		'normal_field' => array(
			'name' => 'Normal Text',
			'meta' => '_text',
			'type' => 'text', // this would be text by default without declaring it
			'desc' => 'Optional supporting text to help user enter data.'
		),
		'another_key' => array(
			'name' => 'Standoffish',
			'meta' => '_allbymyself',
			'options' => array(
				'yes' => 'Yes',
				'very' => 'Very',
				'omg' => 'Crazy Lots'
			)
			'type' => 'select' // produces an HTML <select> dropdown. Default for anything with options already provided
		),
		'date_field' => array(
			'name' => 'Birthday',
			'meta' => '_birthday',
			'type' => 'date' // Gives a datepicker. Also automatically sets 'cast' to 'DATE'	
		)
	);

You get the idea. The available types are:

* `text` Just a text field. Default.
* `textarea` A larger text field
* `wysiwyg` A visual editor field
* `checkbox` A checkbox. Ensure that the `desc` field is used so that there's a label to click on.
* `select` A dropdown. Requires an `options` array to populate. Provide `multiple` => true to create a multi-select field.
* `checkbox_list` Multiple checkboxes. Requires an `options` array to populate. A good alternative to a multi-select.
* `radio` Radio buttons. Requires an `options` array to populate.
* `file` Upload a file(s). Saves a reference to the attachment ID.
* `image` Upload an image(s). Saves a reference to the attachment ID.
* `color` A HEX colorpicker.
* `html` Lets you just display some HTML. Useful for when you need to add directions, etc.
* `post2post` Produces a UI for associating with another WP post. Requires a `post_type` argument for what should appear in the dropdown.
* `time` Provides a timepicker. Timepicker UIs all suck in different ways. You probably shouldn't use this.


These types will likely be extensible at some future point. A text field with a good `desc` goes a long way.