<?php return 'SELECT test_tec_occurrences.post_id
				FROM test_posts
			    	INNER JOIN test_tec_occurrences ON test_posts.ID = test_tec_occurrences.post_id
				WHERE test_posts.post_type = %s
					AND test_tec_occurrences.end_date_utc <= DATE_SUB( CURDATE(), INTERVAL %d MONTH )
				GROUP BY test_tec_occurrences.post_id
				HAVING COUNT(*) = 1
				ORDER BY test_tec_occurrences.start_date_utc ASC, test_tec_occurrences.end_date_utc ASC
				LIMIT %d';
