<?php
/**
 * User: jbrinley
 * Date: 5/18/11
 * Time: 12:34 PM
 */
 
class WP_Route extends WP_Router_Utility {
	protected $id = '';
	protected $path = '';
	protected $query_vars = array();
	protected $wp_rewrite = '';
	protected $title = '';
	protected $title_callback = '__';
	protected $title_arguments = array();
	protected $page_callback = '';
	protected $page_arguments = array();
	protected $access_callback = TRUE;
	protected $access_arguments = array();
	protected $template = array();
	protected $properties = array();

	/**
	 * @throws Exception
	 * @param string $id A unique string used to refer to this route
	 * @param array $properties An array of key/value pairs used to set
	 * the properties of the route. At a minimum, must include:
	 *  - path
	 *  - page_callback
	 */
	public function __construct( $id, array $properties ) {
		$this->set('id', $id);

		foreach ( array('path', 'page_callback') as $property ) {
			if ( !isset($properties[$property]) || !$properties[$property] ) {
				throw new Exception(sprintf(__("Missing %s", 'wp-router'), $property));
			}
		}
		
		foreach ( $properties as $property => $value ) {
			$this->set($property, $value);
		}
		
		if ( $this->access_arguments && !$properties['access_callback'] ) {
			$this->set('access_callback', 'current_user_can');
		}

	}

	/**
	 * Get the value of the the given property
	 *
	 * @throws Exception
	 * @param string $property
	 * @return mixed
	 */
	public function get( $property ) {
		if ( isset($this->$property) ) {
			return $this->$property;
		} elseif ( isset($this->properties[$property]) ) {
			return $this->properties[$property];
		} else {
			throw new Exception(sprintf(__("Property not found: %s.", 'wp-router'), $property));
		}
	}

	/**
	 * Set the value of the given property to $value
	 *
	 * @throws Exception
	 * @param string $property
	 * @param mixed $value
	 * @return void
	 */
	public function set( $property, $value ) {
		if ( in_array($property, array('id', 'path', 'page_callback')) && !$value ) {
			throw new Exception(sprintf(__("Invalid value for %s. Value may not be empty.", 'wp-router'), $property));
		}
		if ( in_array($property, array('query_vars', 'title_arguments', 'page_arguments', 'access_arguments')) && !is_array($value) ) {
			throw new Exception(sprintf(__('Invalid value for %1$s: %2$s. Value must be an array.'), $property, $value));
		}
		if ( isset($this->$property) ) {
			$this->$property = $value;
		} else {
			$this->properties[$property] = $value;
		}
	}

	/**
	 * Execute the callback function for this route.
	 *
	 * @param WP $query_vars
	 * @return void
	 */
	public function execute( WP $query ) {
		// check access
		if ( !$this->check_access($query) ) {
			$this->access_denied();
			return; // can't get in
		}

		// do the callback
		$page_contents = $this->get_page($query);
		
		// if we have content, set up the page
		if ( $page_contents === FALSE ) {
			return; // callback explicitly told us not to do anything with output
		}

		$template = $this->choose_template();

		if ( $template === FALSE ) {
			print $page_contents;
			exit();
		}

		$title = $this->get_title($query);

		$page = new WP_Router_Page($page_contents, $title, $template);
	}

	/**
	 * Return the URL for this route, with the given arguments
	 *
	 * @todo This currently only returns the non-pretty URL. If
	 *       using permalinks, it should be a pretty URL based on
	 *       $this->path
	 * @param array $args
	 * @return string
	 */
	public function url( $args = array() ) {
		$args[self::QUERY_VAR] = $this->id;
		return add_query_arg($args, trailingslashit(home_url()));
	}

	/**
	 * @return array WordPress rewrite rules that should point to this instance's callback
	 */
	public function rewrite_rules() {
		$this->generate_rewrite();
		return array(
			$this->path => $this->wp_rewrite,
		);
	}

	/**
	 * @return array All query vars used by this route
	 */
	public function get_query_vars() {
		return array_keys($this->query_vars);
	}

	/**
	 * Get the appropriate callback function for the route, taking the HTTP method into account
	 *
	 * @return bool|string
	 */
	protected function get_callback( $possibilities ) {
		if ( is_callable($possibilities) ) {
			return $possibilities;
		}
		if ( is_array($possibilities) ) {
			$method = $_SERVER['REQUEST_METHOD'];
			if ( $method && isset($possibilities[$method]) && is_callable($possibilities[$method]) ) {
				return $possibilities[$method];
			}
			if ( isset($possibilities['default']) && is_callable($possibilities['default']) ) {
				return $possibilities['default'];
			}
		}
		return FALSE;
	}

	/**
	 * Get the contents of the page
	 *
	 * @param WP $query
	 * @return bool|string
	 */
	protected function get_page( WP $query ) {
		$callback = $this->get_callback($this->page_callback);
		if ( !$callback ) {
			return FALSE;
		}
		$args = $this->get_query_args($query, 'page');
		ob_start();
		$returned = call_user_func_array($callback, $args);
		$echoed = ob_get_clean();

		if ( $returned === FALSE ) {
			return FALSE;
		}

		return $echoed.$returned;
	}

	protected function get_title( WP $query ) {
		$callback = $this->get_callback($this->title_callback);
		if ( !$callback ) {
			return $this->title; // can't call it
		}
		$args = $this->get_query_args($query, 'title');
		if ( !$args ) {
			$args = array($this->title);
		}
		$title = call_user_func_array($this->title_callback, $args);

		if ( $title === FALSE ) {
			return $this->title;
		}

		return $title;
	}

	protected function check_access( WP $query ) {
		if ( $this->access_callback === TRUE ) {
			return TRUE;
		}
		$callback = $this->get_callback($this->access_callback);
		if ( !$callback ) {
			return FALSE; // nobody gets in
		}
		if ( is_callable($callback) ) {
			$args = $this->get_query_args($query, 'access');
			return (bool)call_user_func_array($callback, $args);
		}
		return (bool)$this->access_callback;
	}

	/**
	 * Choose an action based on logged-in status when denied access
	 *
	 * @return void
	 */
	protected function access_denied() {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$this->error_403();
		} else {
			$this->login_redirect();
		}
	}

	/**
	 * Display a 403 error page
	 *
	 * @return void
	 */
	protected function error_403() {
		$message = apply_filters('wp_router_access_denied_message', __('You are not authorized to access this page', 'wp-router'));
		$title = apply_filters('wp_router_access_denied_title', __('Access Denied', 'wp-router'));
		$args = apply_filters('wp_router_access_denied_args', array( 'response' => 403 ));
		wp_die($message, $title, $args);
		exit();
	}

	/**
	 * Redirect to the login page
	 * 
	 * @return void
	 */
	protected function login_redirect() {
		$url = wp_login_url($_SERVER['REQUEST_URI']);
		wp_redirect($url);
		exit;
	}

	protected function get_query_args( WP $query, $callback_type = 'page' ) {
		$property = $callback_type.'_arguments';
		$args = array();
		if ( $this->$property ) {
			foreach ( $this->$property as $query_var ) {
				if ( $this->is_a_query_var($query_var, $query) ) {
					if ( isset($query->query_vars[$query_var]) ) {
						$args[] = $query->query_vars[$query_var];
					} else {
						$args[] = NULL;
					}
				} else {
					$args[] = $query_var;
				}
			}
		}
		return $args;
	}

	protected function is_a_query_var( $var, WP $query ) {
		// $query->public_query_vars should be set and filtered before we get here
		if ( in_array($var, $query->public_query_vars) ) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Generate the WP rewrite rule for this route
	 *
	 * @return void
	 */
	protected function generate_rewrite() {
		$rule = "index.php?";
		$vars = array();
		foreach ( $this->query_vars as $var => $value ) {
			if ( is_int($value) ) {
				$vars[] = $var.'='.$this->preg_index($value);
			} else {
				$vars[] = $var.'='.$value;
			}
		}
		$vars[] = self::QUERY_VAR.'='.$this->id;
		$rule .= implode('&', $vars);
		$this->wp_rewrite = $rule;
	}

	/**
	 * Pass an integer through $wp_rewrite->preg_index()
	 *
	 * @param int $matches
	 * @return string
	 */
	protected function preg_index( $int ) {
		global $wp_rewrite;
		$wp_rewrite->matches = 'matches'; // because it may not be set, yet
		return $wp_rewrite->preg_index($int);
	}

	protected function choose_template() {
		if ( $this->template === FALSE ) {
			return FALSE;
		}
		$template = '';
		$extra = array(
			'route-$id.php',
			'route.php',
			'page-$id.php',
			'page.php',
		);
		if ( $this->template ) {
			foreach ( (array) $this->template as $path ) {
				$path = str_replace('$id', $this->id, $path);
				if ( $this->is_absolute_path($path) ) {
					if ( file_exists($path) ) {
						$template = $path;
						break;
					}
				} else { // relative path, look in the theme
					$template = locate_template(array($path));
					if ( $template ) {
						break;
					}
				}
			}
		}
		foreach ( $extra as $key => $path ) {
			$extra[$key] = str_replace('$id', $this->id, $path);
		}
		if ( !$template ) {
			$template = locate_template($extra);
		}
		return $template;
	}

	protected function is_absolute_path( $filename ) {
		$char_1 = substr($filename, 0, 1);
		if ( $char_1 == '/' || $char_1 == '\\' ) {
			return TRUE; // unix absolute path
		}
		$char_2 = substr($filename, 1, 1);
		$char_3 = substr($filename, 2, 1);
		if ( ctype_alpha($char_1) && $char_2 == ':' && ( $char_3 == '/' || $char_3 == '\\') ) {
			return TRUE; // windows absolute path
		}
		return FALSE;
	}
}
