<?php return '
					SELECT SQL_CALC_FOUND_ROWS  wp_posts.ID
					FROM wp_posts 
					WHERE 1=1  AND ((wp_posts.post_type = \'tribe_venue\' AND (wp_posts.post_status = \'publish\' OR wp_posts.post_status = \'tribe-ea-success\' OR wp_posts.post_status = \'tribe-ea-failed\' OR wp_posts.post_status = \'tribe-ea-schedule\' OR wp_posts.post_status = \'tribe-ea-pending\' OR wp_posts.post_status = \'tribe-ea-draft\')) OR (wp_posts.post_type = \'tribe_events\' AND (wp_posts.post_status = \'publish\' OR wp_posts.post_status = \'tribe-ea-success\' OR wp_posts.post_status = \'tribe-ea-failed\' OR wp_posts.post_status = \'tribe-ea-schedule\' OR wp_posts.post_status = \'tribe-ea-pending\' OR wp_posts.post_status = \'tribe-ea-draft\')))
					
					ORDER BY wp_posts.post_date DESC
					LIMIT 0, 10
				';
