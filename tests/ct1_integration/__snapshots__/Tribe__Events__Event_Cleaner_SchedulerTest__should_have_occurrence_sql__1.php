<?php return '
			SELECT post_id
			FROM wp_posts AS t1
			INNER JOIN wp_postmeta AS t2 ON t1.ID = t2.post_id
			WHERE
				t1.post_type = %s
				AND t2.meta_key = \'_EventEndDate\'
				AND t2.meta_value <= DATE_SUB( CURRENT_TIMESTAMP(), INTERVAL %d %3s )
				AND t2.meta_value != 0
				AND t2.meta_value != \'\'
				AND t2.meta_value IS NOT NULL
				AND t1.post_parent = 0
				AND t1.ID NOT IN ( 
			SELECT DISTINCT post_parent
			FROM wp_posts
			WHERE
				post_type= \'tribe_events\'
				AND post_parent <> 0
		 )
			LIMIT %d
		';
