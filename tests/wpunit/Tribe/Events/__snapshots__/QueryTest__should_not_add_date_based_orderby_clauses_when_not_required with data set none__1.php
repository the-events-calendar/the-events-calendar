<?php return '
					SELECT SQL_CALC_FOUND_ROWS  test_posts.ID
					FROM test_posts 
					WHERE 1=1  AND ((test_posts.post_type = \'tribe_events\' AND (test_posts.post_status = \'publish\' OR test_posts.post_status = \'tribe-ea-success\' OR test_posts.post_status = \'tribe-ea-failed\' OR test_posts.post_status = \'tribe-ea-schedule\' OR test_posts.post_status = \'tribe-ea-pending\' OR test_posts.post_status = \'tribe-ea-draft\' OR test_posts.post_status = \'future\' OR test_posts.post_status = \'draft\' OR test_posts.post_status = \'pending\')))
					
					
					LIMIT 0, 10
				';
