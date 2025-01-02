<?php return 'SELECT test_tec_occurrences.post_id
				FROM test_posts
			    	INNER JOIN test_tec_occurrences ON test_posts.ID = test_tec_occurrences.post_id
				WHERE test_posts.post_type = "%1$s"
					AND test_tec_occurrences.end_date_utc <= DATE_SUB( CURRENT_TIMESTAMP(), INTERVAL %2$d %4$s )
					AND test_posts.post_status NOT IN ( \'trash\', \'tribe-ignored\' )
				GROUP BY test_tec_occurrences.post_id
				HAVING COUNT(*) = 1
				ORDER BY test_tec_occurrences.start_date_utc ASC, test_tec_occurrences.end_date_utc ASC
				LIMIT %3$d';
