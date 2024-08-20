<?php return '
					SELECT SQL_CALC_FOUND_ROWS  wp_posts.ID
					FROM wp_posts  INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )  INNER JOIN wp_postmeta AS mt1 ON ( wp_posts.ID = mt1.post_id )
					WHERE 1=1  AND ( 
  wp_postmeta.meta_key = \'_EventStartDate\' 
  AND 
  ( mt1.meta_key = \'_EventEndDate\' AND CAST(mt1.meta_value AS DATETIME) >= \'2022-09-28 13:00:00\' )
) AND ((wp_posts.post_type = \'tribe_events\' AND (wp_posts.post_status = \'publish\' OR wp_posts.post_status = \'tribe-ea-success\' OR wp_posts.post_status = \'tribe-ea-failed\' OR wp_posts.post_status = \'tribe-ea-schedule\' OR wp_posts.post_status = \'tribe-ea-pending\' OR wp_posts.post_status = \'tribe-ea-draft\')))
					GROUP BY wp_posts.ID
					ORDER BY CAST(wp_postmeta.meta_value AS CHAR) ASC, wp_posts.post_date ASC
					LIMIT 0, 10
				';
