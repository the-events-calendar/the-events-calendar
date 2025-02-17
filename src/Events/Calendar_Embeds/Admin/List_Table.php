<?php
/**
 * Calendar Embedw List Table
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */

namespace TEC\Events\Calendar_Embeds\Admin;

/**
 * Class List_Table
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */
class List_Table {
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
			'cb'               => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />',
			'title'            => __( 'Calendar Embeds', 'the-events-calendar' ),
			'event_categories' => __( 'Categories', 'the-events-calendar' ),
			'post_tags'        => __( 'Tags', 'the-events-calendar' ),
			'snippet'          => __( 'Embed Snippet', 'the-events-calendar' ),
		];

		return $new_columns;
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
		error_log('testing');
		switch ( $column_name ) {
			case 'event_categories':
				$categories = get_the_terms( $post_id, 'category' );
				if ( ! empty( $categories ) ) {
					$categories = wp_list_pluck( $categories, 'name' );
					echo esc_html( implode( ', ', $categories ) );
				} else {
					echo esc_html( __( 'All Categories', 'the-events-calendar' ) );
				}
				break;
			case 'post_tags':
				$tags = get_the_terms( $post_id, 'post_tag' );
				if ( ! empty( $tags ) ) {
					$tags = wp_list_pluck( $tags, 'name' );
					echo esc_html( implode( ', ', $tags ) );
				} else {
					echo esc_html( __( 'All Tags', 'the-events-calendar' ) );
				}
				break;
			case 'snippet':
				add_thickbox();
				// get permalink
				$permalink = get_permalink( $post_id );

				// create snippet to show iframe
				$snippet = '<iframe src="' . esc_url( $permalink ) . '" width="800" height="600"></iframe>';

				// create html for modal to display snippet
				$html = '<div id="snippet_' . $post_id . '" class="hidden"><p>Copy and paste this code to embed the calendar on your website:<br><textarea rows="3" style="width:100%" readonly="readonly">' . esc_html( $snippet ) . '</textarea></p></div>';

				// create thickbox link to show modal
				$html .= '<a name="Embed Snippet" href="/?TB_inline?width=400&height=300&inlineId=snippet_' . $post_id . '" class="thickbox page-title-action">Get Embed Snippet</a>';

				echo wp_kses_post( $html );
				break;
		}
	}
}
