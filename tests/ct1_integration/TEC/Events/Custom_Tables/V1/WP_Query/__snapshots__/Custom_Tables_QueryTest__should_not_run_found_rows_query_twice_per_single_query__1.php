<?php return array (
  0 => 'SELECT SQL_CALC_FOUND_ROWS  wp_posts.ID
			FROM wp_posts  INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )
			WHERE 1=1  AND ( 
  ( wp_postmeta.meta_key = \'_EventStartDate\' AND wp_postmeta.meta_value > \'2022-10-01 08:00:00\' )
) AND ((wp_posts.post_type = \'tribe_events\' AND (wp_posts.post_status = \'publish\' OR wp_posts.post_status = \'tribe-ea-success\' OR wp_posts.post_status = \'tribe-ea-failed\' OR wp_posts.post_status = \'tribe-ea-schedule\' OR wp_posts.post_status = \'tribe-ea-pending\' OR wp_posts.post_status = \'tribe-ea-draft\' 
OR wp_posts.post_status = \'private\')))
			GROUP BY wp_posts.ID
			ORDER BY wp_posts.post_date DESC
			LIMIT 0, 10',
  1 => 'SELECT FOUND_ROWS()',
);
