<?php
namespace TEC\Events\Calendar_Embeds;

use TEC\Events\Calendar_Embeds\Admin\List_Table;
use Tribe\Tests\Traits\With_Uopz;
use Spatie\Snapshots\MatchesSnapshots;

class List_TableTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;
	use MatchesSnapshots;

	public function testManageColumns() {
		$new_columns = tribe( List_Table::class )->manage_columns( [] );

		$this->assertEquals( '<input type="checkbox" />', $new_columns['cb'] );
		$this->assertEquals( 'Calendar Embeds', $new_columns['title'] );
		$this->assertEquals( 'Categories', $new_columns['event_categories'] );
		$this->assertEquals( 'Tags', $new_columns['event_tags'] );
		$this->assertEquals( 'Embed Snippet', $new_columns['snippet'] );
	}

	public function test_manage_columns_with_filtered_columns() {

		add_filter( 'tec_events_calendar_embeds_list_table_columns', function( $new_columns ) {
			$new_columns['custom_column'] = 'Custom Column';
			return $new_columns;
		} );

		$new_columns = tribe( List_Table::class )->manage_columns( [] );

		$this->assertEquals( '<input type="checkbox" />', $new_columns['cb'] );
		$this->assertEquals( 'Calendar Embeds', $new_columns['title'] );
		$this->assertEquals( 'Categories', $new_columns['event_categories'] );
		$this->assertEquals( 'Tags', $new_columns['event_tags'] );
		$this->assertEquals( 'Embed Snippet', $new_columns['snippet'] );
		$this->assertEquals( 'Custom Column', $new_columns['custom_column'] );
	}

	public function testManageColumnContent() {
		// Create Calendar Embed
		$post_id = wp_insert_post( [
			'post_title' => 'Test Embed',
			'post_type'  => Calendar_Embeds::POSTTYPE,
		] );

		// Test before categories are set.
		ob_start();
		tribe( List_Table::class )->manage_column_content( 'event_categories', $post_id );
		$category_output = ob_get_clean();
		$this->assertEquals( 'All Categories', $category_output );

		// Test before tags are set.
		ob_start();
		tribe( List_Table::class )->manage_column_content( 'event_tags', $post_id );
		$tag_output = ob_get_clean();
		$this->assertEquals( 'All Tags', $tag_output );

		// Add categories and tags via meta.
		update_post_meta( $post_id, Calendar_Embeds::$meta_key_categories, [
			[
				'id' => 1,
				'name' => 'Category 1',
			],
			[
				'id' => 2,
				'name' => 'Category 2',
			],
		 ] );
		update_post_meta( $post_id, Calendar_Embeds::$meta_key_tags, [
			[
				'id' => 1,
				'name' => 'Tag 1',
			],
			[
				'id' => 2,
				'name' => 'Tag 2',
			],
		 ] );

		// Mock get_permalink to return a permalink.
		$this->set_fn_return( 'get_permalink', 'http://example.com/123456abcdef/embed' );

		// Test categories.
		ob_start();
		tribe( List_Table::class )->manage_column_content( 'event_categories', $post_id );
		$category_output = ob_get_clean();
		$this->assertEquals( 'Category 1, Category 2', $category_output );

		// Test tags.
		ob_start();
		tribe( List_Table::class )->manage_column_content( 'event_tags', $post_id );
		$tag_output = ob_get_clean();
		$this->assertEquals( 'Tag 1, Tag 2', $tag_output );

		ob_start();
		tribe( List_Table::class )->manage_column_content( 'snippet', $post_id );
		$snippet = ob_get_clean();

		$snippet = str_replace( [
			'tec_events_calendar_embeds_snippet_code_' . $post_id,
			'tec_events_calendar_embeds_snippet_' . $post_id,
		], [
			'tec_events_calendar_embeds_snippet_code_POSTID',
			'tec_events_calendar_embeds_snippet_POSTID',
		], $snippet );

		$this->assertMatchesSnapshot( $snippet );
	}
}
