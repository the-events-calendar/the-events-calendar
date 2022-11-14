<?php

use Rewrite_functionalTester as Tester;
use Tribe__Events__Main as TEC;

class Default_Canonical_URL_Resolution_Cest {
	private static $eng_expected_canonical_url_mapping = [
		'index.php?post_type=tribe_events&eventDisplay=default&paged=1'                                            => 'events/',
		'index.php?post_type=tribe_events&eventDisplay=default&paged=3'                                            => 'events/page/3/',
		'index.php?post_type=tribe_events&featured=1&eventDisplay=list&paged=1'                                    => 'events/list/featured/',
		'index.php?post_type=tribe_events&featured=1&eventDisplay=list&paged=3'                                    => 'events/featured/page/3/',
		'index.php?post_type=tribe_events&eventDisplay=month'                                                      => 'events/month/',
		'index.php?post_type=tribe_events&eventDisplay=month&featured=1'                                           => 'events/month/featured/',
		'index.php?post_type=tribe_events&eventDisplay=month&eventDate=2022-11'                                    => 'events/month/2022-11/',
		'index.php?post_type=tribe_events&eventDisplay=list&paged=1'                                               => 'events/list/',
		'index.php?post_type=tribe_events&eventDisplay=list&paged=3'                                               => 'events/list/page/3/',
		'index.php?post_type=tribe_events&eventDisplay=list&featured=1&paged=1'                                    => 'events/list/featured/',
		'index.php?post_type=tribe_events&eventDisplay=list&featured=1&paged=3'                                    => 'events/featured/page/3/',
		'index.php?post_type=tribe_events&eventDisplay=list'                                                       => 'events/list/',
		'index.php?post_type=tribe_events&eventDisplay=list&featured=1'                                            => 'events/list/featured/',
		'index.php?post_type=tribe_events&eventDisplay=day'                                                        => 'events/today/',
		'index.php?post_type=tribe_events&eventDisplay=day&featured=1'                                             => 'events/today/featured/',
		'index.php?post_type=tribe_events&eventDisplay=month&eventDate=2022-10'                                    => 'events/month/2022-10/',
		'index.php?post_type=tribe_events&eventDisplay=month&eventDate=2022-11&featured=1'                         => 'events/2022-11/featured/',
		'index.php?post_type=tribe_events&eventDisplay=day&eventDate=2022-10-23'                                   => 'events/2022-10-23/',
		'index.php?post_type=tribe_events&eventDisplay=day&eventDate=2022-10-23&featured=1'                        => 'events/2022-10-23/featured/',
		'index.php?post_type=tribe_events&featured=1'                                                              => 'events/featured/',
		'index.php?post_type=tribe_events&eventDisplay=default'                                                    => 'events/',
		'index.php?post_type=tribe_events&ical=1'                                                                  => 'events/ical/',
		'index.php?post_type=tribe_events&ical=1&featured=1'                                                       => 'events/featured/ical/',
		'index.php?post_type=tribe_events&ical=1&eventDisplay=day&eventDate=2022-10-23'                            => 'events/2022-10-23/ical/',
		'index.php?post_type=tribe_events&ical=1&eventDisplay=day&eventDate=2022-10-23&featured=1'                 => 'events/2022-10-23/ical/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=list&paged=1'                        => 'events/category/lvl_0/list/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=list&paged=3'                        => 'events/category/lvl_0/page/3/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=list&paged=1'                        => 'events/category/lvl_0/lvl_1/list/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=list&paged=3'                        => 'events/category/lvl_0/lvl_1/page/3/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=list&paged=1'                        => 'events/category/lvl_0/lvl_1/lvl_2/list/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=list&paged=3'                        => 'events/category/lvl_0/lvl_1/lvl_2/page/3/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&eventDisplay=list&paged=1'             => 'events/category/lvl_0/list/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&eventDisplay=list&paged=3'             => 'events/category/lvl_0/featured/page/3/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&eventDisplay=list&paged=1'             => 'events/category/lvl_0/lvl_1/list/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&eventDisplay=list&paged=3'             => 'events/category/lvl_0/lvl_1/featured/page/3/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&eventDisplay=list&paged=1'             => 'events/category/lvl_0/lvl_1/lvl_2/list/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&eventDisplay=list&paged=3'             => 'events/category/lvl_0/lvl_1/lvl_2/featured/page/3/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=month'                               => 'events/category/lvl_0/month/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=month'                               => 'events/category/lvl_0/lvl_1/month/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=month'                               => 'events/category/lvl_0/lvl_1/lvl_2/month/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=month&featured=1'                    => 'events/category/lvl_0/month/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=month&featured=1'                    => 'events/category/lvl_0/lvl_1/month/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=month&featured=1'                    => 'events/category/lvl_0/lvl_1/lvl_2/month/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=list&featured=1&paged=1'             => 'events/category/lvl_0/list/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=list&featured=1&paged=3'             => 'events/category/lvl_0/featured/page/3/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=list&featured=1&paged=1'             => 'events/category/lvl_0/lvl_1/list/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=list&featured=1&paged=3'             => 'events/category/lvl_0/lvl_1/featured/page/3/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=list&featured=1&paged=1'             => 'events/category/lvl_0/lvl_1/lvl_2/list/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=list&featured=1&paged=3'             => 'events/category/lvl_0/lvl_1/lvl_2/featured/page/3/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=list'                                => 'events/category/lvl_0/list/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=list'                                => 'events/category/lvl_0/lvl_1/list/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=list'                                => 'events/category/lvl_0/lvl_1/lvl_2/list/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=list&featured=1'                     => 'events/category/lvl_0/list/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=list&featured=1'                     => 'events/category/lvl_0/lvl_1/list/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=list&featured=1'                     => 'events/category/lvl_0/lvl_1/lvl_2/list/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=day'                                 => 'events/category/lvl_0/today/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=day'                                 => 'events/category/lvl_0/lvl_1/today/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=day'                                 => 'events/category/lvl_0/lvl_1/lvl_2/today/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=day&featured=1'                      => 'events/category/lvl_0/today/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=day&featured=1'                      => 'events/category/lvl_0/lvl_1/today/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=day&featured=1'                      => 'events/category/lvl_0/lvl_1/lvl_2/today/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=day&eventDate=2022-10-23'            => 'events/category/lvl_0/day/2022-10-23/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=day&eventDate=2022-10-23'            => 'events/category/lvl_0/lvl_1/day/2022-10-23/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=day&eventDate=2022-10-23'            => 'events/category/lvl_0/lvl_1/lvl_2/day/2022-10-23/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=day&eventDate=2022-10-23&featured=1' => 'events/category/lvl_0/day/2022-10-23/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=day&eventDate=2022-10-23&featured=1' => 'events/category/lvl_0/lvl_1/day/2022-10-23/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=day&eventDate=2022-10-23&featured=1' => 'events/category/lvl_0/lvl_1/lvl_2/day/2022-10-23/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=month&eventDate=2022-11'             => 'events/category/lvl_0/2022-11/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=month&eventDate=2022-11'             => 'events/category/lvl_0/lvl_1/2022-11/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=month&eventDate=2022-11'             => 'events/category/lvl_0/lvl_1/lvl_2/2022-11/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=month&eventDate=2022-11&featured=1'  => 'events/category/lvl_0/2022-11/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=month&eventDate=2022-11&featured=1'  => 'events/category/lvl_0/lvl_1/2022-11/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=month&eventDate=2022-11&featured=1'  => 'events/category/lvl_0/lvl_1/lvl_2/2022-11/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=list&feed=rss2'                      => 'events/category/lvl_0/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=list&feed=rss2'                      => 'events/category/lvl_0/lvl_1/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=list&feed=rss2'                      => 'events/category/lvl_0/lvl_1/lvl_2/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&eventDisplay=list&feed=rss2'           => 'events/category/lvl_0/featured/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&eventDisplay=list&feed=rss2'           => 'events/category/lvl_0/lvl_1/featured/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&eventDisplay=list&feed=rss2'           => 'events/category/lvl_0/lvl_1/lvl_2/featured/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&ical=1'                                           => 'events/category/lvl_0/ical/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&ical=1'                                           => 'events/category/lvl_0/lvl_1/ical/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&ical=1'                                           => 'events/category/lvl_0/lvl_1/lvl_2/ical/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&ical=1'                                => 'events/category/lvl_0/featured/ical/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&ical=1'                                => 'events/category/lvl_0/lvl_1/featured/ical/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&ical=1'                                => 'events/category/lvl_0/lvl_1/lvl_2/featured/ical/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&feed=feed'                                        => 'events/category/lvl_0/feed/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&feed=rdf'                                         => 'events/category/lvl_0/feed/rdf/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&feed=rss'                                         => 'events/category/lvl_0/feed/rss/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&feed=rss2'                                        => 'events/category/lvl_0/feed/rss2/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&feed=atom'                                        => 'events/category/lvl_0/feed/atom/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&feed=feed'                                        => 'events/category/lvl_0/lvl_1/feed/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&feed=rdf'                                         => 'events/category/lvl_0/lvl_1/feed/rdf/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&feed=rss'                                         => 'events/category/lvl_0/lvl_1/feed/rss/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&feed=rss2'                                        => 'events/category/lvl_0/lvl_1/feed/rss2/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&feed=atom'                                        => 'events/category/lvl_0/lvl_1/feed/atom/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&feed=feed'                                        => 'events/category/lvl_0/lvl_1/lvl_2/feed/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&feed=rdf'                                         => 'events/category/lvl_0/lvl_1/lvl_2/feed/rdf/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&feed=rss'                                         => 'events/category/lvl_0/lvl_1/lvl_2/feed/rss/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&feed=rss2'                                        => 'events/category/lvl_0/lvl_1/lvl_2/feed/rss2/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&feed=atom'                                        => 'events/category/lvl_0/lvl_1/lvl_2/feed/atom/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&feed=feed'                             => 'events/category/lvl_0/featured/feed/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&&feed=rdf'                             => 'events/category/lvl_0/featured/feed/rdf/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&&feed=rss'                             => 'events/category/lvl_0/featured/feed/rss/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&&feed=rss2'                            => 'events/category/lvl_0/featured/feed/rss2/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&&feed=atom'                            => 'events/category/lvl_0/featured/feed/atom/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&feed=feed'                             => 'events/category/lvl_0/lvl_1/featured/feed/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&&feed=rdf'                             => 'events/category/lvl_0/lvl_1/featured/feed/rdf/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&&feed=rss'                             => 'events/category/lvl_0/lvl_1/featured/feed/rss/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&&feed=rss2'                            => 'events/category/lvl_0/lvl_1/featured/feed/rss2/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&&feed=atom'                            => 'events/category/lvl_0/lvl_1/featured/feed/atom/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&feed=feed'                             => 'events/category/lvl_0/lvl_1/lvl_2/featured/feed/feed/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&&feed=rdf'                             => 'events/category/lvl_0/lvl_1/lvl_2/featured/feed/rdf/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&&feed=rss'                             => 'events/category/lvl_0/lvl_1/lvl_2/featured/feed/rss/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&&feed=rss2'                            => 'events/category/lvl_0/lvl_1/lvl_2/featured/feed/rss2/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&&feed=atom'                            => 'events/category/lvl_0/lvl_1/lvl_2/featured/feed/atom/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&featured=1&eventDisplay=default'                  => 'events/category/lvl_0/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&featured=1&eventDisplay=default'                  => 'events/category/lvl_0/lvl_1/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&featured=1&eventDisplay=default'                  => 'events/category/lvl_0/lvl_1/lvl_2/featured/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_0&eventDisplay=default'                             => 'events/category/lvl_0/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_1&eventDisplay=default'                             => 'events/category/lvl_0/lvl_1/',
		'index.php?post_type=tribe_events&tribe_events_cat=lvl_2&eventDisplay=default'                             => 'events/category/lvl_0/lvl_1/lvl_2/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=list&paged=1'                                     => 'events/tag/tag_1/list/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=list&paged=3'                                     => 'events/tag/tag_1/page/3/',
		'index.php?post_type=tribe_events&tag=tag_1&featured=1&eventDisplay=list&paged=1'                          => 'events/tag/tag_1/list/featured/',
		'index.php?post_type=tribe_events&tag=tag_1&featured=1&eventDisplay=list&paged=3'                          => 'events/tag/tag_1/featured/page/3/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=month'                                            => 'events/tag/tag_1/month/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=month&featured=1'                                 => 'events/tag/tag_1/month/featured/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=list&featured=1&paged=1'                          => 'events/tag/tag_1/list/featured/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=list&featured=1&paged=3'                          => 'events/tag/tag_1/featured/page/3/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=list'                                             => 'events/tag/tag_1/list/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=list&featured=1'                                  => 'events/tag/tag_1/list/featured/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=day'                                              => 'events/tag/tag_1/today/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=day&featured=1'                                   => 'events/tag/tag_1/today/featured/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=day&eventDate=2022-10-23'                         => 'events/tag/tag_1/day/2022-10-23/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=day&eventDate=2022-10-23&featured=1'              => 'events/tag/tag_1/day/2022-10-23/featured/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=month&eventDate=2022-11'                          => 'events/tag/tag_1/2022-11/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=month&eventDate=2022-11&featured=1'               => 'events/tag/tag_1/2022-11/featured/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=list&feed=rss2'                                   => 'events/tag/tag_1/feed/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=list&feed=rss2&featured=1'                        => 'events/tag/tag_1/featured/feed/',
		'index.php?post_type=tribe_events&tag=tag_1&ical=1'                                                        => 'events/tag/tag_1/ical/',
		'index.php?post_type=tribe_events&tag=tag_1&featured=1&ical=1'                                             => 'events/tag/tag_1/featured/ical/',
		'index.php?post_type=tribe_events&tag=tag_1&feed=feed'                                                     => 'events/tag/tag_1/feed/feed/',
		'index.php?post_type=tribe_events&tag=tag_1&feed=rdf'                                                      => 'events/tag/tag_1/feed/rdf/',
		'index.php?post_type=tribe_events&tag=tag_1&feed=rss'                                                      => 'events/tag/tag_1/feed/rss/',
		'index.php?post_type=tribe_events&tag=tag_1&feed=rss2'                                                     => 'events/tag/tag_1/feed/rss2/',
		'index.php?post_type=tribe_events&tag=tag_1&feed=atom'                                                     => 'events/tag/tag_1/feed/atom/',
		'index.php?post_type=tribe_events&tag=tag_1&featured=1&feed=feed'                                          => 'events/tag/tag_1/featured/feed/feed/',
		'index.php?post_type=tribe_events&tag=tag_1&featured=1&feed=rdf'                                           => 'events/tag/tag_1/featured/feed/rdf/',
		'index.php?post_type=tribe_events&tag=tag_1&featured=1&feed=rss'                                           => 'events/tag/tag_1/featured/feed/rss/',
		'index.php?post_type=tribe_events&tag=tag_1&featured=1&feed=rss2'                                          => 'events/tag/tag_1/featured/feed/rss2/',
		'index.php?post_type=tribe_events&tag=tag_1&featured=1&feed=atom'                                          => 'events/tag/tag_1/featured/feed/atom/',
		'index.php?post_type=tribe_events&tag=tag_1&featured=1'                                                    => 'events/tag/tag_1/featured/',
		'index.php?post_type=tribe_events&tag=tag_1&eventDisplay=default'                                          => 'events/tag/tag_1/',
	];

	private function given_some_event_categories_and_tags( Rewrite_functionalTester $I ): void {
		[ $lvl_0_term_id ] = $I->haveTermInDatabase( 'lvl_0', 'tribe_events_cat', [ 'slug' => 'lvl_0' ] );
		[ $lvl_1_term_id ] = $I->haveTermInDatabase( 'lvl_1', 'tribe_events_cat', [
			'slug'   => 'lvl_1',
			'parent' => $lvl_0_term_id
		] );
		[ $lvl_2_term_id ] = $I->haveTermInDatabase( 'lvl_2', 'tribe_events_cat', [
			'slug'   => 'lvl_2',
			'parent' => $lvl_1_term_id
		] );
		$I->haveTermInDatabase( 'tag_1', 'post_tag', [ 'slug' => 'tag_1' ] );
	}

	public function test_canonical_url_resolution_for_en_US_user_on_en_US_site( Tester $I ): void {
		// The language should be default one, en_US.
		$I->assertEquals( '', $I->grabOptionFromDatabase( 'WPLANG' ) );
		// The language of the admin should be default one, en_US.
		$I->assertEquals( [ '' ], $I->grabUserMetaFromDatabase( 1, 'locale' ) );
		// Canonical URL resolution will require some categories and tags to be present to work correctly.
		$this->given_some_event_categories_and_tags( $I );
		// Log-in as admin to make sure any `current_user_can` check will pass.
		$I->loginAsAdmin();
		$site_url = $I->grabSiteUrl();

		foreach ( self::$eng_expected_canonical_url_mapping as $input => $expected ) {
			// Endpoint provided by the plugin put in place in the bootstrap.php file.
			$I->sendAjaxPostRequest( '/wp-json/tec-canonical/url', [
				'url' => $site_url . '/' . ltrim( $input, '/' ),
			] );

			$I->seeResponseIs( $site_url . '/' . ltrim( $expected, '/' ) );
		}
	}

	public function test_canonical_url_resolution_for_it_IT_user_on_en_US_site( Tester $I ): void {
		// Create a new administrator user with a different language locale.
		$admin_it_id = $I->haveUserInDatabase( 'admin_it', 'administrator', [
				'user_login' => 'admin_it',
				'user_pass'  => 'admin_it',
				'user_email' => 'admin_it@wordpress.test',
			]
		);
		//Ensure the user locale is set to it_IT.
		$I->dontHaveUserMetaInDatabase( [ 'user_id' => $admin_it_id, 'meta_key' => 'locale' ] );
		$I->haveUserMetaInDatabase( $admin_it_id, 'locale', 'it_IT' );
		// The language should be default one, en_US.
		$I->assertEquals( '', $I->grabOptionFromDatabase( 'WPLANG' ) );
		// The language of the admin should be default one, en_US.
		$I->assertEquals( [ 'it_IT' ], $I->grabUserMetaFromDatabase( $admin_it_id, 'locale' ) );
		// Canonical URL resolution will require some categories and tags to be present to work correctly.
		$this->given_some_event_categories_and_tags( $I );
		// Log-in as admin to make sure any `current_user_can` check will pass.
		$I->loginAs( 'admin_it', 'admin_it' );
		$site_url = $I->grabSiteUrl();

		// The resolution en_US should not change because the user has an it_IT locale.
		foreach ( self::$eng_expected_canonical_url_mapping as $input => $expected ) {
			// Endpoint provided by the plugin put in place in the bootstrap.php file.
			$I->sendAjaxPostRequest( '/wp-json/tec-canonical/url', [
				'url' => $site_url . '/' . ltrim( $input, '/' ),
			] );

			$I->seeResponseIs( $site_url . '/' . ltrim( $expected, '/' ) );
		}

		// Now have the it_IT admin regenerate rewrite rules.
		$I->amOnAdminPage( '/options-permalink.php' );
		$I->click( '#submit' );

		// Test the resolution of the it_IT admin again.
		// The resolution en_US should not change because the user has an it_IT locale.
		foreach ( self::$eng_expected_canonical_url_mapping as $input => $expected ) {
			// Endpoint provided by the plugin put in place in the bootstrap.php file.
			$I->sendAjaxPostRequest( '/wp-json/tec-canonical/url', [
				'url' => $site_url . '/' . ltrim( $input, '/' ),
			] );

			$I->seeResponseIs( $site_url . '/' . ltrim( $expected, '/' ) );
		}
	}
}
