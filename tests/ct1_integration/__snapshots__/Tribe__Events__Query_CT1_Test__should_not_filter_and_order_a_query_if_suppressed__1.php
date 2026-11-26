<?php return 'SELECT SQL_CALC_FOUND_ROWS  test_posts.ID
					 FROM test_posts  JOIN test_tec_occurrences ON test_posts.ID = test_tec_occurrences.post_id
					 WHERE 1=1  AND ((test_posts.post_type = \'tribe_events\' AND (test_posts.post_status = \'publish\')))
					 
					 ORDER BY test_posts.post_date DESC
					 LIMIT 0, 10';
