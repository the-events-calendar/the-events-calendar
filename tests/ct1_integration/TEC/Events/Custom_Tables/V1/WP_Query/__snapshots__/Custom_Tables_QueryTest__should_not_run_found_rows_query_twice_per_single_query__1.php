<?php return array (
  0 => 'SELECT SQL_CALC_FOUND_ROWS  test_posts.ID
			FROM test_posts  INNER JOIN test_tec_occurrences ON test_posts.ID = test_tec_occurrences.post_id
			WHERE 1=1  AND ( 
  test_tec_occurrences.start_date > \'2022-10-01 08:00:00\'
) AND ((test_posts.post_type = \'tribe_events\' AND (test_posts.post_status = \'publish\' OR test_posts.post_status = \'tribe-ea-success\' OR test_posts.post_status = \'tribe-ea-failed\' OR test_posts.post_status = \'tribe-ea-schedule\' OR test_posts.post_status = \'tribe-ea-pending\' OR test_posts.post_status = \'tribe-ea-draft\' 
OR test_posts.post_status = \'private\')))
			GROUP BY test_tec_occurrences.occurrence_id
			ORDER BY test_posts.post_date DESC
			LIMIT 0, 10',
  1 => 'SELECT FOUND_ROWS()',
  2 => 'SELECT DISTINCT t.term_id, tr.object_id
			FROM test_terms AS t  INNER JOIN test_term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN test_term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tt.taxonomy IN (\'post_tag\', \'tribe_events_cat\') AND tr.object_id IN (191, 192, 193)
			ORDER BY t.name ASC',
  3 => 'SELECT 3',
);
