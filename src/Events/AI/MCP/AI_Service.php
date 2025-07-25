<?php
/**
 * MCP class for The Events Calendar AI Engine integration.
 * This class handles the registration and execution of MCP tools
 * for interacting with calendar entities through AI Engine.
 *
 * @since TBD
 *
 * @package TEC\Events\AI\MCP
 */

namespace TEC\Events\AI\MCP;

/**
 * Class AI_Service
 *
 * @since TBD
 *
 * @package TEC\Events\AI\MCP
 */
class AI_Service {

	/**
	 * Get the MCP tool definitions.
	 *
	 * @since TBD
	 *
	 * @return array The tool definitions.
	 */
	public function get_tool_definitions(): array {
		$tools = [];

		// Load from the default file if it exists.
		$default_path = tribe( 'events.main' )->plugin_path . 'src/includes/integration-plugin-mcp-ai-service.php';

		if ( file_exists( $default_path ) ) {
			$tools = include $default_path;

			// Ensure we have an array.
			if ( ! is_array( $tools ) ) {
				$tools = [];
			}
		}

		/**
		 * Filters the MCP tool definitions.
		 *
		 * @since TBD
		 *
		 * @param array  $tools        The tool definitions.
		 * @param string $default_path The default file path.
		 */
		return apply_filters( 'tec_ai_mcp_tool_definitions', $tools, $default_path );
	}

	/**
	 * Initialize the MCP integration.
	 *
	 * @since TBD
	 */
	public function init(): void {
		// Register MCP tools.
		add_filter( 'mwai_mcp_tools', [ $this, 'register_tools' ] );

		// Handle MCP tool execution.
		add_filter( 'mwai_mcp_callback', [ $this, 'handle_tool_execution' ], 10, 4 );
	}

	/**
	 * Unregister the MCP integration.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		remove_filter( 'mwai_mcp_tools', [ $this, 'register_tools' ] );
		remove_filter( 'mwai_mcp_callback', [ $this, 'handle_tool_execution' ], 10 );
	}

	/**
	 * Register MCP tools with AI Engine.
	 	*
	 * @since TBD
	 *
	 * @param array $tools Existing tools array.
	 *
	 * @return array Modified tools array.
	 */
	public function register_tools( $tools ): array {
		// Get tool definitions.
		$tec_tools = $this->get_tool_definitions();

		if ( ! empty( $tec_tools ) ) {
			// Add category to each tool.
			foreach ( $tec_tools as &$tool ) {
				$tool['category'] = 'The Events Calendar';
			}

			// Merge with existing tools.
			$tools = array_merge( $tools, $tec_tools );
		}

		return $tools;
	}

	/**
	 * Handle MCP tool execution.
	 *
	 * @since TBD
	 *
	 * @param mixed  $result The result to filter.
	 * @param string $tool   The tool name.
	 * @param array  $args   The tool arguments.
	 * @param int    $id     The request ID.
	 *
	 * @return mixed The filtered result.
	 */
	public function handle_tool_execution( $result, $tool, $args, $id ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// Only handle our tools.
		if ( strpos( $tool, 'tec-calendar-' ) !== 0 ) {
			return $result;
		}

		try {
			switch ( $tool ) {
				case 'tec-calendar-create-update-entities':
					return $this->handle_create_update_entities( $args );

				case 'tec-calendar-read-entities':
					return $this->handle_read_entities( $args );

				case 'tec-calendar-delete-entities':
					return $this->handle_delete_entities( $args );

				case 'tec-calendar-current-datetime':
					return $this->handle_current_datetime( $args );

				default:
					return [
						'success' => false,
						'error'   => 'Unknown tool: ' . $tool,
					];
			}
		} catch ( \Exception $e ) {
			return [
				'success' => false,
				'error'   => $e->getMessage(),
			];
		}
	}

	/**
	 * Handle create/update entities using REST API.
	 *
	 * @since TBD
	 *
	 * @param array $args Tool arguments.
	 *
	 * @return array Result array.
	 */
	private function handle_create_update_entities( $args ): array {
		$post_type = $args['postType'] ?? '';
		$id        = $args['id'] ?? null;
		$data      = $args['data'] ?? [];

		// Check if tickets are requested without Event Tickets being active.
		if ( $post_type === 'ticket' ) {
			/**
			 * Filters whether to show the ticket requirement error.
			 *
			 * Event Tickets can use this filter to disable the error and handle tickets directly.
			 *
			 * @since TBD
			 *
			 * @param bool   $show_error Whether to show the error. Default true.
			 * @param string $tool_name  The tool being executed ('create_update_entities').
			 */
			$show_error = apply_filters( 'tec_ai_mcp_ticket_requirement_error', true, 'create_update_entities' );

			if ( $show_error ) {
				return [
					'success' => false,
					'error'   => 'Ticket management requires the Event Tickets plugin to be installed and activated.',
				];
			}
		}

		// Map post types to REST endpoints.
		$endpoints = [
			'event'     => 'events',
			'venue'     => 'venues',
			'organizer' => 'organizers',
		];

		if ( ! isset( $endpoints[ $post_type ] ) ) {
			return [
				'success' => false,
				'error'   => 'Invalid post type',
			];
		}

		$endpoint = $endpoints[ $post_type ];
		$namespace = tribe( 'tec.rest-v1.main' )->get_events_route_namespace();

		// Prepare REST request.
		$route = '/' . $namespace . '/' . $endpoint;
		if ( $id ) {
			$route .= '/' . $id;
			$method = 'PUT';
		} else {
			$method = 'POST';
		}

		// Create internal REST request.
		$request = new \WP_REST_Request( $method, $route );

		// Map data fields to REST API format.
		$mapped_data = $this->map_data_to_rest_format( $post_type, $data );
		$request->set_body_params( $mapped_data );

		// Execute REST request.
		$response = rest_do_request( $request );

		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'error'   => $response->get_error_message(),
			];
		}

		if ( $response->get_status() >= 400 ) {
			$data = $response->get_data();
			return [
				'success' => false,
				'error'   => $data['message'] ?? 'Request failed with status ' . $response->get_status(),
			];
		}

		$response_data = $response->get_data();

		return [
			'success' => true,
			'data'    => $response_data,
		];
	}

	/**
	 * Handle read entities using REST API.
	 *
	 * @since TBD
	 *
	 * @param array $args Tool arguments.
	 *
	 * @return array Result array.
	 */
	private function handle_read_entities( $args ): array {
		$post_type = $args['postType'] ?? '';
		$id        = $args['id'] ?? null;

		// Check if tickets are requested without Event Tickets being active.
		if ( $post_type === 'ticket' ) {
			/**
			 * Filters whether to show the ticket requirement error.
			 *
			 * Event Tickets can use this filter to disable the error and handle tickets directly.
			 *
			 * @since TBD
			 *
			 * @param bool   $show_error Whether to show the error. Default true.
			 * @param string $tool_name  The tool being executed ('read_entities').
			 */
			$show_error = apply_filters( 'tec_ai_mcp_ticket_requirement_error', true, 'read_entities' );

			if ( $show_error ) {
				return [
					'success' => false,
					'error'   => 'Ticket management requires the Event Tickets plugin to be installed and activated.',
				];
			}
		}

		// Map post types to REST endpoints.
		$endpoints = [
			'event'     => 'events',
			'venue'     => 'venues',
			'organizer' => 'organizers',
		];

		if ( ! isset( $endpoints[ $post_type ] ) ) {
			return [
				'success' => false,
				'error'   => 'Invalid post type',
			];
		}

		$endpoint = $endpoints[ $post_type ];
		$namespace = tribe( 'tec.rest-v1.main' )->get_events_route_namespace();

		// Prepare REST request.
		$route = '/' . $namespace . '/' . $endpoint;
		if ( $id ) {
			$route .= '/' . $id;
		}

		$request = new \WP_REST_Request( 'GET', $route );

		// Set query parameters.
		$query_params = [];

		if ( ! $id ) {
			// Set pagination parameters.
			$query_params['page']     = $args['page'] ?? 1;
			$query_params['per_page'] = $args['per_page'] ?? 10;

			// Add search parameter.
			if ( ! empty( $args['query'] ) ) {
				$query_params['search'] = $args['query'];
			}

			// Add status filter.
			if ( ! empty( $args['status'] ) ) {
				$query_params['status'] = $args['status'];
			}

			// Add post type specific filters.
			$filter_params = $this->map_filters_to_rest_params( $post_type, $args );
			$query_params = array_merge( $query_params, $filter_params );
		}

		$request->set_query_params( $query_params );

		// Execute REST request.
		$response = rest_do_request( $request );

		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'error'   => $response->get_error_message(),
			];
		}

		if ( $response->get_status() >= 400 ) {
			$data = $response->get_data();
			return [
				'success' => false,
				'error'   => $data['message'] ?? 'Request failed with status ' . $response->get_status(),
			];
		}

		$response_data = $response->get_data();

		// For single items, wrap in data property.
		if ( $id ) {
			return [
				'success' => true,
				'data'    => $response_data,
			];
		}

		// For archives, format the response.
		return [
			'success' => true,
			'data'    => [
				'items'       => $response_data[ $endpoint ] ?? [],
				'total'       => $response_data['total'] ?? 0,
				'total_pages' => $response_data['total_pages'] ?? 0,
				'page'        => $query_params['page'] ?? 1,
				'per_page'    => $query_params['per_page'] ?? 10,
			],
		];
	}

	/**
	 * Handle delete entities using REST API.
	 *
	 * @since TBD
	 *
	 * @param array $args Tool arguments.
	 *
	 * @return array Result array.
	 */
	private function handle_delete_entities( $args ): array {
		$post_type = $args['postType'] ?? '';
		$id        = $args['id'] ?? null;
		$force     = $args['force'] ?? false;

		if ( ! $id ) {
			return [
				'success' => false,
				'error'   => 'Post ID is required',
			];
		}

		// Check if tickets are requested without Event Tickets being active.
		if ( $post_type === 'ticket' ) {
			/**
			 * Filters whether to show the ticket requirement error.
			 *
			 * Event Tickets can use this filter to disable the error and handle tickets directly.
			 *
			 * @since TBD
			 *
			 * @param bool   $show_error Whether to show the error. Default true.
			 * @param string $tool_name  The tool being executed ('delete_entities').
			 */
			$show_error = apply_filters( 'tec_ai_mcp_ticket_requirement_error', true, 'delete_entities' );

			if ( $show_error ) {
				return [
					'success' => false,
					'error'   => 'Ticket management requires the Event Tickets plugin to be installed and activated.',
				];
			}
		}

		// Map post types to REST endpoints.
		$endpoints = [
			'event'     => 'events',
			'venue'     => 'venues',
			'organizer' => 'organizers',
		];

		if ( ! isset( $endpoints[ $post_type ] ) ) {
			return [
				'success' => false,
				'error'   => 'Invalid post type',
			];
		}

		$endpoint = $endpoints[ $post_type ];
		$namespace = tribe( 'tec.rest-v1.main' )->get_events_route_namespace();

		// Prepare REST request.
		$route = '/' . $namespace . '/' . $endpoint . '/' . $id;
		$request = new \WP_REST_Request( 'DELETE', $route );

		// Set force parameter.
		$request->set_param( 'force', $force );

		// Execute REST request.
		$response = rest_do_request( $request );

		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'error'   => $response->get_error_message(),
			];
		}

		if ( $response->get_status() >= 400 ) {
			$data = $response->get_data();
			return [
				'success' => false,
				'error'   => $data['message'] ?? 'Request failed with status ' . $response->get_status(),
			];
		}

		$response_data = $response->get_data();

		return [
			'success' => true,
			'data'    => [
				'id'      => $id,
				'deleted' => true,
				'force'   => $force,
			],
		];
	}

	/**
	 * Handle current datetime.
	 *
	 * @since TBD
	 *
	 * @param array $args Tool arguments (not used for this tool).
	 *
	 * @return array Result array.
	 */
	private function handle_current_datetime( $args ): array { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// Get WordPress timezone.
		$wp_timezone = wp_timezone();
		$wp_now      = new \DateTime( 'now', $wp_timezone );

		// Get local timezone (if different).
		$local_timezone = new \DateTimeZone( date_default_timezone_get() );
		$local_now      = new \DateTime( 'now', $local_timezone );

		// Calculate common date/time examples.
		$tomorrow = clone $wp_now;
		$tomorrow->modify( '+1 day' );

		$next_week = clone $wp_now;
		$next_week->modify( '+1 week' );

		return [
			'success' => true,
			'data'    => [
				'wordpress'   => [
					'timezone'       => $wp_timezone->getName(),
					'current'        => $wp_now->format( 'Y-m-d H:i:s' ),
					'formatted'      => $wp_now->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
					'iso8601'        => $wp_now->format( 'c' ),
					'unix_timestamp' => $wp_now->getTimestamp(),
				],
				'local'       => [
					'timezone'       => $local_timezone->getName(),
					'current'        => $local_now->format( 'Y-m-d H:i:s' ),
					'iso8601'        => $local_now->format( 'c' ),
					'unix_timestamp' => $local_now->getTimestamp(),
				],
				'examples'    => [
					'today_3pm'     => $wp_now->format( 'Y-m-d' ) . ' 15:00:00',
					'tomorrow_10am' => $tomorrow->format( 'Y-m-d' ) . ' 10:00:00',
					'next_week_2pm' => $next_week->format( 'Y-m-d' ) . ' 14:00:00',
				],
				'format_help' => 'Use format: YYYY-MM-DD HH:MM:SS for event dates',
			],
		];
	}

	/**
	 * Map data fields to REST API format.
	 *
	 * @since TBD
	 *
	 * @param string $post_type Post type.
	 * @param array  $data      Input data.
	 *
	 * @return array Mapped data.
	 */
	private function map_data_to_rest_format( $post_type, $data ): array {
		$mapped = [];

		switch ( $post_type ) {
			case 'event':
				// Handle title normalization.
				if ( isset( $data['title'] ) ) {
					$mapped['title'] = $data['title'];
				}
				if ( isset( $data['description'] ) ) {
					$mapped['description'] = $data['description'];
				}
				if ( isset( $data['start_date'] ) ) {
					$mapped['start_date'] = $data['start_date'];
				}
				if ( isset( $data['end_date'] ) ) {
					$mapped['end_date'] = $data['end_date'];
				}
				if ( isset( $data['all_day'] ) ) {
					$mapped['all_day'] = $data['all_day'];
				}
				if ( isset( $data['venue'] ) ) {
					$mapped['venue'] = $data['venue'];
				}
				if ( isset( $data['organizer'] ) ) {
					$mapped['organizer'] = $data['organizer'];
				}
				if ( isset( $data['cost'] ) ) {
					$mapped['cost'] = $data['cost'];
				}
				if ( isset( $data['url'] ) ) {
					$mapped['website'] = $data['url'];
				}
				if ( isset( $data['featured'] ) ) {
					$mapped['featured'] = $data['featured'];
				}
				if ( isset( $data['categories'] ) ) {
					$mapped['categories'] = $data['categories'];
				}
				if ( isset( $data['tags'] ) ) {
					$mapped['tags'] = $data['tags'];
				}
				break;

			case 'venue':
				// Handle title/venue field normalization.
				if ( isset( $data['title'] ) ) {
					$mapped['venue'] = $data['title'];
				} elseif ( isset( $data['venue'] ) ) {
					$mapped['venue'] = $data['venue'];
				}

				// Map other venue fields.
				$field_map = [
					'address'  => 'address',
					'city'     => 'city',
					'state'    => 'stateprovince',
					'province' => 'stateprovince',
					'zip'      => 'zip',
					'country'  => 'country',
					'phone'    => 'phone',
					'url'      => 'website',
				];

				foreach ( $field_map as $from => $to ) {
					if ( isset( $data[ $from ] ) ) {
						$mapped[ $to ] = $data[ $from ];
					}
				}
				break;

			case 'organizer':
				// Handle title/organizer field normalization.
				if ( isset( $data['title'] ) ) {
					$mapped['organizer'] = $data['title'];
				} elseif ( isset( $data['organizer'] ) ) {
					$mapped['organizer'] = $data['organizer'];
				}

				// Map other organizer fields.
				if ( isset( $data['phone'] ) ) {
					$mapped['phone'] = $data['phone'];
				}
				if ( isset( $data['website'] ) ) {
					$mapped['website'] = $data['website'];
				}
				if ( isset( $data['email'] ) ) {
					$mapped['email'] = $data['email'];
				}
				break;
		}

		// Add status if provided.
		if ( isset( $data['status'] ) ) {
			$mapped['status'] = $data['status'];
		}

		return $mapped;
	}

	/**
	 * Map filters to REST API query parameters.
	 *
	 * @since TBD
	 *
	 * @param string $post_type Post type.
	 * @param array  $args      Tool arguments.
	 *
	 * @return array Query parameters.
	 */
	private function map_filters_to_rest_params( $post_type, $args ): array {
		$params = [];

		switch ( $post_type ) {
			case 'event':
				$filters = $args['eventFilters'] ?? [];

				if ( ! empty( $filters['start_date'] ) ) {
					$params['start_date'] = $filters['start_date'];
				}
				if ( ! empty( $filters['end_date'] ) ) {
					$params['end_date'] = $filters['end_date'];
				}
				if ( ! empty( $filters['venue'] ) ) {
					$params['venue'] = $filters['venue'];
				}
				if ( ! empty( $filters['organizer'] ) ) {
					$params['organizer'] = is_array( $filters['organizer'] ) ? $filters['organizer'] : [ $filters['organizer'] ];
				}
				if ( isset( $filters['featured'] ) ) {
					$params['featured'] = $filters['featured'];
				}
				if ( ! empty( $filters['categories'] ) ) {
					$params['categories'] = $filters['categories'];
				}
				if ( ! empty( $filters['tags'] ) ) {
					$params['tags'] = $filters['tags'];
				}
				break;

			case 'venue':
				$filters = $args['venueFilters'] ?? [];

				// The REST API supports geolocation search.
				if ( ! empty( $filters['geo_lat'] ) && ! empty( $filters['geo_lng'] ) ) {
					$params['geoloc'] = true;
					$params['lat']    = $filters['geo_lat'];
					$params['lng']    = $filters['geo_lng'];

					if ( ! empty( $filters['radius'] ) ) {
						$params['geoloc_radius'] = $filters['radius'];
					}
				}

				// Other venue filters would need custom implementation in the REST API.
				break;

			case 'organizer':
				// Organizer filters would need custom implementation in the REST API.
				break;
		}

		// Add include/exclude parameters.
		if ( ! empty( $args['include'] ) ) {
			$params['include'] = $args['include'];
		}
		if ( ! empty( $args['exclude'] ) ) {
			$params['exclude'] = $args['exclude'];
		}

		return $params;
	}
}
