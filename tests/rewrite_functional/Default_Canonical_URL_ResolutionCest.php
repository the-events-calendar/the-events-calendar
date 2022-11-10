<?php

use Rewrite_functionalTester as Tester;

class Default_Canonical_URL_Resolution_Cest {
	public function test_canonical_url_resolution( Tester $I ): void {
		// Log-in as admin to make sure any `current_user_can` check will pass.
		$I->loginAsAdmin();

		$site_url = $I->grabSiteUrl();


		foreach (
			[
				'index.php?post_type=tribe_events&eventDisplay=default&paged=1'         => 'events/',
				'index.php?post_type=tribe_events&eventDisplay=default&paged=3'         => 'events/page/3/',
				'index.php?post_type=tribe_events&featured=1&eventDisplay=list&paged=1' => 'events/list/featured/',
				'index.php?post_type=tribe_events&featured=1&eventDisplay=list&paged=3' => 'events/featured/page/3/',
//				'index.php?post_type=tribe_events&eventDisplay=list&feed=$matches[1]'                                               => 'events/(feed|rdf|rss|rss2|atom)/?$',
//				'index.php?post_type=tribe_events&featured=1&eventDisplay=list&feed=$matches[1]'                                    => 'events/featured/(feed|rdf|rss|rss2|atom)/?$',
//				'index.php?post_type=tribe_events&eventDisplay=month'                                                               => 'events/(?:month)/?$',
//				'index.php?post_type=tribe_events&eventDisplay=month&featured=1'                                                    => 'events/(?:month)/featured/?$',
//				'index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]'                                         => 'events/(?:month)/(\\d{4}-\\d{2})/?$',
//				'index.php?post_type=tribe_events&eventDisplay=list&paged=$matches[1]'                                              => 'events/(?:list)/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&eventDisplay=list&featured=1&paged=$matches[1]'                                   => 'events/(?:list)/featured/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&eventDisplay=list'                                                                => 'events/(?:list)/?$',
//				'index.php?post_type=tribe_events&eventDisplay=list&featured=1'                                                     => 'events/(?:list)/featured/?$',
//				'index.php?post_type=tribe_events&eventDisplay=day'                                                                 => 'events/(?:today)/?$',
//				'index.php?post_type=tribe_events&eventDisplay=day&featured=1'                                                      => 'events/(?:today)/featured/?$',
//				'index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]'                                         => 'events/(\\d{4}-\\d{2})/?$',
//				'index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]&featured=1'                              => 'events/(\\d{4}-\\d{2})/featured/?$',
//				'index.php?post_type=tribe_events&eventDisplay=day&eventDate=$matches[1]'                                           => 'events/(\\d{4}-\\d{2}-\\d{2})/?$',
//				'index.php?post_type=tribe_events&eventDisplay=day&eventDate=$matches[1]&featured=1'                                => 'events/(\\d{4}-\\d{2}-\\d{2})/featured/?$',
//				'index.php?post_type=tribe_events&featured=1'                                                                       => 'events/featured/?$',
//				'index.php?post_type=tribe_events&eventDisplay=default'                                                             => 'events/?$',
//				'index.php?post_type=tribe_events&ical=1'                                                                           => 'events/ical/?$',
//				'index.php?post_type=tribe_events&ical=1&featured=1'                                                                => 'events/featured/ical/?$',
//				'index.php?post_type=tribe_events&ical=1&eventDisplay=day&eventDate=$matches[1]'                                    => 'events/(\\d{4}-\\d{2}-\\d{2})/ical/?$',
//				'index.php?post_type=tribe_events&ical=1&eventDisplay=day&eventDate=$matches[1]&featured=1'                         => 'events/(\\d{4}-\\d{2}-\\d{2})/ical/featured/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&paged=$matches[2]'                 => 'events/(?:category)/(?:[^/]+/)*([^/]+)/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&eventDisplay=list&paged=$matches[2]'      => 'events/(?:category)/(?:[^/]+/)*([^/]+)/featured/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month'                                  => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:month)/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&featured=1'                       => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:month)/featured/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&paged=$matches[2]'                 => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&featured=1&paged=$matches[2]'      => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/featured/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list'                                   => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&featured=1'                        => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/featured/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day'                                    => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:today)/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&featured=1'                         => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:today)/featured/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]'              => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]&featured=1'   => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/featured/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&eventDate=$matches[2]'            => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2})/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&eventDate=$matches[2]&featured=1' => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2})/featured/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]'              => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2}-\\d{2})/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]&featured=1'   => 'events/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2}-\\d{2})/featured/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&feed=rss2'                         => 'events/(?:category)/(?:[^/]+/)*([^/]+)/feed/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&eventDisplay=list&feed=rss2'              => 'events/(?:category)/(?:[^/]+/)*([^/]+)/featured/feed/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&ical=1'                                              => 'events/(?:category)/(?:[^/]+/)*([^/]+)/ical/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&ical=1'                                   => 'events/(?:category)/(?:[^/]+/)*([^/]+)/featured/ical/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&feed=$matches[2]'                                    => 'events/(?:category)/(?:[^/]+/)*([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&feed=$matches[2]'                         => 'events/(?:category)/(?:[^/]+/)*([^/]+)/featured/feed/(feed|rdf|rss|rss2|atom)/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&eventDisplay=default'                     => 'events/(?:category)/(?:[^/]+/)*([^/]+)/featured/?$',
//				'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=default'                                => 'events/(?:category)/(?:[^/]+/)*([^/]+)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&paged=$matches[2]'                              => 'events/(?:tag)/([^/]+)/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&featured=1&eventDisplay=list&paged=$matches[2]'                   => 'events/(?:tag)/([^/]+)/featured/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month'                                               => 'events/(?:tag)/([^/]+)/(?:month)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&featured=1'                                    => 'events/(?:tag)/([^/]+)/(?:month)/featured/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&paged=$matches[2]'                              => 'events/(?:tag)/([^/]+)/(?:list)/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&featured=1&paged=$matches[2]'                   => 'events/(?:tag)/([^/]+)/(?:list)/featured/page/(\\d+)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list'                                                => 'events/(?:tag)/([^/]+)/(?:list)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&featured=1'                                     => 'events/(?:tag)/([^/]+)/(?:list)/featured/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day'                                                 => 'events/(?:tag)/([^/]+)/(?:today)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&featured=1'                                      => 'events/(?:tag)/([^/]+)/(?:today)/featured/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]'                           => 'events/(?:tag)/([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]&featured=1'                => 'events/(?:tag)/([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/featured/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&eventDate=$matches[2]'                         => 'events/(?:tag)/([^/]+)/(\\d{4}-\\d{2})/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&eventDate=$matches[2]&featured=1'              => 'events/(?:tag)/([^/]+)/(\\d{4}-\\d{2})/featured/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]'                           => 'events/(?:tag)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]&featured=1'                => 'events/(?:tag)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/featured/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&feed=rss2'                                      => 'events/(?:tag)/([^/]+)/feed/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&feed=rss2&featured=1'                           => 'events/(?:tag)/([^/]+)/featured/feed/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&ical=1'                                                           => 'events/(?:tag)/([^/]+)/ical/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&featured=1&ical=1'                                                => 'events/(?:tag)/([^/]+)/featured/ical/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&feed=$matches[2]'                                                 => 'events/(?:tag)/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&featured=1&feed=$matches[2]'                                      => 'events/(?:tag)/([^/]+)/featured/feed/(feed|rdf|rss|rss2|atom)/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&featured=1'                                                       => 'events/(?:tag)/([^/]+)/featured/?$',
//				'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=default' => 'events/(?:tag)/([^/]+)/?$',
			] as $input => $expected
		) {
			// Endpoint provided by the plugin put in place in the bootstrap.php file.
			$I->sendAjaxPostRequest( '/wp-json/tec-canonical/url', [
				'url' => $site_url . '/' . ltrim( $input, '/' ),
			] );

			$I->seeResponseIs( $site_url . '/' . ltrim( $expected, '/' ) );
		}
	}
}
