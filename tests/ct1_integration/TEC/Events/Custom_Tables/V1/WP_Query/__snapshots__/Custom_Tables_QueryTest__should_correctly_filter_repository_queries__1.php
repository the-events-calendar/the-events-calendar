<?php return '
			SELECT SQL_CALC_FOUND_ROWS  test_posts.ID, CAST( test_tec_occurrences.start_date AS DATETIME ) AS event_date
			FROM test_posts 
JOIN test_tec_occurrences ON test_posts.ID = test_tec_occurrences.post_id 
			WHERE 1=1  AND test_posts.post_password != \'\' AND test_posts.post_type = \'tribe_events\' AND ((test_posts.post_status <> \'trash\' AND test_posts.post_status <> \'auto-draft\'))
			GROUP BY test_tec_occurrences.occurrence_id
			ORDER BY test_tec_occurrences.start_date ASC, test_posts.post_date ASC
			LIMIT 0, 10
		';
