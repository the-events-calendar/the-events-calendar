<?php
/**
 * Calendar Embed List Table
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */

namespace TEC\Events\Calendar_Embeds\Admin;

use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use Tribe__Template as Template;
use Tribe__Events__Main as TEC_Plugin;

/**
 * Class List_Table
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */
class List_Table {
	/**
	 * The template.
	 *
	 * @since TBD
	 *
	 * @var Template
	 */
	protected Template $template;

	/**
	 * Customize columns for the table.
	 *
	 * @since TBD
	 *
	 * @param array $columns The columns.
	 *
	 * @return array
	 */
	public function manage_columns( $columns ): array {
		$new_columns = [
			'cb'               => $columns['cb'] ?? '<input type="checkbox" />',
			'title'            => __( 'Calendar Embeds', 'the-events-calendar' ),
			'event_categories' => __( 'Categories', 'the-events-calendar' ),
			'event_tags'       => __( 'Tags', 'the-events-calendar' ),
			'snippet'          => __( 'Embed Snippet', 'the-events-calendar' ),
		];

		/**
		 * Filters the columns for the calendar embeds list table.
		 *
		 * @since TBD
		 *
		 * @param array $new_columns The columns.
		 *
		 * @return array The filtered columns.
		 */
		return (array) apply_filters( 'tec_events_calendar_embeds_list_table_columns', $new_columns );
	}

	/**
	 * Customize the content of the columns.
	 *
	 * @since TBD
	 *
	 * @param string $column_name The name of the column.
	 * @param int    $post_id     The post ID.
	 *
	 * @return void
	 */
	public function manage_column_content( $column_name, $post_id ): void {
		switch ( $column_name ) {
			case 'event_categories':
				// Get events categores from post meta.
				$categories = get_post_meta( $post_id, Calendar_Embeds::META_KEY_CATEGORIES, true );
				if ( ! empty( $categories ) ) {
					$categories = wp_list_pluck( $categories, 'name' );
					echo esc_html( implode( ', ', $categories ) );
				} else {
					echo esc_html( __( 'All Categories', 'the-events-calendar' ) );
				}
				break;
			case 'event_tags':
				// Get events tags from post meta.
				$tags = get_post_meta( $post_id, Calendar_Embeds::META_KEY_TAGS, true );
				if ( ! empty( $tags ) ) {
					$tags = wp_list_pluck( $tags, 'name' );
					echo esc_html( implode( ', ', $tags ) );
				} else {
					echo esc_html( __( 'All Tags', 'the-events-calendar' ) );
				}
				break;
			case 'snippet':
				// @todo Create a class/method to get the embed link.
				$permalink = get_permalink( $post_id );

				$admin_template = $this->get_template();
				$admin_template->template(
					'embed-snippet-content',
					[
						'post_id'   => $post_id,
						'permalink' => $permalink,
					]
				);

				break;
		}
	}

	/**
	 * Get the template.
	 *
	 * @since TBD
	 *
	 * @return Template
	 */
	public function get_template(): Template {
		if ( ! empty( $this->template ) ) {
			return $this->template;
		}

		$this->template = new Template();
		$this->template->set_template_origin( TEC_Plugin::instance() );
		$this->template->set_template_folder( 'src/admin-views/calendar-embeds/' );
		$this->template->set_template_context_extract( true );
		$this->template->set_template_folder_lookup( false );

		return $this->template;
	}
}
