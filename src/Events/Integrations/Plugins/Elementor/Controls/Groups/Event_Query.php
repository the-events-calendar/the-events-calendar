<?php
/**
 * Event Query Elementor Control Group.
 *
 * @since   5.4.0
 *
 * @package Tribe\Events\Integrations\Elementor\Controls\Groups
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Controls\Groups;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Base;
use TEC\Events\Integrations\Plugins\Elementor\Traits\Categories;
use TEC\Events\Integrations\Plugins\Elementor\Traits\Tags;

/**
 * Class Event_Query
 */
class Event_Query extends Group_Control_Base {
	use Categories;
	use Tags;

	/**
	 * @var string Control Group slug.
	 */
	protected static $slug = 'tec_elementor_event_query_group';

	/**
	 * @var array Initialized control fields.
	 */
	protected static $fields;

	/**
	 * {@inheritDoc}
	 */
	public static function get_type() {
		return static::$slug;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param array $args Group control settings value.
	 */
	protected function init_args( $args ) {
		parent::init_args( $args );
		$args           = $this->get_args();
		static::$fields = $this->init_fields_by_name( $args['name'] );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function init_fields() {
		$args = $this->get_args();

		return $this->init_fields_by_name( $args['name'] );
	}

	/**
	 * Initialize controls and tabs via array.
	 *
	 * @since 5.4.0
	 *
	 * @param string $name Control Group name.
	 *
	 * @return array
	 */
	protected function init_fields_by_name( $name ) {
		$fields       = [];
		$current_text = __( 'Use the current event ID', 'the-events-calendar' );
		$event_id     = tribe_get_request_var( 'post', false );

		if ( ! tribe_is_event( $event_id ) ) {
			$current_text = __( 'Use the current event ID (show demo data)', 'the-events-calendar' );
		}

		$fields['id_selection'] = [
			'label'       => __( 'Specify an Event', 'the-events-calendar' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'current',
			'label_block' => true,
			'options'     => [
				'current' => $current_text,
				'custom'  => __( 'Manually enter event ID', 'the-events-calendar' ),
				'search'  => __( 'Select a specific upcoming event by ID', 'the-events-calendar' ),
			],
		];

		$fields['id'] = [
			'label'       => __( 'Event ID', 'the-events-calendar' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'condition'   => [
				'id_selection' => 'custom',
			],
		];

		$fields['search'] = [
			'label'       => __( 'Search', 'the-events-calendar' ),
			'placeholder' => __( 'Search for a specific upcoming event', 'the-events-calendar' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'condition'   => [
				'id_selection' => 'search',
			],
		];

		$fields['slug'] = [
			'label'       => __( 'Event Slug', 'the-events-calendar' ),
			'placeholder' => __( 'Enter a URL-formatted event name', 'the-events-calendar' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'condition'   => [
				'id_selection' => 'search',
			],
		];

		$fields['tab_heading'] = [
			'label'     => __( 'Advanced event filtering', 'the-events-calendar' ),
			'raw'       => '<div class="elementor-control-field-description">' . __( 'Select an upcoming event using date-based rules and meta information.', 'the-events-calendar' ) . '</div>',
			'type'      => Controls_Manager::RAW_HTML,
			'separator' => 'before',
		];

		$fields['query_tabs'] = [
			'type' => Controls_Manager::TABS,
		];

		$tabs_wrapper     = $name . '_query_tabs';
		$date_tab_wrapper = $name . '_date_tab';
		$meta_tab_wrapper = $name . '_meta_tab';

		$fields['date_tab'] = [
			'type'         => Controls_Manager::TAB,
			'label'        => __( 'Dates', 'the-events-calendar' ),
			'tabs_wrapper' => $tabs_wrapper,
		];

		$fields['starts_when'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Starts', 'the-events-calendar' ),
			'type'         => Controls_Manager::SELECT,
			'label_block'  => false,
			'options'      => [
				''            => __( 'Select Date', 'the-events-calendar' ),
				'after'       => __( 'After', 'the-events-calendar' ),
				'before'      => __( 'Before', 'the-events-calendar' ),
				'between'     => __( 'Between', 'the-events-calendar' ),
				'on'          => __( 'On', 'the-events-calendar' ),
				'on_or_after' => __( 'On or After', 'the-events-calendar' ),
			],
		];

		$fields['starts_method'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Entry Format', 'the-events-calendar' ),
			'type'         => Controls_Manager::CHOOSE,
			'label_block'  => false,
			'default'      => 'date',
			'options'      => [
				'date'   => [
					'title' => __( 'Date', 'the-events-calendar' ),
					'icon'  => 'eicon-calendar',
				],
				'custom' => [
					'title' => __( 'Custom', 'the-events-calendar' ),
					'icon'  => 'fa fa-edit',
				],
			],
			'condition'    => [
				'starts_when!' => [ '' ],
			],
		];

		$fields['start_date'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date', 'the-events-calendar' ),
			'type'         => Controls_Manager::DATE_TIME,
			'label_block'  => true,
			'condition'    => [
				'starts_when!'  => [ '', 'between' ],
				'starts_method' => 'date',
			],
		];

		$fields['start_date_custom'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date', 'the-events-calendar' ),
			'description'  => __( 'Enter a date using a standard date format or a relative string like: tomorrow, next week, +5 days, etc', 'the-events-calendar' ),
			'type'         => Controls_Manager::TEXT,
			'label_block'  => true,
			'condition'    => [
				'starts_when!'  => [ '', 'between' ],
				'starts_method' => 'custom',
			],
		];

		$fields['start_date_start'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Lower Boundary', 'the-events-calendar' ),
			'type'         => Controls_Manager::DATE_TIME,
			'label_block'  => true,
			'condition'    => [
				'starts_when'   => 'between',
				'starts_method' => 'date',
			],
		];

		$fields['start_date_end'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Upper Boundary', 'the-events-calendar' ),
			'type'         => Controls_Manager::DATE_TIME,
			'label_block'  => true,
			'condition'    => [
				'starts_when'   => 'between',
				'starts_method' => 'date',
			],
		];

		$fields['start_date_start_custom'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Lower Boundary', 'the-events-calendar' ),
			'description'  => __( 'Enter a date using a standard date format or a relative string like: tomorrow, next week, +5 days, etc', 'the-events-calendar' ),
			'type'         => Controls_Manager::TEXT,
			'label_block'  => true,
			'condition'    => [
				'starts_when'   => 'between',
				'starts_method' => 'custom',
			],
		];

		$fields['start_date_end_custom'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Upper Boundary', 'the-events-calendar' ),
			'description'  => __( 'Enter a date using a standard date format or a relative string like: tomorrow, next week, +5 days, etc', 'the-events-calendar' ),
			'type'         => Controls_Manager::TEXT,
			'label_block'  => true,
			'condition'    => [
				'starts_when'   => 'between',
				'starts_method' => 'custom',
			],
		];

		$fields['ends_when'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Ends', 'the-events-calendar' ),
			'type'         => Controls_Manager::SELECT,
			'label_block'  => false,
			'separator'    => 'before',
			'options'      => [
				''             => __( 'Select Date', 'the-events-calendar' ),
				'after'        => __( 'After', 'the-events-calendar' ),
				'before'       => __( 'Before', 'the-events-calendar' ),
				'between'      => __( 'Between', 'the-events-calendar' ),
				'on'           => __( 'On', 'the-events-calendar' ),
				'on_or_before' => __( 'On or Before', 'the-events-calendar' ),
			],
		];

		$fields['ends_method'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Entry Format', 'the-events-calendar' ),
			'type'         => Controls_Manager::CHOOSE,
			'label_block'  => false,
			'default'      => 'date',
			'options'      => [
				'date'   => [
					'title' => __( 'Date', 'the-events-calendar' ),
					'icon'  => 'eicon-calendar',
				],
				'custom' => [
					'title' => __( 'Custom', 'the-events-calendar' ),
					'icon'  => 'fa fa-edit',
				],
			],
			'condition'    => [
				'ends_when!' => [ '' ],
			],
		];

		$fields['end_date'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date', 'the-events-calendar' ),
			'type'         => Controls_Manager::DATE_TIME,
			'label_block'  => true,
			'condition'    => [
				'ends_when!'  => [ '', 'between' ],
				'ends_method' => 'date',
			],
		];

		$fields['end_date_custom'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date', 'the-events-calendar' ),
			'description'  => __( 'Enter a date using a standard date format or a relative string like: tomorrow, next week, +5 days, etc', 'the-events-calendar' ),
			'type'         => Controls_Manager::TEXT,
			'label_block'  => true,
			'condition'    => [
				'ends_when!'  => [ '', 'between' ],
				'ends_method' => 'custom',
			],
		];

		$fields['end_date_start'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Lower Boundary', 'the-events-calendar' ),
			'type'         => Controls_Manager::DATE_TIME,
			'label_block'  => true,
			'condition'    => [
				'ends_when'   => 'between',
				'ends_method' => 'date',
			],
		];

		$fields['end_date_end'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Upper Boundary', 'the-events-calendar' ),
			'type'         => Controls_Manager::DATE_TIME,
			'label_block'  => true,
			'condition'    => [
				'ends_when'   => 'between',
				'ends_method' => 'date',
			],
		];

		$fields['end_date_start_custom'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Lower Boundary', 'the-events-calendar' ),
			'description'  => __( 'Enter a date using a standard date format or a relative string like: tomorrow, next week, +5 days, etc', 'the-events-calendar' ),
			'type'         => Controls_Manager::TEXT,
			'label_block'  => true,
			'condition'    => [
				'ends_when'   => 'between',
				'ends_method' => 'custom',
			],
		];

		$fields['end_date_end_custom'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $date_tab_wrapper,
			'label'        => __( 'Date Upper Boundary', 'the-events-calendar' ),
			'description'  => __( 'Enter a date using a standard date format or a relative string like: tomorrow, next week, +5 days, etc', 'the-events-calendar' ),
			'type'         => Controls_Manager::TEXT,
			'label_block'  => true,
			'condition'    => [
				'ends_when'   => 'between',
				'ends_method' => 'custom',
			],
		];

		$fields['meta_tab'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'type'         => Controls_Manager::TAB,
			'label'        => __( 'Meta Data', 'the-events-calendar' ),
		];

		$fields['all_day'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $meta_tab_wrapper,
			'label'        => __( 'All-day Events', 'the-events-calendar' ),
			'type'         => Controls_Manager::CHOOSE,
			'toggle'       => false,
			'default'      => 'include',
			'options'      => [
				'include' => [
					'title' => __( 'Include', 'the-events-calendar' ),
					'icon'  => 'fa fa-plus',
				],
				'exclude' => [ // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
					'title' => __( 'Exclude', 'the-events-calendar' ),
					'icon'  => 'fa fa-minus',
				],
				'only'    => [
					'title' => __( 'Only All-day Events', 'the-events-calendar' ),
					'icon'  => 'fa fa-check',
				],
			],
		];

		$fields['multiday'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $meta_tab_wrapper,
			'label'        => __( 'Multi-day Events', 'the-events-calendar' ),
			'type'         => Controls_Manager::CHOOSE,
			'toggle'       => false,
			'default'      => 'include',
			'options'      => [
				'include' => [
					'title' => __( 'Include', 'the-events-calendar' ),
					'icon'  => 'fa fa-plus',
				],
				'exclude' => [ // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
					'title' => __( 'Exclude', 'the-events-calendar' ),
					'icon'  => 'fa fa-minus',
				],
				'only'    => [
					'title' => __( 'Only Multi-day Events', 'the-events-calendar' ),
					'icon'  => 'fa fa-check',
				],
			],
		];

		$fields['featured'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $meta_tab_wrapper,
			'label'        => __( 'Featured Events', 'the-events-calendar' ),
			'type'         => Controls_Manager::CHOOSE,
			'default'      => 'include',
			'toggle'       => false,
			'options'      => [
				'include' => [
					'title' => __( 'Include', 'the-events-calendar' ),
					'icon'  => 'fa fa-plus',
				],
				'exclude' => [ // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
					'title' => __( 'Exclude', 'the-events-calendar' ),
					'icon'  => 'fa fa-minus',
				],
				'only'    => [
					'title' => __( 'Only Featured Events', 'the-events-calendar' ),
					'icon'  => 'fa fa-check',
				],
			],
		];

		$fields['has_geoloc'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $meta_tab_wrapper,
			'label'        => __( 'Geocoded Events', 'the-events-calendar' ),
			'type'         => Controls_Manager::CHOOSE,
			'toggle'       => false,
			'default'      => 'include',
			'options'      => [
				'include' => [
					'title' => __( 'Include', 'the-events-calendar' ),
					'icon'  => 'fa fa-plus',
				],
				'exclude' => [ // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
					'title' => __( 'Exclude', 'the-events-calendar' ),
					'icon'  => 'fa fa-minus',
				],
				'only'    => [
					'title' => __( 'Only Geocoded Events', 'the-events-calendar' ),
					'icon'  => 'fa fa-check',
				],
			],
		];

		$fields['series'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $meta_tab_wrapper,
			'label'        => __( 'Recurring Events', 'the-events-calendar' ),
			'type'         => Controls_Manager::CHOOSE,
			'toggle'       => false,
			'default'      => 'include',
			'options'      => [
				'include' => [
					'title' => __( 'Include', 'the-events-calendar' ),
					'icon'  => 'fa fa-plus',
				],
				'exclude' => [ // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
					'title' => __( 'Exclude', 'the-events-calendar' ),
					'icon'  => 'fa fa-minus',
				],
				'only'    => [
					'title' => __( 'Only Recurring Events', 'the-events-calendar' ),
					'icon'  => 'fa fa-check',
				],
			],
		];

		$fields['category'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $meta_tab_wrapper,
			'label'        => __( 'Category', 'the-events-calendar' ),
			'type'         => Controls_Manager::SELECT2,
			'options'      => $this->get_event_categories(),
			'label_block'  => true,
			'multiple'     => true,
			'separator'    => 'before',
		];

		$fields['post_tag'] = [
			'tabs_wrapper' => $tabs_wrapper,
			'inner_tab'    => $meta_tab_wrapper,
			'label'        => __( 'Tag', 'the-events-calendar' ),
			'type'         => Controls_Manager::SELECT2,
			'options'      => $this->get_event_tags(),
			'label_block'  => true,
			'multiple'     => true,
		];

		return $fields;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_options() {
		return [
			'popover' => false,
		];
	}
}
