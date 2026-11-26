<?php return 'SELECT SQL_CALC_FOUND_ROWS  test_posts.ID
					 FROM test_posts 
					 WHERE 1=1  AND ((test_posts.post_type = \'tribe_events\' AND (test_posts.post_status = \'publish\')))
					 
					 ORDER BY RAND()
					 LIMIT 0, 10';
