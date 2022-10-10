<?php return '
					SELECT SQL_CALC_FOUND_ROWS  test_posts.ID
					FROM test_posts  INNER JOIN test_postmeta ON ( test_posts.ID = test_postmeta.post_id )  INNER JOIN test_postmeta AS mt1 ON ( test_posts.ID = mt1.post_id )
					WHERE 1=1  AND ( 
  test_postmeta.meta_key = \'_EventStartDate\' 
  AND 
  ( mt1.meta_key = \'_EventEndDate\' AND CAST(mt1.meta_value AS DATETIME) >= \'2022-09-28 13:00:00\' )
) AND ((test_posts.post_type = \'tribe_events\' AND (test_posts.post_status = \'publish\' OR test_posts.post_status = \'tribe-ea-success\' OR test_posts.post_status = \'tribe-ea-failed\' OR test_posts.post_status = \'tribe-ea-schedule\' OR test_posts.post_status = \'tribe-ea-pending\' OR test_posts.post_status = \'tribe-ea-draft\')))
					GROUP BY test_posts.ID
					ORDER BY CAST(test_postmeta.meta_value AS CHAR) ASC, test_posts.post_date ASC
					LIMIT 0, 10
				';
