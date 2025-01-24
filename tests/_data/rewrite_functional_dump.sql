-- MariaDB dump 10.19  Distrib 10.5.23-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: db    Database: test
-- ------------------------------------------------------
-- Server version	10.7.8-MariaDB-1:10.7.8+maria~ubu2004

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `test_commentmeta`
--

DROP TABLE IF EXISTS `test_commentmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_commentmeta`
--

LOCK TABLES `test_commentmeta` WRITE;
/*!40000 ALTER TABLE `test_commentmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_commentmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_comments`
--

DROP TABLE IF EXISTS `test_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT 0,
  `comment_author` tinytext NOT NULL,
  `comment_author_email` varchar(100) NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT 0,
  `comment_approved` varchar(20) NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) NOT NULL DEFAULT '',
  `comment_type` varchar(20) NOT NULL DEFAULT 'comment',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_comments`
--

LOCK TABLES `test_comments` WRITE;
/*!40000 ALTER TABLE `test_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_links`
--

DROP TABLE IF EXISTS `test_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) NOT NULL DEFAULT '',
  `link_name` varchar(255) NOT NULL DEFAULT '',
  `link_image` varchar(255) NOT NULL DEFAULT '',
  `link_target` varchar(25) NOT NULL DEFAULT '',
  `link_description` varchar(255) NOT NULL DEFAULT '',
  `link_visible` varchar(20) NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) unsigned NOT NULL DEFAULT 1,
  `link_rating` int(11) NOT NULL DEFAULT 0,
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) NOT NULL DEFAULT '',
  `link_notes` mediumtext NOT NULL,
  `link_rss` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_links`
--

LOCK TABLES `test_links` WRITE;
/*!40000 ALTER TABLE `test_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_options`
--

DROP TABLE IF EXISTS `test_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) NOT NULL DEFAULT '',
  `option_value` longtext NOT NULL,
  `autoload` varchar(20) NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_options`
--

LOCK TABLES `test_options` WRITE;
/*!40000 ALTER TABLE `test_options` DISABLE KEYS */;
INSERT INTO `test_options` VALUES (2,'siteurl','http://wordpress.test','yes'),(3,'home','http://wordpress.test','yes'),(4,'blogname','The Events Calendar PRO Tests','yes'),(5,'blogdescription','','yes'),(6,'users_can_register','0','yes'),(7,'admin_email','admin@wordpress.test','yes'),(8,'start_of_week','1','yes'),(9,'use_balanceTags','0','yes'),(10,'use_smilies','1','yes'),(11,'require_name_email','1','yes'),(12,'comments_notify','1','yes'),(13,'posts_per_rss','10','yes'),(14,'rss_use_excerpt','0','yes'),(15,'mailserver_url','mail.example.com','yes'),(16,'mailserver_login','login@example.com','yes'),(17,'mailserver_pass','password','yes'),(18,'mailserver_port','110','yes'),(19,'default_category','1','yes'),(20,'default_comment_status','open','yes'),(21,'default_ping_status','open','yes'),(22,'default_pingback_flag','1','yes'),(23,'posts_per_page','10','yes'),(24,'date_format','F j, Y','yes'),(25,'time_format','g:i a','yes'),(26,'links_updated_date_format','F j, Y g:i a','yes'),(27,'comment_moderation','0','yes'),(28,'moderation_notify','1','yes'),(29,'rewrite_rules','','yes'),(30,'hack_file','0','yes'),(31,'blog_charset','UTF-8','yes'),(32,'moderation_keys','','no'),(33,'active_plugins','a:2:{i:0;s:34:\"events-pro/events-calendar-pro.php\";i:1;s:43:\"the-events-calendar/the-events-calendar.php\";}','yes'),(34,'category_base','','yes'),(35,'ping_sites','http://rpc.pingomatic.com/','yes'),(36,'comment_max_links','2','yes'),(37,'gmt_offset','0','yes'),(38,'default_email_category','1','yes'),(39,'recently_edited','','no'),(40,'template','twentytwentythree','yes'),(41,'stylesheet','twentytwentythree','yes'),(42,'comment_registration','0','yes'),(43,'html_type','text/html','yes'),(44,'use_trackback','0','yes'),(45,'default_role','subscriber','yes'),(46,'db_version','53496','yes'),(47,'uploads_use_yearmonth_folders','1','yes'),(48,'upload_path','','yes'),(49,'blog_public','1','yes'),(50,'default_link_category','2','yes'),(51,'show_on_front','posts','yes'),(52,'tag_base','','yes'),(53,'show_avatars','1','yes'),(54,'avatar_rating','G','yes'),(55,'upload_url_path','','yes'),(56,'thumbnail_size_w','150','yes'),(57,'thumbnail_size_h','150','yes'),(58,'thumbnail_crop','1','yes'),(59,'medium_size_w','300','yes'),(60,'medium_size_h','300','yes'),(61,'avatar_default','mystery','yes'),(62,'large_size_w','1024','yes'),(63,'large_size_h','1024','yes'),(64,'image_default_link_type','none','yes'),(65,'image_default_size','','yes'),(66,'image_default_align','','yes'),(67,'close_comments_for_old_posts','0','yes'),(68,'close_comments_days_old','14','yes'),(69,'thread_comments','1','yes'),(70,'thread_comments_depth','5','yes'),(71,'page_comments','0','yes'),(72,'comments_per_page','50','yes'),(73,'default_comments_page','newest','yes'),(74,'comment_order','asc','yes'),(75,'sticky_posts','a:0:{}','yes'),(76,'widget_categories','a:0:{}','yes'),(77,'widget_text','a:0:{}','yes'),(78,'widget_rss','a:0:{}','yes'),(79,'uninstall_plugins','a:1:{s:34:\"events-pro/events-calendar-pro.php\";a:2:{i:0;s:23:\"Tribe__Events__Pro__PUE\";i:1;s:9:\"uninstall\";}}','no'),(80,'timezone_string','','yes'),(81,'page_for_posts','0','yes'),(82,'page_on_front','0','yes'),(83,'default_post_format','0','yes'),(84,'link_manager_enabled','0','yes'),(85,'finished_splitting_shared_terms','1','yes'),(86,'site_icon','0','yes'),(87,'medium_large_size_w','768','yes'),(88,'medium_large_size_h','0','yes'),(89,'wp_page_for_privacy_policy','3','yes'),(90,'show_comments_cookies_opt_in','1','yes'),(91,'admin_email_lifespan','1683552021','yes'),(92,'disallowed_keys','','no'),(93,'comment_previously_approved','1','yes'),(94,'auto_plugin_theme_update_emails','a:0:{}','no'),(95,'auto_update_core_dev','enabled','yes'),(96,'auto_update_core_minor','enabled','yes'),(97,'auto_update_core_major','enabled','yes'),(98,'wp_force_deactivated_plugins','a:0:{}','yes'),(99,'initial_db_version','53496','yes'),(100,'test_user_roles','a:5:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:101:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;s:25:\"read_private_tribe_events\";b:1;s:17:\"edit_tribe_events\";b:1;s:24:\"edit_others_tribe_events\";b:1;s:25:\"edit_private_tribe_events\";b:1;s:27:\"edit_published_tribe_events\";b:1;s:19:\"delete_tribe_events\";b:1;s:26:\"delete_others_tribe_events\";b:1;s:27:\"delete_private_tribe_events\";b:1;s:29:\"delete_published_tribe_events\";b:1;s:20:\"publish_tribe_events\";b:1;s:25:\"read_private_tribe_venues\";b:1;s:17:\"edit_tribe_venues\";b:1;s:24:\"edit_others_tribe_venues\";b:1;s:25:\"edit_private_tribe_venues\";b:1;s:27:\"edit_published_tribe_venues\";b:1;s:19:\"delete_tribe_venues\";b:1;s:26:\"delete_others_tribe_venues\";b:1;s:27:\"delete_private_tribe_venues\";b:1;s:29:\"delete_published_tribe_venues\";b:1;s:20:\"publish_tribe_venues\";b:1;s:29:\"read_private_tribe_organizers\";b:1;s:21:\"edit_tribe_organizers\";b:1;s:28:\"edit_others_tribe_organizers\";b:1;s:29:\"edit_private_tribe_organizers\";b:1;s:31:\"edit_published_tribe_organizers\";b:1;s:23:\"delete_tribe_organizers\";b:1;s:30:\"delete_others_tribe_organizers\";b:1;s:31:\"delete_private_tribe_organizers\";b:1;s:33:\"delete_published_tribe_organizers\";b:1;s:24:\"publish_tribe_organizers\";b:1;s:31:\"read_private_aggregator-records\";b:1;s:23:\"edit_aggregator-records\";b:1;s:30:\"edit_others_aggregator-records\";b:1;s:31:\"edit_private_aggregator-records\";b:1;s:33:\"edit_published_aggregator-records\";b:1;s:25:\"delete_aggregator-records\";b:1;s:32:\"delete_others_aggregator-records\";b:1;s:33:\"delete_private_aggregator-records\";b:1;s:35:\"delete_published_aggregator-records\";b:1;s:26:\"publish_aggregator-records\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:74:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:25:\"read_private_tribe_events\";b:1;s:17:\"edit_tribe_events\";b:1;s:24:\"edit_others_tribe_events\";b:1;s:25:\"edit_private_tribe_events\";b:1;s:27:\"edit_published_tribe_events\";b:1;s:19:\"delete_tribe_events\";b:1;s:26:\"delete_others_tribe_events\";b:1;s:27:\"delete_private_tribe_events\";b:1;s:29:\"delete_published_tribe_events\";b:1;s:20:\"publish_tribe_events\";b:1;s:25:\"read_private_tribe_venues\";b:1;s:17:\"edit_tribe_venues\";b:1;s:24:\"edit_others_tribe_venues\";b:1;s:25:\"edit_private_tribe_venues\";b:1;s:27:\"edit_published_tribe_venues\";b:1;s:19:\"delete_tribe_venues\";b:1;s:26:\"delete_others_tribe_venues\";b:1;s:27:\"delete_private_tribe_venues\";b:1;s:29:\"delete_published_tribe_venues\";b:1;s:20:\"publish_tribe_venues\";b:1;s:29:\"read_private_tribe_organizers\";b:1;s:21:\"edit_tribe_organizers\";b:1;s:28:\"edit_others_tribe_organizers\";b:1;s:29:\"edit_private_tribe_organizers\";b:1;s:31:\"edit_published_tribe_organizers\";b:1;s:23:\"delete_tribe_organizers\";b:1;s:30:\"delete_others_tribe_organizers\";b:1;s:31:\"delete_private_tribe_organizers\";b:1;s:33:\"delete_published_tribe_organizers\";b:1;s:24:\"publish_tribe_organizers\";b:1;s:31:\"read_private_aggregator-records\";b:1;s:23:\"edit_aggregator-records\";b:1;s:30:\"edit_others_aggregator-records\";b:1;s:31:\"edit_private_aggregator-records\";b:1;s:33:\"edit_published_aggregator-records\";b:1;s:25:\"delete_aggregator-records\";b:1;s:32:\"delete_others_aggregator-records\";b:1;s:33:\"delete_private_aggregator-records\";b:1;s:35:\"delete_published_aggregator-records\";b:1;s:26:\"publish_aggregator-records\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:30:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:17:\"edit_tribe_events\";b:1;s:27:\"edit_published_tribe_events\";b:1;s:19:\"delete_tribe_events\";b:1;s:29:\"delete_published_tribe_events\";b:1;s:20:\"publish_tribe_events\";b:1;s:17:\"edit_tribe_venues\";b:1;s:27:\"edit_published_tribe_venues\";b:1;s:19:\"delete_tribe_venues\";b:1;s:29:\"delete_published_tribe_venues\";b:1;s:20:\"publish_tribe_venues\";b:1;s:21:\"edit_tribe_organizers\";b:1;s:31:\"edit_published_tribe_organizers\";b:1;s:23:\"delete_tribe_organizers\";b:1;s:33:\"delete_published_tribe_organizers\";b:1;s:24:\"publish_tribe_organizers\";b:1;s:23:\"edit_aggregator-records\";b:1;s:33:\"edit_published_aggregator-records\";b:1;s:25:\"delete_aggregator-records\";b:1;s:35:\"delete_published_aggregator-records\";b:1;s:26:\"publish_aggregator-records\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:13:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:17:\"edit_tribe_events\";b:1;s:19:\"delete_tribe_events\";b:1;s:17:\"edit_tribe_venues\";b:1;s:19:\"delete_tribe_venues\";b:1;s:21:\"edit_tribe_organizers\";b:1;s:23:\"delete_tribe_organizers\";b:1;s:23:\"edit_aggregator-records\";b:1;s:25:\"delete_aggregator-records\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}}','yes'),(101,'fresh_site','1','yes'),(102,'user_count','1','no'),(103,'widget_block','a:6:{i:2;a:1:{s:7:\"content\";s:19:\"<!-- wp:search /-->\";}i:3;a:1:{s:7:\"content\";s:154:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Posts</h2><!-- /wp:heading --><!-- wp:latest-posts /--></div><!-- /wp:group -->\";}i:4;a:1:{s:7:\"content\";s:227:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Comments</h2><!-- /wp:heading --><!-- wp:latest-comments {\"displayAvatar\":false,\"displayDate\":false,\"displayExcerpt\":false} /--></div><!-- /wp:group -->\";}i:5;a:1:{s:7:\"content\";s:146:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Archives</h2><!-- /wp:heading --><!-- wp:archives /--></div><!-- /wp:group -->\";}i:6;a:1:{s:7:\"content\";s:150:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Categories</h2><!-- /wp:heading --><!-- wp:categories /--></div><!-- /wp:group -->\";}s:12:\"_multiwidget\";i:1;}','yes'),(104,'sidebars_widgets','a:4:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:3:{i:0;s:7:\"block-2\";i:1;s:7:\"block-3\";i:2;s:7:\"block-4\";}s:9:\"sidebar-2\";a:2:{i:0;s:7:\"block-5\";i:1;s:7:\"block-6\";}s:13:\"array_version\";i:3;}','yes'),(105,'cron','a:5:{i:1668000022;a:3:{s:30:\"tribe_schedule_transient_purge\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"tribe_daily_cron\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:32:\"recovery_mode_clean_expired_keys\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1668000023;a:6:{s:21:\"tribe-recurrence-cron\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:18:\"wp_https_detection\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1668086422;a:1:{s:24:\"tribe_common_log_cleanup\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1668086423;a:1:{s:30:\"wp_site_health_scheduled_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}s:7:\"version\";i:2;}','yes'),(106,'tribe_last_updated_option','1668000023.4868','yes'),(107,'nonce_key','vwR;vBa$T6#I8m6`ou|Y*>45z^m<C]T>Dw*MEV]hJ/}_#N!>V+a1rVjAcb8s{kZB','no'),(108,'nonce_salt','IBT`:b4W6gk8D-kGoN^Z*9 QNUa*rX;VWF_@}i;t!Lst_zv%kY{;|yKMGi>%F0#[','no'),(109,'tec_custom_tables_v1_provisional_post_base_provisional_id','10000000','yes'),(110,'tribe_events_calendar_options','a:10:{s:8:\"did_init\";b:1;s:19:\"tribeEventsTemplate\";s:0:\"\";s:16:\"tribeEnableViews\";a:3:{i:0;s:4:\"list\";i:1;s:5:\"month\";i:2;s:3:\"day\";}s:10:\"viewOption\";s:4:\"list\";s:14:\"schema-version\";s:7:\"6.0.3.1\";s:21:\"previous_ecp_versions\";a:1:{i:0;s:1:\"0\";}s:18:\"latest_ecp_version\";s:7:\"6.0.3.1\";s:18:\"dateWithYearFormat\";s:6:\"F j, Y\";s:24:\"recurrenceMaxMonthsAfter\";i:24;s:22:\"google_maps_js_api_key\";s:39:\"AIzaSyDNsicAsP6-VuGtAb1O9riI3oc_NOb7IOU\";}','yes'),(113,'tribe_last_save_post','1668000023.4874','yes'),(114,'widget_pages','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(115,'widget_calendar','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(116,'widget_archives','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(117,'widget_media_audio','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(118,'widget_media_image','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(119,'widget_media_gallery','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(120,'widget_media_video','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(121,'widget_meta','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(122,'widget_search','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(123,'widget_recent-posts','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(124,'widget_recent-comments','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(125,'widget_tag_cloud','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(126,'widget_nav_menu','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(127,'widget_custom_html','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(128,'widget_tribe-widget-events-list','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(129,'widget_tribe-widget-event-countdown','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(130,'widget_tribe-widget-featured-venue','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(131,'widget_tribe-widget-events-month','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(132,'widget_tribe-widget-events-week','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(133,'tec_ct1_series_relationship_table_schema_version','1.0.0','yes'),(134,'tec_ct1_events_table_schema_version','1.0.0','yes'),(135,'tec_ct1_occurrences_table_schema_version','1.0.0','yes'),(136,'tec_ct1_events_field_schema_version','1.0.1','yes');
/*!40000 ALTER TABLE `test_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_postmeta`
--

DROP TABLE IF EXISTS `test_postmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_postmeta`
--

LOCK TABLES `test_postmeta` WRITE;
/*!40000 ALTER TABLE `test_postmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_postmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_posts`
--

DROP TABLE IF EXISTS `test_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext NOT NULL,
  `post_title` text NOT NULL,
  `post_excerpt` text NOT NULL,
  `post_status` varchar(20) NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) NOT NULL DEFAULT 'open',
  `post_password` varchar(255) NOT NULL DEFAULT '',
  `post_name` varchar(200) NOT NULL DEFAULT '',
  `to_ping` text NOT NULL,
  `pinged` text NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `guid` varchar(255) NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `post_type` varchar(20) NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_posts`
--

LOCK TABLES `test_posts` WRITE;
/*!40000 ALTER TABLE `test_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_tec_events`
--

DROP TABLE IF EXISTS `test_tec_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_tec_events` (
  `event_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `timezone` varchar(30) NOT NULL DEFAULT 'UTC',
  `start_date_utc` datetime NOT NULL,
  `end_date_utc` datetime DEFAULT NULL,
  `duration` mediumint(30) DEFAULT 7200,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `hash` varchar(40) NOT NULL,
  `rset` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `post_id` (`post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_tec_events`
--

LOCK TABLES `test_tec_events` WRITE;
/*!40000 ALTER TABLE `test_tec_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_tec_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_tec_occurrences`
--

DROP TABLE IF EXISTS `test_tec_occurrences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_tec_occurrences` (
  `occurrence_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `post_id` bigint(20) unsigned NOT NULL,
  `start_date` datetime NOT NULL,
  `start_date_utc` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `end_date_utc` datetime NOT NULL,
  `duration` mediumint(30) DEFAULT 7200,
  `hash` varchar(40) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `has_recurrence` tinyint(1) DEFAULT 0,
  `sequence` bigint(20) unsigned DEFAULT 0,
  `is_rdate` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`occurrence_id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `test_tec_occurrences_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_10` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_11` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_12` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_13` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_14` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_15` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_16` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_17` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_18` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_19` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_20` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_21` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_22` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_23` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_24` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_25` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_26` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_27` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_28` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_29` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_30` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_31` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_32` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_33` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_34` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_35` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_36` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_37` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_38` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_39` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_4` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_40` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_41` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_42` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_43` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_44` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_45` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_46` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_47` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_48` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_49` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_5` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_50` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_51` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_52` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_6` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_7` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_8` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `test_tec_occurrences_ibfk_9` FOREIGN KEY (`event_id`) REFERENCES `test_tec_events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=689 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_tec_occurrences`
--

LOCK TABLES `test_tec_occurrences` WRITE;
/*!40000 ALTER TABLE `test_tec_occurrences` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_tec_occurrences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_tec_series_relationships`
--

DROP TABLE IF EXISTS `test_tec_series_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_tec_series_relationships` (
  `relationship_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `series_post_id` bigint(20) unsigned NOT NULL,
  `event_id` bigint(20) unsigned NOT NULL,
  `event_post_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`relationship_id`),
  KEY `series_post_id` (`series_post_id`),
  KEY `event_post_id` (`event_post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_tec_series_relationships`
--

LOCK TABLES `test_tec_series_relationships` WRITE;
/*!40000 ALTER TABLE `test_tec_series_relationships` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_tec_series_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_term_relationships`
--

DROP TABLE IF EXISTS `test_term_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_term_relationships`
--

LOCK TABLES `test_term_relationships` WRITE;
/*!40000 ALTER TABLE `test_term_relationships` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_term_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_term_taxonomy`
--

DROP TABLE IF EXISTS `test_term_taxonomy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `taxonomy` varchar(32) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_term_taxonomy`
--

LOCK TABLES `test_term_taxonomy` WRITE;
/*!40000 ALTER TABLE `test_term_taxonomy` DISABLE KEYS */;
INSERT INTO `test_term_taxonomy` VALUES (1,1,'category','',0,0);
/*!40000 ALTER TABLE `test_term_taxonomy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_termmeta`
--

DROP TABLE IF EXISTS `test_termmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_termmeta`
--

LOCK TABLES `test_termmeta` WRITE;
/*!40000 ALTER TABLE `test_termmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_termmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_terms`
--

DROP TABLE IF EXISTS `test_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `slug` varchar(200) NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_terms`
--

LOCK TABLES `test_terms` WRITE;
/*!40000 ALTER TABLE `test_terms` DISABLE KEYS */;
INSERT INTO `test_terms` VALUES (1,'Uncategorized','uncategorized',0);
/*!40000 ALTER TABLE `test_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_usermeta`
--

DROP TABLE IF EXISTS `test_usermeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_usermeta`
--

LOCK TABLES `test_usermeta` WRITE;
/*!40000 ALTER TABLE `test_usermeta` DISABLE KEYS */;
INSERT INTO `test_usermeta` VALUES (1,1,'nickname','admin'),(2,1,'first_name',''),(3,1,'last_name',''),(4,1,'description',''),(5,1,'rich_editing','true'),(6,1,'syntax_highlighting','true'),(7,1,'comment_shortcuts','false'),(8,1,'admin_color','fresh'),(9,1,'use_ssl','0'),(10,1,'show_admin_bar_front','true'),(11,1,'locale',''),(12,1,'test_capabilities','a:1:{s:13:\"administrator\";b:1;}'),(13,1,'test_user_level','10'),(14,1,'dismissed_wp_pointers',''),(15,1,'show_welcome_panel','1');
/*!40000 ALTER TABLE `test_usermeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_users`
--

DROP TABLE IF EXISTS `test_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) NOT NULL DEFAULT '',
  `user_pass` varchar(255) NOT NULL DEFAULT '',
  `user_nicename` varchar(50) NOT NULL DEFAULT '',
  `user_email` varchar(100) NOT NULL DEFAULT '',
  `user_url` varchar(100) NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT 0,
  `display_name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_users`
--

LOCK TABLES `test_users` WRITE;
/*!40000 ALTER TABLE `test_users` DISABLE KEYS */;
INSERT INTO `test_users` VALUES (1,'admin','$P$B15.YjKmUo3SIJqjTb4hnqxlNGVfrW1','admin','admin@wordpress.test','http://wordpress.test','2022-11-09 13:20:21','',0,'admin');
/*!40000 ALTER TABLE `test_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_actionscheduler_actions`
--

DROP TABLE IF EXISTS `wp_actionscheduler_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_actionscheduler_actions` (
  `action_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hook` varchar(191) NOT NULL,
  `status` varchar(20) NOT NULL,
  `scheduled_date_gmt` datetime DEFAULT '0000-00-00 00:00:00',
  `scheduled_date_local` datetime DEFAULT '0000-00-00 00:00:00',
  `args` varchar(191) DEFAULT NULL,
  `schedule` longtext DEFAULT NULL,
  `group_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `last_attempt_gmt` datetime DEFAULT '0000-00-00 00:00:00',
  `last_attempt_local` datetime DEFAULT '0000-00-00 00:00:00',
  `claim_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `extended_args` varchar(8000) DEFAULT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT 10,
  PRIMARY KEY (`action_id`),
  KEY `hook` (`hook`),
  KEY `status` (`status`),
  KEY `scheduled_date_gmt` (`scheduled_date_gmt`),
  KEY `args` (`args`),
  KEY `group_id` (`group_id`),
  KEY `last_attempt_gmt` (`last_attempt_gmt`),
  KEY `claim_id_status_scheduled_date_gmt` (`claim_id`,`status`,`scheduled_date_gmt`),
  KEY `hook_status_scheduled_date_gmt` (`hook`(163),`status`,`scheduled_date_gmt`),
  KEY `status_scheduled_date_gmt` (`status`,`scheduled_date_gmt`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_actionscheduler_actions`
--

LOCK TABLES `wp_actionscheduler_actions` WRITE;
/*!40000 ALTER TABLE `wp_actionscheduler_actions` DISABLE KEYS */;
INSERT INTO `wp_actionscheduler_actions` VALUES (2,'action_scheduler/migration_hook','complete','2022-11-08 10:50:41','2022-11-08 02:50:41','[]','O:30:\"ActionScheduler_SimpleSchedule\":2:{s:22:\"\0*\0scheduled_timestamp\";i:1667904641;s:41:\"\0ActionScheduler_SimpleSchedule\0timestamp\";i:1667904641;}',1,1,'2022-11-08 12:44:24','2022-11-08 04:44:24',0,NULL,10),(3,'action_scheduler/migration_hook','complete','2022-11-10 10:17:04','2022-11-10 02:17:04','[]','O:30:\"ActionScheduler_SimpleSchedule\":2:{s:22:\"\0*\0scheduled_timestamp\";i:1668075424;s:41:\"\0ActionScheduler_SimpleSchedule\0timestamp\";i:1668075424;}',1,1,'2022-11-10 10:18:50','2022-11-10 02:18:50',0,NULL,10);
/*!40000 ALTER TABLE `wp_actionscheduler_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_actionscheduler_claims`
--

DROP TABLE IF EXISTS `wp_actionscheduler_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_actionscheduler_claims` (
  `claim_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date_created_gmt` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`claim_id`),
  KEY `date_created_gmt` (`date_created_gmt`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_actionscheduler_claims`
--

LOCK TABLES `wp_actionscheduler_claims` WRITE;
/*!40000 ALTER TABLE `wp_actionscheduler_claims` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_actionscheduler_claims` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_actionscheduler_groups`
--

DROP TABLE IF EXISTS `wp_actionscheduler_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_actionscheduler_groups` (
  `group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`group_id`),
  KEY `slug` (`slug`(191))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_actionscheduler_groups`
--

LOCK TABLES `wp_actionscheduler_groups` WRITE;
/*!40000 ALTER TABLE `wp_actionscheduler_groups` DISABLE KEYS */;
INSERT INTO `wp_actionscheduler_groups` VALUES (1,'action-scheduler-migration');
/*!40000 ALTER TABLE `wp_actionscheduler_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_actionscheduler_logs`
--

DROP TABLE IF EXISTS `wp_actionscheduler_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_actionscheduler_logs` (
  `log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `action_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `log_date_gmt` datetime DEFAULT '0000-00-00 00:00:00',
  `log_date_local` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`log_id`),
  KEY `action_id` (`action_id`),
  KEY `log_date_gmt` (`log_date_gmt`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_actionscheduler_logs`
--

LOCK TABLES `wp_actionscheduler_logs` WRITE;
/*!40000 ALTER TABLE `wp_actionscheduler_logs` DISABLE KEYS */;
INSERT INTO `wp_actionscheduler_logs` VALUES (1,2,'action created','2022-11-08 10:49:41','2022-11-08 02:49:41'),(2,2,'action started via WP Cron','2022-11-08 12:44:24','2022-11-08 04:44:24'),(3,2,'action complete via WP Cron','2022-11-08 12:44:24','2022-11-08 04:44:24'),(4,3,'action created','2022-11-10 10:16:04','2022-11-10 02:16:04'),(5,3,'action started via Async Request','2022-11-10 10:18:50','2022-11-10 02:18:50'),(6,3,'action complete via Async Request','2022-11-10 10:18:50','2022-11-10 02:18:50');
/*!40000 ALTER TABLE `wp_actionscheduler_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_commentmeta`
--

DROP TABLE IF EXISTS `wp_commentmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_commentmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_comments`
--

DROP TABLE IF EXISTS `wp_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT 0,
  `comment_author` tinytext NOT NULL,
  `comment_author_email` varchar(100) NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT 0,
  `comment_approved` varchar(20) NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) NOT NULL DEFAULT '',
  `comment_type` varchar(20) NOT NULL DEFAULT 'comment',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_comments`
--

LOCK TABLES `wp_comments` WRITE;
/*!40000 ALTER TABLE `wp_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_edd_customermeta`
--

DROP TABLE IF EXISTS `wp_edd_customermeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_edd_customermeta` (
  `meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `customer_id` (`customer_id`),
  KEY `meta_key` (`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_edd_customermeta`
--

LOCK TABLES `wp_edd_customermeta` WRITE;
/*!40000 ALTER TABLE `wp_edd_customermeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_edd_customermeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_edd_customers`
--

DROP TABLE IF EXISTS `wp_edd_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_edd_customers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `name` mediumtext NOT NULL,
  `purchase_value` mediumtext NOT NULL,
  `purchase_count` bigint(20) NOT NULL,
  `payment_ids` longtext NOT NULL,
  `notes` longtext NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_edd_customers`
--

LOCK TABLES `wp_edd_customers` WRITE;
/*!40000 ALTER TABLE `wp_edd_customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_edd_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_links`
--

DROP TABLE IF EXISTS `wp_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) NOT NULL DEFAULT '',
  `link_name` varchar(255) NOT NULL DEFAULT '',
  `link_image` varchar(255) NOT NULL DEFAULT '',
  `link_target` varchar(25) NOT NULL DEFAULT '',
  `link_description` varchar(255) NOT NULL DEFAULT '',
  `link_visible` varchar(20) NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) unsigned NOT NULL DEFAULT 1,
  `link_rating` int(11) NOT NULL DEFAULT 0,
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) NOT NULL DEFAULT '',
  `link_notes` mediumtext NOT NULL,
  `link_rss` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_links`
--

LOCK TABLES `wp_links` WRITE;
/*!40000 ALTER TABLE `wp_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_options`
--

DROP TABLE IF EXISTS `wp_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) NOT NULL DEFAULT '',
  `option_value` longtext NOT NULL,
  `autoload` varchar(20) NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB AUTO_INCREMENT=348 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_options`
--

LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
INSERT INTO `wp_options` VALUES (1,'siteurl','http://example.com','yes'),(2,'home','http://example.com','yes'),(3,'blogname','TEC REST v1 Tests','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@wordpress.test','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','1','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%postname%/','yes'),(29,'rewrite_rules','a:390:{s:32:\"(?:events)/map/(?:page)/(\\d+)/?$\";s:67:\"index.php?post_type=tribe_events&eventDisplay=map&paged=$matches[1]\";s:64:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/map/(?:page)/(\\d+)/?$\";s:96:\"index.php?tribe_events_cat=$matches[1]&post_type=tribe_events&eventDisplay=map&paged=$matches[2]\";s:48:\"(?:events)/(?:tag)/([^/]+)/map/(?:page)/(\\d+)/?$\";s:83:\"index.php?tag=$matches[1]&post_type=tribe_events&eventDisplay=map&paged=$matches[2]\";s:14:\"(?:events)/map\";s:49:\"index.php?post_type=tribe_events&eventDisplay=map\";s:49:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/map/?$\";s:78:\"index.php?tribe_events_cat=$matches[1]&post_type=tribe_events&eventDisplay=map\";s:33:\"(?:events)/(?:tag)/([^/]+)/map/?$\";s:65:\"index.php?tag=$matches[1]&post_type=tribe_events&eventDisplay=map\";s:10:\"events/map\";s:49:\"index.php?post_type=tribe_events&eventDisplay=map\";s:41:\"events/category/(?:[^/]+/)*([^/]+)/map/?$\";s:78:\"index.php?tribe_events_cat=$matches[2]&post_type=tribe_events&eventDisplay=map\";s:25:\"events/tag/([^/]+)/map/?$\";s:65:\"index.php?tag=$matches[2]&post_type=tribe_events&eventDisplay=map\";s:41:\"(?:events)/(?:map)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:71:\"index.php?post_type=tribe_events&eventDisplay=map&eventDate=$matches[1]\";s:56:\"(?:events)/(?:map)/(\\d{4}-\\d{2}-\\d{2})/(?:page)/(\\d+)/?$\";s:89:\"index.php?post_type=tribe_events&eventDisplay=map&eventDate=$matches[1]&paged=$matches[2]\";s:53:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:map)/?$\";s:78:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=map\";s:66:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:map)/(?:featured)/?$\";s:89:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=map&featured=1\";s:37:\"(?:events)/(?:tag)/([^/]+)/(?:map)/?$\";s:65:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=map\";s:50:\"(?:events)/(?:tag)/([^/]+)/(?:map)/(?:featured)/?$\";s:76:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=map&featured=1\";s:22:\"(?:events)/(?:week)/?$\";s:50:\"index.php?post_type=tribe_events&eventDisplay=week\";s:35:\"(?:events)/(?:week)/(?:featured)/?$\";s:61:\"index.php?post_type=tribe_events&eventDisplay=week&featured=1\";s:42:\"(?:events)/(?:week)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:72:\"index.php?post_type=tribe_events&eventDisplay=week&eventDate=$matches[1]\";s:55:\"(?:events)/(?:week)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:83:\"index.php?post_type=tribe_events&eventDisplay=week&eventDate=$matches[1]&featured=1\";s:43:\"(?:events)/(?:week)/(\\d{2})/(?:featured)/?$\";s:83:\"index.php?post_type=tribe_events&eventDisplay=week&eventDate=$matches[1]&featured=1\";s:54:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:week)/?$\";s:79:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=week\";s:67:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:week)/(?:featured)/?$\";s:90:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=week&featured=1\";s:74:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:week)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:101:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=week&eventDate=$matches[2]\";s:87:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:week)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:112:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=week&eventDate=$matches[2]&featured=1\";s:38:\"(?:events)/(?:tag)/([^/]+)/(?:week)/?$\";s:66:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=week\";s:51:\"(?:events)/(?:tag)/([^/]+)/(?:week)/(?:featured)/?$\";s:77:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=week&featured=1\";s:58:\"(?:events)/(?:tag)/([^/]+)/(?:week)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:88:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=week&eventDate=$matches[2]\";s:71:\"(?:events)/(?:tag)/([^/]+)/(?:week)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:99:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=week&eventDate=$matches[2]&featured=1\";s:34:\"(?:events)/(?:week)/(?:virtual)/?$\";s:60:\"index.php?post_type=tribe_events&eventDisplay=week&virtual=1\";s:54:\"(?:events)/(?:week)/(\\d{4}-\\d{2}-\\d{2})/(?:virtual)/?$\";s:82:\"index.php?post_type=tribe_events&eventDisplay=week&eventDate=$matches[1]&virtual=1\";s:42:\"(?:events)/(?:week)/(\\d{2})/(?:virtual)/?$\";s:82:\"index.php?post_type=tribe_events&eventDisplay=week&eventDate=$matches[1]&virtual=1\";s:66:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:week)/(?:virtual)/?$\";s:89:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=week&virtual=1\";s:86:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:week)/(\\d{4}-\\d{2}-\\d{2})/(?:virtual)/?$\";s:111:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=week&eventDate=$matches[2]&virtual=1\";s:50:\"(?:events)/(?:tag)/([^/]+)/(?:week)/(?:virtual)/?$\";s:76:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=week&virtual=1\";s:70:\"(?:events)/(?:tag)/([^/]+)/(?:week)/(\\d{4}-\\d{2}-\\d{2})/(?:virtual)/?$\";s:98:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=week&eventDate=$matches[2]&virtual=1\";s:60:\"(?:events)/(?:organizers)/(?:category)/(?:[^/]+/)*([^/]+)/?$\";s:93:\"index.php?eventDisplay=organizer&post_type=tribe_organizer&tec_organizer_category=$matches[1]\";s:75:\"(?:events)/(?:organizers)/(?:category)/(?:[^/]+/)*([^/]+)/(?:page)/(\\d+)/?$\";s:111:\"index.php?eventDisplay=organizer&post_type=tribe_organizer&tec_organizer_category=$matches[1]&paged=$matches[2]\";s:56:\"(?:events)/(?:venues)/(?:category)/(?:[^/]+/)*([^/]+)/?$\";s:81:\"index.php?eventDisplay=venue&post_type=tribe_venue&tec_venue_category=$matches[1]\";s:71:\"(?:events)/(?:venues)/(?:category)/(?:[^/]+/)*([^/]+)/(?:page)/(\\d+)/?$\";s:99:\"index.php?eventDisplay=venue&post_type=tribe_venue&tec_venue_category=$matches[1]&paged=$matches[2]\";s:40:\"(?:events)/(?:summary)/(?:page)/(\\d+)/?$\";s:71:\"index.php?post_type=tribe_events&eventDisplay=summary&paged=$matches[1]\";s:53:\"(?:events)/(?:summary)/(?:featured)/(?:page)/(\\d+)/?$\";s:82:\"index.php?post_type=tribe_events&eventDisplay=summary&featured=1&paged=$matches[1]\";s:25:\"(?:events)/(?:summary)/?$\";s:53:\"index.php?post_type=tribe_events&eventDisplay=summary\";s:38:\"(?:events)/(?:summary)/(?:featured)/?$\";s:64:\"index.php?post_type=tribe_events&eventDisplay=summary&featured=1\";s:45:\"(?:events)/(?:summary)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:75:\"index.php?post_type=tribe_events&eventDisplay=summary&eventDate=$matches[1]\";s:58:\"(?:events)/(?:summary)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:86:\"index.php?post_type=tribe_events&eventDisplay=summary&eventDate=$matches[1]&featured=1\";s:72:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:summary)/(?:page)/(\\d+)/?$\";s:100:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=summary&paged=$matches[2]\";s:85:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:summary)/(?:featured)/(?:page)/(\\d+)/?$\";s:111:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=summary&featured=1&paged=$matches[2]\";s:70:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:summary)/(?:featured)/?$\";s:93:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=summary&featured=1\";s:57:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:summary)/?$\";s:82:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=summary\";s:56:\"(?:events)/(?:tag)/([^/]+)/(?:summary)/(?:page)/(\\d+)/?$\";s:87:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=summary&paged=$matches[2]\";s:69:\"(?:events)/(?:tag)/([^/]+)/(?:summary)/(?:featured)/(?:page)/(\\d+)/?$\";s:98:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=summary&featured=1&paged=$matches[2]\";s:54:\"(?:events)/(?:tag)/([^/]+)/(?:summary)/(?:featured)/?$\";s:80:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=summary&featured=1\";s:41:\"(?:events)/(?:tag)/([^/]+)/(?:summary)/?$\";s:69:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=summary\";s:40:\"(?:event)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:56:\"index.php?tribe_events=$matches[1]&eventDate=$matches[2]\";s:65:\"(?:event)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/(feed|rdf|rss|rss2|atom)/?$\";s:73:\"index.php?tribe_events=$matches[1]&eventDate=$matches[2]&feed=$matches[3]\";s:71:\"(?:event)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/(\\d+)/(feed|rdf|rss|rss2|atom)/?$\";s:99:\"index.php?tribe_events=$matches[1]&eventDate=$matches[2]&eventSequence=$matches[3]&feed=$matches[4]\";s:46:\"(?:event)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/(\\d+)/?$\";s:82:\"index.php?tribe_events=$matches[1]&eventDate=$matches[2]&eventSequence=$matches[3]\";s:46:\"(?:event)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/embed/?$\";s:64:\"index.php?tribe_events=$matches[1]&eventDate=$matches[2]&embed=1\";s:43:\"(?:event)/([^/]+)/(?:all)/(?:page)/(\\d+)/?$\";s:115:\"index.php?tribe_events=$matches[1]&post_type=tribe_events&eventDisplay=all&tribe_recurrence_list=1&page=$matches[2]\";s:28:\"(?:event)/([^/]+)/(?:all)/?$\";s:98:\"index.php?tribe_events=$matches[1]&post_type=tribe_events&eventDisplay=all&tribe_recurrence_list=1\";s:45:\"(?:event)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/ical/?$\";s:63:\"index.php?tribe_events=$matches[1]&eventDate=$matches[2]&ical=1\";s:23:\"(?:events)/(?:photo)/?$\";s:51:\"index.php?post_type=tribe_events&eventDisplay=photo\";s:36:\"(?:events)/(?:photo)/(?:featured)/?$\";s:62:\"index.php?post_type=tribe_events&eventDisplay=photo&featured=1\";s:38:\"(?:events)/(?:photo)/(?:page)/(\\d+)/?$\";s:69:\"index.php?post_type=tribe_events&eventDisplay=photo&paged=$matches[1]\";s:43:\"(?:events)/(?:photo)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:73:\"index.php?post_type=tribe_events&eventDisplay=photo&eventDate=$matches[1]\";s:58:\"(?:events)/(?:photo)/(\\d{4}-\\d{2}-\\d{2})/(?:page)/(\\d+)/?$\";s:91:\"index.php?post_type=tribe_events&eventDisplay=photo&eventDate=$matches[1]&paged=$matches[2]\";s:56:\"(?:events)/(?:photo)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:84:\"index.php?post_type=tribe_events&eventDisplay=photo&eventDate=$matches[1]&featured=1\";s:71:\"(?:events)/(?:photo)/(\\d{4}-\\d{2}-\\d{2})/(?:page)/(\\d+)/(?:featured)/?$\";s:102:\"index.php?post_type=tribe_events&eventDisplay=photo&eventDate=$matches[1]&paged=$matches[2]&featured=1\";s:55:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:photo)/?$\";s:80:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=photo\";s:68:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:photo)/(?:featured)/?$\";s:91:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=photo&featured=1\";s:39:\"(?:events)/(?:tag)/([^/]+)/(?:photo)/?$\";s:67:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=photo\";s:52:\"(?:events)/(?:tag)/([^/]+)/(?:photo)/(?:featured)/?$\";s:78:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=photo&featured=1\";s:40:\"(?:events)/(?:virtual)/(?:page)/(\\d+)/?$\";s:78:\"index.php?post_type=tribe_events&virtual=1&eventDisplay=list&paged=$matches[1]\";s:50:\"(?:events)/(?:virtual)/(feed|rdf|rss|rss2|atom)/?$\";s:77:\"index.php?post_type=tribe_events&virtual=1&eventDisplay=list&feed=$matches[1]\";s:49:\"(?:events)/(?:list)/(?:virtual)/(?:page)/(\\d+)/?$\";s:78:\"index.php?post_type=tribe_events&eventDisplay=list&virtual=1&paged=$matches[1]\";s:34:\"(?:events)/(?:list)/(?:virtual)/?$\";s:60:\"index.php?post_type=tribe_events&eventDisplay=list&virtual=1\";s:72:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:virtual)/(?:page)/(\\d+)/?$\";s:107:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&virtual=1&eventDisplay=list&paged=$matches[2]\";s:81:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/(?:virtual)/(?:page)/(\\d+)/?$\";s:107:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&virtual=1&paged=$matches[2]\";s:66:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/(?:virtual)/?$\";s:89:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&virtual=1\";s:56:\"(?:events)/(?:tag)/([^/]+)/(?:virtual)/(?:page)/(\\d+)/?$\";s:94:\"index.php?post_type=tribe_events&tag=$matches[1]&virtual=1&eventDisplay=list&paged=$matches[2]\";s:65:\"(?:events)/(?:tag)/([^/]+)/(?:list)/(?:virtual)/(?:page)/(\\d+)/?$\";s:94:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&virtual=1&paged=$matches[2]\";s:50:\"(?:events)/(?:tag)/([^/]+)/(?:list)/(?:virtual)/?$\";s:76:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&virtual=1\";s:62:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:virtual)/feed/?$\";s:99:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&virtual=1&eventDisplay=list&feed=rss2\";s:46:\"(?:events)/(?:tag)/([^/]+)/(?:virtual)/feed/?$\";s:86:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&feed=rss2&virtual=1\";s:35:\"(?:events)/(?:month)/(?:virtual)/?$\";s:61:\"index.php?post_type=tribe_events&eventDisplay=month&virtual=1\";s:39:\"(?:events)/(\\d{4}-\\d{2})/(?:virtual)/?$\";s:83:\"index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]&virtual=1\";s:67:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:month)/(?:virtual)/?$\";s:90:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&virtual=1\";s:71:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2})/(?:virtual)/?$\";s:112:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&eventDate=$matches[2]&virtual=1\";s:51:\"(?:events)/(?:tag)/([^/]+)/(?:month)/(?:virtual)/?$\";s:77:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&virtual=1\";s:55:\"(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2})/(?:virtual)/?$\";s:99:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&eventDate=$matches[2]&virtual=1\";s:35:\"(?:events)/(?:today)/(?:virtual)/?$\";s:59:\"index.php?post_type=tribe_events&eventDisplay=day&virtual=1\";s:45:\"(?:events)/(\\d{4}-\\d{2}-\\d{2})/(?:virtual)/?$\";s:81:\"index.php?post_type=tribe_events&eventDisplay=day&eventDate=$matches[1]&virtual=1\";s:30:\"(?:events)/(?:virtual)/ical/?$\";s:49:\"index.php?post_type=tribe_events&ical=1&virtual=1\";s:38:\"(?:events)/(\\d{4}-\\d{2}-\\d{2})/ical/?$\";s:78:\"index.php?post_type=tribe_events&ical=1&eventDisplay=day&eventDate=$matches[1]\";s:50:\"(?:events)/(\\d{4}-\\d{2}-\\d{2})/ical/(?:virtual)/?$\";s:88:\"index.php?post_type=tribe_events&ical=1&eventDisplay=day&eventDate=$matches[1]&virtual=1\";s:67:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:today)/(?:virtual)/?$\";s:88:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&virtual=1\";s:85:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/(?:virtual)/?$\";s:110:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]&virtual=1\";s:77:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2}-\\d{2})/(?:virtual)/?$\";s:110:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]&virtual=1\";s:51:\"(?:events)/(?:tag)/([^/]+)/(?:today)/(?:virtual)/?$\";s:75:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&virtual=1\";s:69:\"(?:events)/(?:tag)/([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/(?:virtual)/?$\";s:97:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]&virtual=1\";s:61:\"(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/(?:virtual)/?$\";s:97:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]&virtual=1\";s:25:\"(?:events)/(?:virtual)/?$\";s:42:\"index.php?post_type=tribe_events&virtual=1\";s:57:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:virtual)/?$\";s:92:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&virtual=1&eventDisplay=default\";s:41:\"(?:events)/(?:tag)/([^/]+)/(?:virtual)/?$\";s:58:\"index.php?post_type=tribe_events&tag=$matches[1]&virtual=1\";s:62:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:virtual)/ical/?$\";s:78:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&virtual=1&ical=1\";s:87:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:virtual)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:88:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&virtual=1&feed=$matches[2]\";s:46:\"(?:events)/(?:tag)/([^/]+)/(?:virtual)/ical/?$\";s:65:\"index.php?post_type=tribe_events&tag=$matches[1]&virtual=1&ical=1\";s:71:\"(?:events)/(?:tag)/([^/]+)/(?:virtual)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:75:\"index.php?post_type=tribe_events&tag=$matches[1]&virtual=1&feed=$matches[2]\";s:35:\"(?:events)/(?:photo)/(?:virtual)/?$\";s:61:\"index.php?post_type=tribe_events&eventDisplay=photo&virtual=1\";s:55:\"(?:events)/(?:photo)/(\\d{4}-\\d{2}-\\d{2})/(?:virtual)/?$\";s:83:\"index.php?post_type=tribe_events&eventDisplay=photo&eventDate=$matches[1]&virtual=1\";s:67:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:photo)/(?:virtual)/?$\";s:90:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=photo&virtual=1\";s:51:\"(?:events)/(?:tag)/([^/]+)/(?:photo)/(?:virtual)/?$\";s:77:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=photo&virtual=1\";s:35:\"(?:venue)/([^/]+)/(?:page)/(\\d+)/?$\";s:70:\"index.php?eventDisplay=venue&tribe_venue=$matches[1]&paged=$matches[2]\";s:20:\"(?:venue)/([^/]+)/?$\";s:52:\"index.php?eventDisplay=venue&tribe_venue=$matches[1]\";s:39:\"(?:organizer)/([^/]+)/(?:page)/(\\d+)/?$\";s:78:\"index.php?eventDisplay=organizer&tribe_organizer=$matches[1]&paged=$matches[2]\";s:24:\"(?:organizer)/([^/]+)/?$\";s:60:\"index.php?eventDisplay=organizer&tribe_organizer=$matches[1]\";s:28:\"tribe/events/kitchen-sink/?$\";s:69:\"index.php?post_type=tribe_events&tribe_events_views_kitchen_sink=page\";s:93:\"tribe/events/kitchen-sink/(page|grid|typographical|elements|events-bar|navigation|manager)/?$\";s:76:\"index.php?post_type=tribe_events&tribe_events_views_kitchen_sink=$matches[1]\";s:28:\"event-aggregator/(insert)/?$\";s:53:\"index.php?tribe-aggregator=1&tribe-action=$matches[1]\";s:25:\"(?:event)/([^/]+)/ical/?$\";s:56:\"index.php?ical=1&name=$matches[1]&post_type=tribe_events\";s:28:\"(?:events)/(?:page)/(\\d+)/?$\";s:71:\"index.php?post_type=tribe_events&eventDisplay=default&paged=$matches[1]\";s:41:\"(?:events)/(?:featured)/(?:page)/(\\d+)/?$\";s:79:\"index.php?post_type=tribe_events&featured=1&eventDisplay=list&paged=$matches[1]\";s:38:\"(?:events)/(feed|rdf|rss|rss2|atom)/?$\";s:67:\"index.php?post_type=tribe_events&eventDisplay=list&feed=$matches[1]\";s:51:\"(?:events)/(?:featured)/(feed|rdf|rss|rss2|atom)/?$\";s:78:\"index.php?post_type=tribe_events&featured=1&eventDisplay=list&feed=$matches[1]\";s:23:\"(?:events)/(?:month)/?$\";s:51:\"index.php?post_type=tribe_events&eventDisplay=month\";s:36:\"(?:events)/(?:month)/(?:featured)/?$\";s:62:\"index.php?post_type=tribe_events&eventDisplay=month&featured=1\";s:37:\"(?:events)/(?:month)/(\\d{4}-\\d{2})/?$\";s:73:\"index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]\";s:37:\"(?:events)/(?:list)/(?:page)/(\\d+)/?$\";s:68:\"index.php?post_type=tribe_events&eventDisplay=list&paged=$matches[1]\";s:50:\"(?:events)/(?:list)/(?:featured)/(?:page)/(\\d+)/?$\";s:79:\"index.php?post_type=tribe_events&eventDisplay=list&featured=1&paged=$matches[1]\";s:22:\"(?:events)/(?:list)/?$\";s:50:\"index.php?post_type=tribe_events&eventDisplay=list\";s:35:\"(?:events)/(?:list)/(?:featured)/?$\";s:61:\"index.php?post_type=tribe_events&eventDisplay=list&featured=1\";s:23:\"(?:events)/(?:today)/?$\";s:49:\"index.php?post_type=tribe_events&eventDisplay=day\";s:36:\"(?:events)/(?:today)/(?:featured)/?$\";s:60:\"index.php?post_type=tribe_events&eventDisplay=day&featured=1\";s:27:\"(?:events)/(\\d{4}-\\d{2})/?$\";s:73:\"index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]\";s:40:\"(?:events)/(\\d{4}-\\d{2})/(?:featured)/?$\";s:84:\"index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]&featured=1\";s:33:\"(?:events)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:71:\"index.php?post_type=tribe_events&eventDisplay=day&eventDate=$matches[1]\";s:46:\"(?:events)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:82:\"index.php?post_type=tribe_events&eventDisplay=day&eventDate=$matches[1]&featured=1\";s:26:\"(?:events)/(?:featured)/?$\";s:43:\"index.php?post_type=tribe_events&featured=1\";s:13:\"(?:events)/?$\";s:53:\"index.php?post_type=tribe_events&eventDisplay=default\";s:18:\"(?:events)/ical/?$\";s:39:\"index.php?post_type=tribe_events&ical=1\";s:31:\"(?:events)/(?:featured)/ical/?$\";s:50:\"index.php?post_type=tribe_events&ical=1&featured=1\";s:51:\"(?:events)/(\\d{4}-\\d{2}-\\d{2})/ical/(?:featured)/?$\";s:89:\"index.php?post_type=tribe_events&ical=1&eventDisplay=day&eventDate=$matches[1]&featured=1\";s:60:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:page)/(\\d+)/?$\";s:97:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&paged=$matches[2]\";s:73:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:featured)/(?:page)/(\\d+)/?$\";s:108:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&eventDisplay=list&paged=$matches[2]\";s:55:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:month)/?$\";s:80:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month\";s:68:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:month)/(?:featured)/?$\";s:91:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&featured=1\";s:69:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/(?:page)/(\\d+)/?$\";s:97:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&paged=$matches[2]\";s:82:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/(?:featured)/(?:page)/(\\d+)/?$\";s:108:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&featured=1&paged=$matches[2]\";s:54:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/?$\";s:79:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list\";s:67:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:list)/(?:featured)/?$\";s:90:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&featured=1\";s:55:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:today)/?$\";s:78:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day\";s:68:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:today)/(?:featured)/?$\";s:89:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&featured=1\";s:73:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:100:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]\";s:86:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:111:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]&featured=1\";s:59:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2})/?$\";s:102:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&eventDate=$matches[2]\";s:72:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2})/(?:featured)/?$\";s:113:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&eventDate=$matches[2]&featured=1\";s:65:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:100:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]\";s:78:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:111:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=day&eventDate=$matches[2]&featured=1\";s:50:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/feed/?$\";s:89:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=list&feed=rss2\";s:63:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:featured)/feed/?$\";s:100:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&eventDisplay=list&feed=rss2\";s:50:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/ical/?$\";s:68:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&ical=1\";s:63:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:featured)/ical/?$\";s:79:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&ical=1\";s:75:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:78:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&feed=$matches[2]\";s:88:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:featured)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:89:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&feed=$matches[2]\";s:58:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:featured)/?$\";s:93:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&featured=1&eventDisplay=default\";s:45:\"(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/?$\";s:82:\"index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=default\";s:44:\"(?:events)/(?:tag)/([^/]+)/(?:page)/(\\d+)/?$\";s:84:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&paged=$matches[2]\";s:57:\"(?:events)/(?:tag)/([^/]+)/(?:featured)/(?:page)/(\\d+)/?$\";s:95:\"index.php?post_type=tribe_events&tag=$matches[1]&featured=1&eventDisplay=list&paged=$matches[2]\";s:39:\"(?:events)/(?:tag)/([^/]+)/(?:month)/?$\";s:67:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month\";s:52:\"(?:events)/(?:tag)/([^/]+)/(?:month)/(?:featured)/?$\";s:78:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&featured=1\";s:53:\"(?:events)/(?:tag)/([^/]+)/(?:list)/(?:page)/(\\d+)/?$\";s:84:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&paged=$matches[2]\";s:66:\"(?:events)/(?:tag)/([^/]+)/(?:list)/(?:featured)/(?:page)/(\\d+)/?$\";s:95:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&featured=1&paged=$matches[2]\";s:38:\"(?:events)/(?:tag)/([^/]+)/(?:list)/?$\";s:66:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list\";s:51:\"(?:events)/(?:tag)/([^/]+)/(?:list)/(?:featured)/?$\";s:77:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&featured=1\";s:39:\"(?:events)/(?:tag)/([^/]+)/(?:today)/?$\";s:65:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day\";s:52:\"(?:events)/(?:tag)/([^/]+)/(?:today)/(?:featured)/?$\";s:76:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&featured=1\";s:57:\"(?:events)/(?:tag)/([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:87:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]\";s:70:\"(?:events)/(?:tag)/([^/]+)/(?:day)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:98:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]&featured=1\";s:43:\"(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2})/?$\";s:89:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&eventDate=$matches[2]\";s:56:\"(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2})/(?:featured)/?$\";s:100:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&eventDate=$matches[2]&featured=1\";s:49:\"(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/?$\";s:87:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]\";s:62:\"(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2}-\\d{2})/(?:featured)/?$\";s:98:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=day&eventDate=$matches[2]&featured=1\";s:34:\"(?:events)/(?:tag)/([^/]+)/feed/?$\";s:76:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&feed=rss2\";s:47:\"(?:events)/(?:tag)/([^/]+)/(?:featured)/feed/?$\";s:87:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=list&feed=rss2&featured=1\";s:34:\"(?:events)/(?:tag)/([^/]+)/ical/?$\";s:55:\"index.php?post_type=tribe_events&tag=$matches[1]&ical=1\";s:47:\"(?:events)/(?:tag)/([^/]+)/(?:featured)/ical/?$\";s:66:\"index.php?post_type=tribe_events&tag=$matches[1]&featured=1&ical=1\";s:59:\"(?:events)/(?:tag)/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:65:\"index.php?post_type=tribe_events&tag=$matches[1]&feed=$matches[2]\";s:72:\"(?:events)/(?:tag)/([^/]+)/(?:featured)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:76:\"index.php?post_type=tribe_events&tag=$matches[1]&featured=1&feed=$matches[2]\";s:42:\"(?:events)/(?:tag)/([^/]+)/(?:featured)/?$\";s:59:\"index.php?post_type=tribe_events&tag=$matches[1]&featured=1\";s:29:\"(?:events)/(?:tag)/([^/]+)/?$\";s:69:\"index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=default\";s:11:\"^wp-json/?$\";s:22:\"index.php?rest_route=/\";s:14:\"^wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:21:\"^index.php/wp-json/?$\";s:22:\"index.php?rest_route=/\";s:24:\"^index.php/wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:17:\"^wp-sitemap\\.xml$\";s:23:\"index.php?sitemap=index\";s:17:\"^wp-sitemap\\.xsl$\";s:36:\"index.php?sitemap-stylesheet=sitemap\";s:23:\"^wp-sitemap-index\\.xsl$\";s:34:\"index.php?sitemap-stylesheet=index\";s:48:\"^wp-sitemap-([a-z]+?)-([a-z\\d_-]+?)-(\\d+?)\\.xml$\";s:75:\"index.php?sitemap=$matches[1]&sitemap-subtype=$matches[2]&paged=$matches[3]\";s:34:\"^wp-sitemap-([a-z]+?)-(\\d+?)\\.xml$\";s:47:\"index.php?sitemap=$matches[1]&paged=$matches[2]\";s:22:\"tribe-promoter-auth/?$\";s:37:\"index.php?tribe-promoter-auth-check=1\";s:8:\"event/?$\";s:32:\"index.php?post_type=tribe_events\";s:38:\"event/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?post_type=tribe_events&feed=$matches[1]\";s:33:\"event/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?post_type=tribe_events&feed=$matches[1]\";s:25:\"event/page/([0-9]{1,})/?$\";s:50:\"index.php?post_type=tribe_events&paged=$matches[1]\";s:47:\"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:42:\"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:23:\"category/(.+?)/embed/?$\";s:46:\"index.php?category_name=$matches[1]&embed=true\";s:35:\"category/(.+?)/page/?([0-9]{1,})/?$\";s:53:\"index.php?category_name=$matches[1]&paged=$matches[2]\";s:17:\"category/(.+?)/?$\";s:35:\"index.php?category_name=$matches[1]\";s:44:\"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:39:\"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:20:\"tag/([^/]+)/embed/?$\";s:36:\"index.php?tag=$matches[1]&embed=true\";s:32:\"tag/([^/]+)/page/?([0-9]{1,})/?$\";s:43:\"index.php?tag=$matches[1]&paged=$matches[2]\";s:14:\"tag/([^/]+)/?$\";s:25:\"index.php?tag=$matches[1]\";s:45:\"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:40:\"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:21:\"type/([^/]+)/embed/?$\";s:44:\"index.php?post_format=$matches[1]&embed=true\";s:33:\"type/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?post_format=$matches[1]&paged=$matches[2]\";s:15:\"type/([^/]+)/?$\";s:33:\"index.php?post_format=$matches[1]\";s:34:\"series/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:44:\"series/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:64:\"series/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:59:\"series/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:59:\"series/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:40:\"series/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:23:\"series/([^/]+)/embed/?$\";s:51:\"index.php?tribe_event_series=$matches[1]&embed=true\";s:27:\"series/([^/]+)/trackback/?$\";s:45:\"index.php?tribe_event_series=$matches[1]&tb=1\";s:35:\"series/([^/]+)/page/?([0-9]{1,})/?$\";s:58:\"index.php?tribe_event_series=$matches[1]&paged=$matches[2]\";s:42:\"series/([^/]+)/comment-page-([0-9]{1,})/?$\";s:58:\"index.php?tribe_event_series=$matches[1]&cpage=$matches[2]\";s:31:\"series/([^/]+)(?:/([0-9]+))?/?$\";s:57:\"index.php?tribe_event_series=$matches[1]&page=$matches[2]\";s:23:\"series/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:33:\"series/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:53:\"series/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:48:\"series/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:48:\"series/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:29:\"series/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:33:\"venue/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:43:\"venue/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:63:\"venue/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:58:\"venue/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:58:\"venue/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:39:\"venue/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:22:\"venue/([^/]+)/embed/?$\";s:44:\"index.php?tribe_venue=$matches[1]&embed=true\";s:26:\"venue/([^/]+)/trackback/?$\";s:38:\"index.php?tribe_venue=$matches[1]&tb=1\";s:34:\"venue/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?tribe_venue=$matches[1]&paged=$matches[2]\";s:41:\"venue/([^/]+)/comment-page-([0-9]{1,})/?$\";s:51:\"index.php?tribe_venue=$matches[1]&cpage=$matches[2]\";s:30:\"venue/([^/]+)(?:/([0-9]+))?/?$\";s:50:\"index.php?tribe_venue=$matches[1]&page=$matches[2]\";s:22:\"venue/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:32:\"venue/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:52:\"venue/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:47:\"venue/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:47:\"venue/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:28:\"venue/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:37:\"organizer/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:47:\"organizer/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:67:\"organizer/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:62:\"organizer/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:62:\"organizer/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:43:\"organizer/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:26:\"organizer/([^/]+)/embed/?$\";s:48:\"index.php?tribe_organizer=$matches[1]&embed=true\";s:30:\"organizer/([^/]+)/trackback/?$\";s:42:\"index.php?tribe_organizer=$matches[1]&tb=1\";s:38:\"organizer/([^/]+)/page/?([0-9]{1,})/?$\";s:55:\"index.php?tribe_organizer=$matches[1]&paged=$matches[2]\";s:45:\"organizer/([^/]+)/comment-page-([0-9]{1,})/?$\";s:55:\"index.php?tribe_organizer=$matches[1]&cpage=$matches[2]\";s:34:\"organizer/([^/]+)(?:/([0-9]+))?/?$\";s:54:\"index.php?tribe_organizer=$matches[1]&page=$matches[2]\";s:26:\"organizer/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:36:\"organizer/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:56:\"organizer/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:51:\"organizer/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:51:\"organizer/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:32:\"organizer/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:33:\"event/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:43:\"event/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:63:\"event/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:58:\"event/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:58:\"event/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:39:\"event/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:22:\"event/([^/]+)/embed/?$\";s:45:\"index.php?tribe_events=$matches[1]&embed=true\";s:26:\"event/([^/]+)/trackback/?$\";s:39:\"index.php?tribe_events=$matches[1]&tb=1\";s:46:\"event/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:51:\"index.php?tribe_events=$matches[1]&feed=$matches[2]\";s:41:\"event/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:51:\"index.php?tribe_events=$matches[1]&feed=$matches[2]\";s:34:\"event/([^/]+)/page/?([0-9]{1,})/?$\";s:52:\"index.php?tribe_events=$matches[1]&paged=$matches[2]\";s:41:\"event/([^/]+)/comment-page-([0-9]{1,})/?$\";s:52:\"index.php?tribe_events=$matches[1]&cpage=$matches[2]\";s:30:\"event/([^/]+)(?:/([0-9]+))?/?$\";s:51:\"index.php?tribe_events=$matches[1]&page=$matches[2]\";s:22:\"event/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:32:\"event/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:52:\"event/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:47:\"event/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:47:\"event/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:28:\"event/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:54:\"events/category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:55:\"index.php?tribe_events_cat=$matches[1]&feed=$matches[2]\";s:49:\"events/category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:55:\"index.php?tribe_events_cat=$matches[1]&feed=$matches[2]\";s:30:\"events/category/(.+?)/embed/?$\";s:49:\"index.php?tribe_events_cat=$matches[1]&embed=true\";s:42:\"events/category/(.+?)/page/?([0-9]{1,})/?$\";s:56:\"index.php?tribe_events_cat=$matches[1]&paged=$matches[2]\";s:24:\"events/category/(.+?)/?$\";s:38:\"index.php?tribe_events_cat=$matches[1]\";s:41:\"deleted_event/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:51:\"deleted_event/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:71:\"deleted_event/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:66:\"deleted_event/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:66:\"deleted_event/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:47:\"deleted_event/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:30:\"deleted_event/([^/]+)/embed/?$\";s:46:\"index.php?deleted_event=$matches[1]&embed=true\";s:34:\"deleted_event/([^/]+)/trackback/?$\";s:40:\"index.php?deleted_event=$matches[1]&tb=1\";s:42:\"deleted_event/([^/]+)/page/?([0-9]{1,})/?$\";s:53:\"index.php?deleted_event=$matches[1]&paged=$matches[2]\";s:49:\"deleted_event/([^/]+)/comment-page-([0-9]{1,})/?$\";s:53:\"index.php?deleted_event=$matches[1]&cpage=$matches[2]\";s:38:\"deleted_event/([^/]+)(?:/([0-9]+))?/?$\";s:52:\"index.php?deleted_event=$matches[1]&page=$matches[2]\";s:30:\"deleted_event/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:40:\"deleted_event/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:60:\"deleted_event/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:55:\"deleted_event/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:55:\"deleted_event/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:36:\"deleted_event/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:65:\"events/organizers/category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:61:\"index.php?tec_organizer_category=$matches[1]&feed=$matches[2]\";s:60:\"events/organizers/category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:61:\"index.php?tec_organizer_category=$matches[1]&feed=$matches[2]\";s:41:\"events/organizers/category/(.+?)/embed/?$\";s:55:\"index.php?tec_organizer_category=$matches[1]&embed=true\";s:53:\"events/organizers/category/(.+?)/page/?([0-9]{1,})/?$\";s:62:\"index.php?tec_organizer_category=$matches[1]&paged=$matches[2]\";s:35:\"events/organizers/category/(.+?)/?$\";s:44:\"index.php?tec_organizer_category=$matches[1]\";s:61:\"events/venues/category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:57:\"index.php?tec_venue_category=$matches[1]&feed=$matches[2]\";s:56:\"events/venues/category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:57:\"index.php?tec_venue_category=$matches[1]&feed=$matches[2]\";s:37:\"events/venues/category/(.+?)/embed/?$\";s:51:\"index.php?tec_venue_category=$matches[1]&embed=true\";s:49:\"events/venues/category/(.+?)/page/?([0-9]{1,})/?$\";s:58:\"index.php?tec_venue_category=$matches[1]&paged=$matches[2]\";s:31:\"events/venues/category/(.+?)/?$\";s:40:\"index.php?tec_venue_category=$matches[1]\";s:12:\"robots\\.txt$\";s:18:\"index.php?robots=1\";s:13:\"favicon\\.ico$\";s:19:\"index.php?favicon=1\";s:48:\".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\\.php$\";s:18:\"index.php?feed=old\";s:20:\".*wp-app\\.php(/.*)?$\";s:19:\"index.php?error=403\";s:18:\".*wp-register.php$\";s:23:\"index.php?register=true\";s:32:\"feed/(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:27:\"(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:8:\"embed/?$\";s:21:\"index.php?&embed=true\";s:20:\"page/?([0-9]{1,})/?$\";s:28:\"index.php?&paged=$matches[1]\";s:41:\"comments/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:36:\"comments/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:17:\"comments/embed/?$\";s:21:\"index.php?&embed=true\";s:44:\"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:39:\"search/(.+)/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:20:\"search/(.+)/embed/?$\";s:34:\"index.php?s=$matches[1]&embed=true\";s:32:\"search/(.+)/page/?([0-9]{1,})/?$\";s:41:\"index.php?s=$matches[1]&paged=$matches[2]\";s:14:\"search/(.+)/?$\";s:23:\"index.php?s=$matches[1]\";s:47:\"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:42:\"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:23:\"author/([^/]+)/embed/?$\";s:44:\"index.php?author_name=$matches[1]&embed=true\";s:35:\"author/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?author_name=$matches[1]&paged=$matches[2]\";s:17:\"author/([^/]+)/?$\";s:33:\"index.php?author_name=$matches[1]\";s:69:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:45:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$\";s:74:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]\";s:39:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$\";s:63:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]\";s:56:\"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:51:\"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:32:\"([0-9]{4})/([0-9]{1,2})/embed/?$\";s:58:\"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true\";s:44:\"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]\";s:26:\"([0-9]{4})/([0-9]{1,2})/?$\";s:47:\"index.php?year=$matches[1]&monthnum=$matches[2]\";s:43:\"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:38:\"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:19:\"([0-9]{4})/embed/?$\";s:37:\"index.php?year=$matches[1]&embed=true\";s:31:\"([0-9]{4})/page/?([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&paged=$matches[2]\";s:13:\"([0-9]{4})/?$\";s:26:\"index.php?year=$matches[1]\";s:27:\".?.+?/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\".?.+?/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\".?.+?/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"(.?.+?)/embed/?$\";s:41:\"index.php?pagename=$matches[1]&embed=true\";s:20:\"(.?.+?)/trackback/?$\";s:35:\"index.php?pagename=$matches[1]&tb=1\";s:40:\"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:35:\"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:28:\"(.?.+?)/page/?([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&paged=$matches[2]\";s:35:\"(.?.+?)/comment-page-([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&cpage=$matches[2]\";s:24:\"(.?.+?)(?:/([0-9]+))?/?$\";s:47:\"index.php?pagename=$matches[1]&page=$matches[2]\";s:27:\"[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\"[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\"[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\"[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\"[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\"[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"([^/]+)/embed/?$\";s:37:\"index.php?name=$matches[1]&embed=true\";s:20:\"([^/]+)/trackback/?$\";s:31:\"index.php?name=$matches[1]&tb=1\";s:40:\"([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?name=$matches[1]&feed=$matches[2]\";s:35:\"([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?name=$matches[1]&feed=$matches[2]\";s:28:\"([^/]+)/page/?([0-9]{1,})/?$\";s:44:\"index.php?name=$matches[1]&paged=$matches[2]\";s:35:\"([^/]+)/comment-page-([0-9]{1,})/?$\";s:44:\"index.php?name=$matches[1]&cpage=$matches[2]\";s:24:\"([^/]+)(?:/([0-9]+))?/?$\";s:43:\"index.php?name=$matches[1]&page=$matches[2]\";s:16:\"[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:26:\"[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:46:\"[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:41:\"[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:41:\"[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:22:\"[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";}','yes'),(30,'hack_file','0','yes'),(31,'blog_charset','UTF-8','yes'),(32,'moderation_keys','','no'),(33,'active_plugins','a:3:{i:0;s:34:\"events-pro/events-calendar-pro.php\";i:1;s:29:\"tec-canonical-url-service.php\";i:2;s:43:\"the-events-calendar/the-events-calendar.php\";}','yes'),(34,'category_base','','yes'),(35,'ping_sites','http://rpc.pingomatic.com/','yes'),(36,'comment_max_links','2','yes'),(37,'gmt_offset','','yes'),(38,'default_email_category','1','yes'),(39,'recently_edited','','no'),(40,'template','twentytwenty','yes'),(41,'stylesheet','twentytwenty','yes'),(42,'comment_registration','0','yes'),(43,'html_type','text/html','yes'),(44,'use_trackback','0','yes'),(45,'default_role','subscriber','yes'),(46,'db_version','57155','yes'),(47,'uploads_use_yearmonth_folders','1','yes'),(48,'upload_path','','yes'),(49,'blog_public','1','yes'),(50,'default_link_category','2','yes'),(51,'show_on_front','posts','yes'),(52,'tag_base','','yes'),(53,'show_avatars','1','yes'),(54,'avatar_rating','G','yes'),(55,'upload_url_path','','yes'),(56,'thumbnail_size_w','150','yes'),(57,'thumbnail_size_h','150','yes'),(58,'thumbnail_crop','1','yes'),(59,'medium_size_w','300','yes'),(60,'medium_size_h','300','yes'),(61,'avatar_default','mystery','yes'),(62,'large_size_w','1024','yes'),(63,'large_size_h','1024','yes'),(64,'image_default_link_type','none','yes'),(65,'image_default_size','','yes'),(66,'image_default_align','','yes'),(67,'close_comments_for_old_posts','0','yes'),(68,'close_comments_days_old','14','yes'),(69,'thread_comments','1','yes'),(70,'thread_comments_depth','5','yes'),(71,'page_comments','0','yes'),(72,'comments_per_page','50','yes'),(73,'default_comments_page','newest','yes'),(74,'comment_order','asc','yes'),(75,'sticky_posts','a:0:{}','yes'),(76,'widget_categories','a:2:{i:2;a:4:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:12:\"hierarchical\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}','yes'),(77,'widget_text','a:0:{}','yes'),(78,'widget_rss','a:0:{}','yes'),(79,'uninstall_plugins','a:0:{}','no'),(80,'timezone_string','America/Los_Angeles','yes'),(81,'page_for_posts','0','yes'),(82,'page_on_front','0','yes'),(83,'default_post_format','0','yes'),(84,'link_manager_enabled','0','yes'),(85,'finished_splitting_shared_terms','1','yes'),(86,'site_icon','0','yes'),(87,'medium_large_size_w','768','yes'),(88,'medium_large_size_h','0','yes'),(89,'wp_page_for_privacy_policy','0','yes'),(90,'show_comments_cookies_opt_in','1','yes'),(92,'disallowed_keys','','no'),(93,'comment_previously_approved','1','yes'),(94,'auto_plugin_theme_update_emails','a:0:{}','no'),(95,'initial_db_version','48748','yes'),(96,'wp_user_roles','a:5:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:101:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;s:25:\"read_private_tribe_events\";b:1;s:17:\"edit_tribe_events\";b:1;s:24:\"edit_others_tribe_events\";b:1;s:25:\"edit_private_tribe_events\";b:1;s:27:\"edit_published_tribe_events\";b:1;s:19:\"delete_tribe_events\";b:1;s:26:\"delete_others_tribe_events\";b:1;s:27:\"delete_private_tribe_events\";b:1;s:29:\"delete_published_tribe_events\";b:1;s:20:\"publish_tribe_events\";b:1;s:25:\"read_private_tribe_venues\";b:1;s:17:\"edit_tribe_venues\";b:1;s:24:\"edit_others_tribe_venues\";b:1;s:25:\"edit_private_tribe_venues\";b:1;s:27:\"edit_published_tribe_venues\";b:1;s:19:\"delete_tribe_venues\";b:1;s:26:\"delete_others_tribe_venues\";b:1;s:27:\"delete_private_tribe_venues\";b:1;s:29:\"delete_published_tribe_venues\";b:1;s:20:\"publish_tribe_venues\";b:1;s:29:\"read_private_tribe_organizers\";b:1;s:21:\"edit_tribe_organizers\";b:1;s:28:\"edit_others_tribe_organizers\";b:1;s:29:\"edit_private_tribe_organizers\";b:1;s:31:\"edit_published_tribe_organizers\";b:1;s:23:\"delete_tribe_organizers\";b:1;s:30:\"delete_others_tribe_organizers\";b:1;s:31:\"delete_private_tribe_organizers\";b:1;s:33:\"delete_published_tribe_organizers\";b:1;s:24:\"publish_tribe_organizers\";b:1;s:31:\"read_private_aggregator-records\";b:1;s:23:\"edit_aggregator-records\";b:1;s:30:\"edit_others_aggregator-records\";b:1;s:31:\"edit_private_aggregator-records\";b:1;s:33:\"edit_published_aggregator-records\";b:1;s:25:\"delete_aggregator-records\";b:1;s:32:\"delete_others_aggregator-records\";b:1;s:33:\"delete_private_aggregator-records\";b:1;s:35:\"delete_published_aggregator-records\";b:1;s:26:\"publish_aggregator-records\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:74:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:25:\"read_private_tribe_events\";b:1;s:17:\"edit_tribe_events\";b:1;s:24:\"edit_others_tribe_events\";b:1;s:25:\"edit_private_tribe_events\";b:1;s:27:\"edit_published_tribe_events\";b:1;s:19:\"delete_tribe_events\";b:1;s:26:\"delete_others_tribe_events\";b:1;s:27:\"delete_private_tribe_events\";b:1;s:29:\"delete_published_tribe_events\";b:1;s:20:\"publish_tribe_events\";b:1;s:25:\"read_private_tribe_venues\";b:1;s:17:\"edit_tribe_venues\";b:1;s:24:\"edit_others_tribe_venues\";b:1;s:25:\"edit_private_tribe_venues\";b:1;s:27:\"edit_published_tribe_venues\";b:1;s:19:\"delete_tribe_venues\";b:1;s:26:\"delete_others_tribe_venues\";b:1;s:27:\"delete_private_tribe_venues\";b:1;s:29:\"delete_published_tribe_venues\";b:1;s:20:\"publish_tribe_venues\";b:1;s:29:\"read_private_tribe_organizers\";b:1;s:21:\"edit_tribe_organizers\";b:1;s:28:\"edit_others_tribe_organizers\";b:1;s:29:\"edit_private_tribe_organizers\";b:1;s:31:\"edit_published_tribe_organizers\";b:1;s:23:\"delete_tribe_organizers\";b:1;s:30:\"delete_others_tribe_organizers\";b:1;s:31:\"delete_private_tribe_organizers\";b:1;s:33:\"delete_published_tribe_organizers\";b:1;s:24:\"publish_tribe_organizers\";b:1;s:31:\"read_private_aggregator-records\";b:1;s:23:\"edit_aggregator-records\";b:1;s:30:\"edit_others_aggregator-records\";b:1;s:31:\"edit_private_aggregator-records\";b:1;s:33:\"edit_published_aggregator-records\";b:1;s:25:\"delete_aggregator-records\";b:1;s:32:\"delete_others_aggregator-records\";b:1;s:33:\"delete_private_aggregator-records\";b:1;s:35:\"delete_published_aggregator-records\";b:1;s:26:\"publish_aggregator-records\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:30:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:17:\"edit_tribe_events\";b:1;s:27:\"edit_published_tribe_events\";b:1;s:19:\"delete_tribe_events\";b:1;s:29:\"delete_published_tribe_events\";b:1;s:20:\"publish_tribe_events\";b:1;s:17:\"edit_tribe_venues\";b:1;s:27:\"edit_published_tribe_venues\";b:1;s:19:\"delete_tribe_venues\";b:1;s:29:\"delete_published_tribe_venues\";b:1;s:20:\"publish_tribe_venues\";b:1;s:21:\"edit_tribe_organizers\";b:1;s:31:\"edit_published_tribe_organizers\";b:1;s:23:\"delete_tribe_organizers\";b:1;s:33:\"delete_published_tribe_organizers\";b:1;s:24:\"publish_tribe_organizers\";b:1;s:23:\"edit_aggregator-records\";b:1;s:33:\"edit_published_aggregator-records\";b:1;s:25:\"delete_aggregator-records\";b:1;s:35:\"delete_published_aggregator-records\";b:1;s:26:\"publish_aggregator-records\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:13:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:17:\"edit_tribe_events\";b:1;s:19:\"delete_tribe_events\";b:1;s:17:\"edit_tribe_venues\";b:1;s:19:\"delete_tribe_venues\";b:1;s:21:\"edit_tribe_organizers\";b:1;s:23:\"delete_tribe_organizers\";b:1;s:23:\"edit_aggregator-records\";b:1;s:25:\"delete_aggregator-records\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}}','yes'),(97,'fresh_site','1','yes'),(98,'widget_search','a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}','yes'),(99,'widget_recent-posts','a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}','yes'),(100,'widget_recent-comments','a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}','yes'),(101,'widget_archives','a:2:{i:2;a:3:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}','yes'),(102,'widget_meta','a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}','yes'),(103,'sidebars_widgets','a:4:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:3:{i:0;s:8:\"search-2\";i:1;s:14:\"recent-posts-2\";i:2;s:17:\"recent-comments-2\";}s:9:\"sidebar-2\";a:3:{i:0;s:10:\"archives-2\";i:1;s:12:\"categories-2\";i:2;s:6:\"meta-2\";}s:13:\"array_version\";i:3;}','yes'),(104,'cron','a:13:{i:1670232949;a:1:{s:26:\"action_scheduler_run_queue\";a:1:{s:32:\"0d04ed39571b55704c122d726248bbac\";a:3:{s:8:\"schedule\";s:12:\"every_minute\";s:4:\"args\";a:1:{i:0;s:7:\"WP Cron\";}s:8:\"interval\";i:60;}}}i:1670234755;a:1:{s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}}i:1670241950;a:1:{s:32:\"recovery_mode_clean_expired_keys\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1670241955;a:3:{s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1670241963;a:2:{s:24:\"tribe_common_log_cleanup\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:16:\"tribe_daily_cron\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1670241964;a:1:{s:30:\"tribe_schedule_transient_purge\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1670242036;a:2:{s:19:\"wp_scheduled_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:25:\"delete_expired_transients\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1670252359;a:1:{s:21:\"tribe-recurrence-cron\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1670252374;a:1:{s:21:\"wp_update_user_counts\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1670252376;a:1:{s:30:\"wp_scheduled_auto_draft_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1670501150;a:1:{s:30:\"wp_site_health_scheduled_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}i:1717085522;a:1:{s:30:\"wp_delete_temp_updater_backups\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}s:7:\"version\";i:2;}','yes'),(105,'widget_pages','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(106,'widget_calendar','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(107,'widget_media_audio','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(108,'widget_media_image','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(109,'widget_media_gallery','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(110,'widget_media_video','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(111,'widget_tag_cloud','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(112,'widget_nav_menu','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(113,'widget_custom_html','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(124,'tribe_last_updated_option','1717085521.6209','yes'),(125,'tribe_last_save_post','1717085521.6214','yes'),(126,'widget_tribe-events-list-widget','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(127,'tribe_last_generate_rewrite_rules','1717085521.6122','yes'),(139,'new_admin_email','admin@wordpress.test','yes'),(142,'schema-ActionScheduler_StoreSchema','7.0.1717083202','yes'),(143,'schema-ActionScheduler_LoggerSchema','3.0.1668075529','yes'),(148,'widget_block','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(149,'widget_tribe-widget-events-list','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(152,'tec_ct1_events_table_schema_version','1.0.1','yes'),(153,'tec_ct1_occurrences_table_schema_version','1.0.2','yes'),(154,'tec_ct1_migration_state','a:3:{s:18:\"complete_timestamp\";N;s:5:\"phase\";s:22:\"migration-not-required\";s:19:\"preview_unsupported\";b:0;}','yes'),(158,'action_scheduler_lock_async-request-runner','6658a3a41d3f96.86975904|1717085152','yes'),(159,'action_scheduler_migration_status','complete','yes'),(162,'recovery_keys','a:0:{}','yes'),(166,'finished_updating_comment_type','1','yes'),(169,'https_detection_errors','a:1:{s:20:\"https_request_failed\";a:1:{i:0;s:21:\"HTTPS request failed.\";}}','yes'),(176,'theme_mods_twentytwenty','a:1:{s:18:\"custom_css_post_id\";i:-1;}','yes'),(179,'_transient_health-check-site-status-result','{\"good\":17,\"recommended\":4,\"critical\":0}','yes'),(183,'auto_update_core_dev','enabled','yes'),(184,'auto_update_core_minor','enabled','yes'),(185,'auto_update_core_major','unset','yes'),(186,'wp_force_deactivated_plugins','a:0:{}','yes'),(187,'user_count','1','no'),(188,'db_upgraded','1','yes'),(206,'_site_transient_update_plugins','O:8:\"stdClass\":4:{s:12:\"last_checked\";i:1717085536;s:8:\"response\";a:29:{s:37:\"action-scheduler/action-scheduler.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:30:\"w.org/plugins/action-scheduler\";s:4:\"slug\";s:16:\"action-scheduler\";s:6:\"plugin\";s:37:\"action-scheduler/action-scheduler.php\";s:11:\"new_version\";s:5:\"3.8.0\";s:3:\"url\";s:47:\"https://wordpress.org/plugins/action-scheduler/\";s:7:\"package\";s:65:\"https://downloads.wordpress.org/plugin/action-scheduler.3.8.0.zip\";s:5:\"icons\";a:1:{s:7:\"default\";s:60:\"https://s.w.org/plugins/geopattern-icon/action-scheduler.svg\";}s:7:\"banners\";a:0:{}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.2\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"5.6\";s:16:\"requires_plugins\";a:0:{}}s:47:\"advanced-cron-manager/advanced-cron-manager.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:35:\"w.org/plugins/advanced-cron-manager\";s:4:\"slug\";s:21:\"advanced-cron-manager\";s:6:\"plugin\";s:47:\"advanced-cron-manager/advanced-cron-manager.php\";s:11:\"new_version\";s:5:\"2.5.5\";s:3:\"url\";s:52:\"https://wordpress.org/plugins/advanced-cron-manager/\";s:7:\"package\";s:70:\"https://downloads.wordpress.org/plugin/advanced-cron-manager.2.5.5.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:66:\"https://ps.w.org/advanced-cron-manager/assets/icon.svg?rev=2520950\";s:3:\"svg\";s:66:\"https://ps.w.org/advanced-cron-manager/assets/icon.svg?rev=2520950\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:77:\"https://ps.w.org/advanced-cron-manager/assets/banner-1544x500.png?rev=2520953\";s:2:\"1x\";s:76:\"https://ps.w.org/advanced-cron-manager/assets/banner-772x250.png?rev=2520953\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"3.6\";s:6:\"tested\";s:5:\"6.4.4\";s:12:\"requires_php\";s:3:\"5.3\";s:16:\"requires_plugins\";a:0:{}}s:19:\"akismet/akismet.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:21:\"w.org/plugins/akismet\";s:4:\"slug\";s:7:\"akismet\";s:6:\"plugin\";s:19:\"akismet/akismet.php\";s:11:\"new_version\";s:5:\"5.3.2\";s:3:\"url\";s:38:\"https://wordpress.org/plugins/akismet/\";s:7:\"package\";s:56:\"https://downloads.wordpress.org/plugin/akismet.5.3.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:60:\"https://ps.w.org/akismet/assets/icon-256x256.png?rev=2818463\";s:2:\"1x\";s:60:\"https://ps.w.org/akismet/assets/icon-128x128.png?rev=2818463\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:63:\"https://ps.w.org/akismet/assets/banner-1544x500.png?rev=2900731\";s:2:\"1x\";s:62:\"https://ps.w.org/akismet/assets/banner-772x250.png?rev=2900731\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.8\";s:6:\"tested\";s:5:\"6.4.4\";s:12:\"requires_php\";s:6:\"5.6.20\";s:16:\"requires_plugins\";a:0:{}}s:31:\"code-snippets/code-snippets.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:27:\"w.org/plugins/code-snippets\";s:4:\"slug\";s:13:\"code-snippets\";s:6:\"plugin\";s:31:\"code-snippets/code-snippets.php\";s:11:\"new_version\";s:7:\"3.6.5.1\";s:3:\"url\";s:44:\"https://wordpress.org/plugins/code-snippets/\";s:7:\"package\";s:64:\"https://downloads.wordpress.org/plugin/code-snippets.3.6.5.1.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:58:\"https://ps.w.org/code-snippets/assets/icon.svg?rev=2148878\";s:3:\"svg\";s:58:\"https://ps.w.org/code-snippets/assets/icon.svg?rev=2148878\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/code-snippets/assets/banner-1544x500.png?rev=2260997\";s:2:\"1x\";s:68:\"https://ps.w.org/code-snippets/assets/banner-772x250.png?rev=2256244\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.0\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:39:\"custom-post-types/custom-post-types.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:31:\"w.org/plugins/custom-post-types\";s:4:\"slug\";s:17:\"custom-post-types\";s:6:\"plugin\";s:39:\"custom-post-types/custom-post-types.php\";s:11:\"new_version\";s:5:\"5.0.5\";s:3:\"url\";s:48:\"https://wordpress.org/plugins/custom-post-types/\";s:7:\"package\";s:66:\"https://downloads.wordpress.org/plugin/custom-post-types.5.0.5.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:62:\"https://ps.w.org/custom-post-types/assets/icon.svg?rev=2955361\";s:3:\"svg\";s:62:\"https://ps.w.org/custom-post-types/assets/icon.svg?rev=2955361\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:73:\"https://ps.w.org/custom-post-types/assets/banner-1544x500.png?rev=2955370\";s:2:\"1x\";s:72:\"https://ps.w.org/custom-post-types/assets/banner-772x250.png?rev=2955370\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.0\";s:6:\"tested\";s:5:\"6.4.4\";s:12:\"requires_php\";s:3:\"5.6\";s:16:\"requires_plugins\";a:0:{}}s:29:\"elasticpress/elasticpress.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:26:\"w.org/plugins/elasticpress\";s:4:\"slug\";s:12:\"elasticpress\";s:6:\"plugin\";s:29:\"elasticpress/elasticpress.php\";s:11:\"new_version\";s:5:\"5.1.1\";s:3:\"url\";s:43:\"https://wordpress.org/plugins/elasticpress/\";s:7:\"package\";s:61:\"https://downloads.wordpress.org/plugin/elasticpress.5.1.1.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:65:\"https://ps.w.org/elasticpress/assets/icon-256x256.jpg?rev=2458479\";s:2:\"1x\";s:65:\"https://ps.w.org/elasticpress/assets/icon-128x128.jpg?rev=2458479\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:68:\"https://ps.w.org/elasticpress/assets/banner-1544x500.png?rev=2458479\";s:2:\"1x\";s:67:\"https://ps.w.org/elasticpress/assets/banner-772x250.png?rev=2458479\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.0\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:23:\"elementor/elementor.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:23:\"w.org/plugins/elementor\";s:4:\"slug\";s:9:\"elementor\";s:6:\"plugin\";s:23:\"elementor/elementor.php\";s:11:\"new_version\";s:6:\"3.21.8\";s:3:\"url\";s:40:\"https://wordpress.org/plugins/elementor/\";s:7:\"package\";s:59:\"https://downloads.wordpress.org/plugin/elementor.3.21.8.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:54:\"https://ps.w.org/elementor/assets/icon.svg?rev=2597493\";s:3:\"svg\";s:54:\"https://ps.w.org/elementor/assets/icon.svg?rev=2597493\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:65:\"https://ps.w.org/elementor/assets/banner-1544x500.png?rev=3005087\";s:2:\"1x\";s:64:\"https://ps.w.org/elementor/assets/banner-772x250.png?rev=3005087\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.0\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:53:\"facebook-for-woocommerce/facebook-for-woocommerce.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:38:\"w.org/plugins/facebook-for-woocommerce\";s:4:\"slug\";s:24:\"facebook-for-woocommerce\";s:6:\"plugin\";s:53:\"facebook-for-woocommerce/facebook-for-woocommerce.php\";s:11:\"new_version\";s:5:\"3.2.3\";s:3:\"url\";s:55:\"https://wordpress.org/plugins/facebook-for-woocommerce/\";s:7:\"package\";s:73:\"https://downloads.wordpress.org/plugin/facebook-for-woocommerce.3.2.3.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:69:\"https://ps.w.org/facebook-for-woocommerce/assets/icon.svg?rev=2040223\";s:3:\"svg\";s:69:\"https://ps.w.org/facebook-for-woocommerce/assets/icon.svg?rev=2040223\";}s:7:\"banners\";a:0:{}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.6\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";b:0;s:16:\"requires_plugins\";a:1:{i:0;s:11:\"woocommerce\";}}s:59:\"genesis-connect-woocommerce/genesis-connect-woocommerce.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:41:\"w.org/plugins/genesis-connect-woocommerce\";s:4:\"slug\";s:27:\"genesis-connect-woocommerce\";s:6:\"plugin\";s:59:\"genesis-connect-woocommerce/genesis-connect-woocommerce.php\";s:11:\"new_version\";s:5:\"1.1.2\";s:3:\"url\";s:58:\"https://wordpress.org/plugins/genesis-connect-woocommerce/\";s:7:\"package\";s:76:\"https://downloads.wordpress.org/plugin/genesis-connect-woocommerce.1.1.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:80:\"https://ps.w.org/genesis-connect-woocommerce/assets/icon-256x256.png?rev=1335684\";s:2:\"1x\";s:80:\"https://ps.w.org/genesis-connect-woocommerce/assets/icon-128x128.png?rev=1335684\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:83:\"https://ps.w.org/genesis-connect-woocommerce/assets/banner-1544x500.png?rev=1335684\";s:2:\"1x\";s:82:\"https://ps.w.org/genesis-connect-woocommerce/assets/banner-772x250.png?rev=1335684\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.7\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"5.6\";s:16:\"requires_plugins\";a:0:{}}s:51:\"google-listings-and-ads/google-listings-and-ads.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:37:\"w.org/plugins/google-listings-and-ads\";s:4:\"slug\";s:23:\"google-listings-and-ads\";s:6:\"plugin\";s:51:\"google-listings-and-ads/google-listings-and-ads.php\";s:11:\"new_version\";s:5:\"2.7.1\";s:3:\"url\";s:54:\"https://wordpress.org/plugins/google-listings-and-ads/\";s:7:\"package\";s:72:\"https://downloads.wordpress.org/plugin/google-listings-and-ads.2.7.1.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:68:\"https://ps.w.org/google-listings-and-ads/assets/icon.svg?rev=2775988\";s:3:\"svg\";s:68:\"https://ps.w.org/google-listings-and-ads/assets/icon.svg?rev=2775988\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:79:\"https://ps.w.org/google-listings-and-ads/assets/banner-1544x500.png?rev=2542080\";s:2:\"1x\";s:78:\"https://ps.w.org/google-listings-and-ads/assets/banner-772x250.png?rev=2542080\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.9\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:1:{i:0;s:11:\"woocommerce\";}}s:25:\"gutenverse/gutenverse.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:24:\"w.org/plugins/gutenverse\";s:4:\"slug\";s:10:\"gutenverse\";s:6:\"plugin\";s:25:\"gutenverse/gutenverse.php\";s:11:\"new_version\";s:5:\"1.9.2\";s:3:\"url\";s:41:\"https://wordpress.org/plugins/gutenverse/\";s:7:\"package\";s:59:\"https://downloads.wordpress.org/plugin/gutenverse.1.9.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:63:\"https://ps.w.org/gutenverse/assets/icon-256x256.gif?rev=2841417\";s:2:\"1x\";s:63:\"https://ps.w.org/gutenverse/assets/icon-128x128.gif?rev=2841417\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:66:\"https://ps.w.org/gutenverse/assets/banner-1544x500.png?rev=2784989\";s:2:\"1x\";s:65:\"https://ps.w.org/gutenverse/assets/banner-772x250.png?rev=2784989\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";b:0;s:6:\"tested\";s:5:\"6.4.4\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:41:\"jsm-show-post-meta/jsm-show-post-meta.php\";O:8:\"stdClass\":14:{s:2:\"id\";s:32:\"w.org/plugins/jsm-show-post-meta\";s:4:\"slug\";s:18:\"jsm-show-post-meta\";s:6:\"plugin\";s:41:\"jsm-show-post-meta/jsm-show-post-meta.php\";s:11:\"new_version\";s:5:\"4.3.0\";s:3:\"url\";s:49:\"https://wordpress.org/plugins/jsm-show-post-meta/\";s:7:\"package\";s:67:\"https://downloads.wordpress.org/plugin/jsm-show-post-meta.4.3.0.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:71:\"https://ps.w.org/jsm-show-post-meta/assets/icon-256x256.jpg?rev=2396819\";s:2:\"1x\";s:71:\"https://ps.w.org/jsm-show-post-meta/assets/icon-128x128.jpg?rev=2396819\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:74:\"https://ps.w.org/jsm-show-post-meta/assets/banner-1544x500.jpg?rev=3024578\";s:2:\"1x\";s:73:\"https://ps.w.org/jsm-show-post-meta/assets/banner-772x250.jpg?rev=3024578\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.8\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:6:\"7.2.34\";s:16:\"requires_plugins\";a:0:{}s:14:\"upgrade_notice\";s:61:\"<p>(2024/04/18) Updated the <code>SucomUtil</code> class.</p>\";}s:35:\"litespeed-cache/litespeed-cache.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:29:\"w.org/plugins/litespeed-cache\";s:4:\"slug\";s:15:\"litespeed-cache\";s:6:\"plugin\";s:35:\"litespeed-cache/litespeed-cache.php\";s:11:\"new_version\";s:7:\"6.2.0.1\";s:3:\"url\";s:46:\"https://wordpress.org/plugins/litespeed-cache/\";s:7:\"package\";s:66:\"https://downloads.wordpress.org/plugin/litespeed-cache.6.2.0.1.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:68:\"https://ps.w.org/litespeed-cache/assets/icon-256x256.png?rev=2554181\";s:2:\"1x\";s:68:\"https://ps.w.org/litespeed-cache/assets/icon-128x128.png?rev=2554181\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:71:\"https://ps.w.org/litespeed-cache/assets/banner-1544x500.png?rev=2554181\";s:2:\"1x\";s:70:\"https://ps.w.org/litespeed-cache/assets/banner-772x250.png?rev=2554181\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.0\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";b:0;s:16:\"requires_plugins\";a:0:{}}s:55:\"pinterest-for-woocommerce/pinterest-for-woocommerce.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:39:\"w.org/plugins/pinterest-for-woocommerce\";s:4:\"slug\";s:25:\"pinterest-for-woocommerce\";s:6:\"plugin\";s:55:\"pinterest-for-woocommerce/pinterest-for-woocommerce.php\";s:11:\"new_version\";s:6:\"1.3.24\";s:3:\"url\";s:56:\"https://wordpress.org/plugins/pinterest-for-woocommerce/\";s:7:\"package\";s:75:\"https://downloads.wordpress.org/plugin/pinterest-for-woocommerce.1.3.24.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:70:\"https://ps.w.org/pinterest-for-woocommerce/assets/icon.svg?rev=2619817\";s:3:\"svg\";s:70:\"https://ps.w.org/pinterest-for-woocommerce/assets/icon.svg?rev=2619817\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:81:\"https://ps.w.org/pinterest-for-woocommerce/assets/banner-1544x500.png?rev=2619817\";s:2:\"1x\";s:80:\"https://ps.w.org/pinterest-for-woocommerce/assets/banner-772x250.png?rev=2619817\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.6\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:31:\"query-monitor/query-monitor.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:27:\"w.org/plugins/query-monitor\";s:4:\"slug\";s:13:\"query-monitor\";s:6:\"plugin\";s:31:\"query-monitor/query-monitor.php\";s:11:\"new_version\";s:6:\"3.16.3\";s:3:\"url\";s:44:\"https://wordpress.org/plugins/query-monitor/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/query-monitor.3.16.3.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:58:\"https://ps.w.org/query-monitor/assets/icon.svg?rev=2994095\";s:3:\"svg\";s:58:\"https://ps.w.org/query-monitor/assets/icon.svg?rev=2994095\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/query-monitor/assets/banner-1544x500.png?rev=2870124\";s:2:\"1x\";s:68:\"https://ps.w.org/query-monitor/assets/banner-772x250.png?rev=2457098\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.7\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:27:\"redis-cache/redis-cache.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:25:\"w.org/plugins/redis-cache\";s:4:\"slug\";s:11:\"redis-cache\";s:6:\"plugin\";s:27:\"redis-cache/redis-cache.php\";s:11:\"new_version\";s:5:\"2.5.2\";s:3:\"url\";s:42:\"https://wordpress.org/plugins/redis-cache/\";s:7:\"package\";s:60:\"https://downloads.wordpress.org/plugin/redis-cache.2.5.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/redis-cache/assets/icon-256x256.gif?rev=2568513\";s:2:\"1x\";s:64:\"https://ps.w.org/redis-cache/assets/icon-128x128.gif?rev=2568513\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/redis-cache/assets/banner-1544x500.png?rev=2315420\";s:2:\"1x\";s:66:\"https://ps.w.org/redis-cache/assets/banner-772x250.png?rev=2315420\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.6\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.2\";s:16:\"requires_plugins\";a:0:{}}s:32:\"wp-simple-firewall/icwp-wpsf.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:32:\"w.org/plugins/wp-simple-firewall\";s:4:\"slug\";s:18:\"wp-simple-firewall\";s:6:\"plugin\";s:32:\"wp-simple-firewall/icwp-wpsf.php\";s:11:\"new_version\";s:7:\"19.1.19\";s:3:\"url\";s:49:\"https://wordpress.org/plugins/wp-simple-firewall/\";s:7:\"package\";s:69:\"https://downloads.wordpress.org/plugin/wp-simple-firewall.19.1.19.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:71:\"https://ps.w.org/wp-simple-firewall/assets/icon-256x256.png?rev=3054572\";s:2:\"1x\";s:71:\"https://ps.w.org/wp-simple-firewall/assets/icon-128x128.png?rev=3054572\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:74:\"https://ps.w.org/wp-simple-firewall/assets/banner-1544x500.png?rev=2472340\";s:2:\"1x\";s:73:\"https://ps.w.org/wp-simple-firewall/assets/banner-772x250.png?rev=2472340\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.7\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:5:\"7.2.5\";s:16:\"requires_plugins\";a:0:{}}s:31:\"sg-cachepress/sg-cachepress.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:27:\"w.org/plugins/sg-cachepress\";s:4:\"slug\";s:13:\"sg-cachepress\";s:6:\"plugin\";s:31:\"sg-cachepress/sg-cachepress.php\";s:11:\"new_version\";s:5:\"7.6.0\";s:3:\"url\";s:44:\"https://wordpress.org/plugins/sg-cachepress/\";s:7:\"package\";s:62:\"https://downloads.wordpress.org/plugin/sg-cachepress.7.6.0.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:66:\"https://ps.w.org/sg-cachepress/assets/icon-256x256.gif?rev=2971889\";s:2:\"1x\";s:66:\"https://ps.w.org/sg-cachepress/assets/icon-128x128.gif?rev=2971889\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/sg-cachepress/assets/banner-1544x500.png?rev=2971889\";s:2:\"1x\";s:68:\"https://ps.w.org/sg-cachepress/assets/banner-772x250.png?rev=2971889\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.7\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.0\";s:16:\"requires_plugins\";a:0:{}}s:47:\"the-events-calendar-zip/the-events-calendar.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:33:\"w.org/plugins/the-events-calendar\";s:4:\"slug\";s:19:\"the-events-calendar\";s:6:\"plugin\";s:47:\"the-events-calendar-zip/the-events-calendar.php\";s:11:\"new_version\";s:5:\"6.5.0\";s:3:\"url\";s:50:\"https://wordpress.org/plugins/the-events-calendar/\";s:7:\"package\";s:68:\"https://downloads.wordpress.org/plugin/the-events-calendar.6.5.0.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:72:\"https://ps.w.org/the-events-calendar/assets/icon-256x256.gif?rev=2516440\";s:2:\"1x\";s:72:\"https://ps.w.org/the-events-calendar/assets/icon-128x128.gif?rev=2516440\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:75:\"https://ps.w.org/the-events-calendar/assets/banner-1544x500.png?rev=2257622\";s:2:\"1x\";s:74:\"https://ps.w.org/the-events-calendar/assets/banner-772x250.png?rev=2257622\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.3\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:46:\"tiktok-for-business/tiktok-for-woocommerce.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:33:\"w.org/plugins/tiktok-for-business\";s:4:\"slug\";s:19:\"tiktok-for-business\";s:6:\"plugin\";s:46:\"tiktok-for-business/tiktok-for-woocommerce.php\";s:11:\"new_version\";s:5:\"1.2.5\";s:3:\"url\";s:50:\"https://wordpress.org/plugins/tiktok-for-business/\";s:7:\"package\";s:68:\"https://downloads.wordpress.org/plugin/tiktok-for-business.1.2.5.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:72:\"https://ps.w.org/tiktok-for-business/assets/icon-256x256.jpg?rev=2721531\";s:2:\"1x\";s:72:\"https://ps.w.org/tiktok-for-business/assets/icon-256x256.jpg?rev=2721531\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:75:\"https://ps.w.org/tiktok-for-business/assets/banner-1544x500.png?rev=2981412\";s:2:\"1x\";s:74:\"https://ps.w.org/tiktok-for-business/assets/banner-772x250.png?rev=2981406\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:5:\"5.7.0\";s:6:\"tested\";s:5:\"6.4.4\";s:12:\"requires_php\";s:3:\"7.0\";s:16:\"requires_plugins\";a:0:{}}s:41:\"transients-manager/transients-manager.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:32:\"w.org/plugins/transients-manager\";s:4:\"slug\";s:18:\"transients-manager\";s:6:\"plugin\";s:41:\"transients-manager/transients-manager.php\";s:11:\"new_version\";s:5:\"2.0.5\";s:3:\"url\";s:49:\"https://wordpress.org/plugins/transients-manager/\";s:7:\"package\";s:67:\"https://downloads.wordpress.org/plugin/transients-manager.2.0.5.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:63:\"https://ps.w.org/transients-manager/assets/icon.svg?rev=1671074\";s:3:\"svg\";s:63:\"https://ps.w.org/transients-manager/assets/icon.svg?rev=1671074\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:74:\"https://ps.w.org/transients-manager/assets/banner-1544x500.png?rev=2645363\";s:2:\"1x\";s:73:\"https://ps.w.org/transients-manager/assets/banner-772x250.png?rev=2645363\";}s:11:\"banners_rtl\";a:2:{s:2:\"2x\";s:78:\"https://ps.w.org/transients-manager/assets/banner-1544x500-rtl.png?rev=2645363\";s:2:\"1x\";s:77:\"https://ps.w.org/transients-manager/assets/banner-772x250-rtl.png?rev=2645363\";}s:8:\"requires\";s:3:\"5.3\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:6:\"5.6.20\";s:16:\"requires_plugins\";a:0:{}}s:33:\"w3-total-cache/w3-total-cache.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:28:\"w.org/plugins/w3-total-cache\";s:4:\"slug\";s:14:\"w3-total-cache\";s:6:\"plugin\";s:33:\"w3-total-cache/w3-total-cache.php\";s:11:\"new_version\";s:5:\"2.7.2\";s:3:\"url\";s:45:\"https://wordpress.org/plugins/w3-total-cache/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/w3-total-cache.2.7.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/w3-total-cache/assets/icon-256x256.png?rev=1041806\";s:2:\"1x\";s:67:\"https://ps.w.org/w3-total-cache/assets/icon-128x128.png?rev=1041806\";}s:7:\"banners\";a:1:{s:2:\"1x\";s:69:\"https://ps.w.org/w3-total-cache/assets/banner-772x250.jpg?rev=1041806\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.3\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"5.6\";s:16:\"requires_plugins\";a:0:{}}s:45:\"woocommerce-services/woocommerce-services.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:34:\"w.org/plugins/woocommerce-services\";s:4:\"slug\";s:20:\"woocommerce-services\";s:6:\"plugin\";s:45:\"woocommerce-services/woocommerce-services.php\";s:11:\"new_version\";s:5:\"2.5.7\";s:3:\"url\";s:51:\"https://wordpress.org/plugins/woocommerce-services/\";s:7:\"package\";s:69:\"https://downloads.wordpress.org/plugin/woocommerce-services.2.5.7.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:73:\"https://ps.w.org/woocommerce-services/assets/icon-256x256.png?rev=2954616\";s:2:\"1x\";s:73:\"https://ps.w.org/woocommerce-services/assets/icon-128x128.png?rev=2954616\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:76:\"https://ps.w.org/woocommerce-services/assets/banner-1544x500.png?rev=2954616\";s:2:\"1x\";s:75:\"https://ps.w.org/woocommerce-services/assets/banner-772x250.png?rev=2954616\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.3\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:1:{i:0;s:11:\"woocommerce\";}}s:45:\"woocommerce-payments/woocommerce-payments.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:34:\"w.org/plugins/woocommerce-payments\";s:4:\"slug\";s:20:\"woocommerce-payments\";s:6:\"plugin\";s:45:\"woocommerce-payments/woocommerce-payments.php\";s:11:\"new_version\";s:5:\"7.7.0\";s:3:\"url\";s:51:\"https://wordpress.org/plugins/woocommerce-payments/\";s:7:\"package\";s:69:\"https://downloads.wordpress.org/plugin/woocommerce-payments.7.7.0.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:73:\"https://ps.w.org/woocommerce-payments/assets/icon-256x256.png?rev=2940394\";s:2:\"1x\";s:73:\"https://ps.w.org/woocommerce-payments/assets/icon-128x128.png?rev=2940394\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:76:\"https://ps.w.org/woocommerce-payments/assets/banner-1544x500.png?rev=2954456\";s:2:\"1x\";s:75:\"https://ps.w.org/woocommerce-payments/assets/banner-772x250.png?rev=2954456\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.0\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.3\";s:16:\"requires_plugins\";a:1:{i:0;s:11:\"woocommerce\";}}s:31:\"wpbenchmark/wp-benchmark-io.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:25:\"w.org/plugins/wpbenchmark\";s:4:\"slug\";s:11:\"wpbenchmark\";s:6:\"plugin\";s:31:\"wpbenchmark/wp-benchmark-io.php\";s:11:\"new_version\";s:5:\"1.4.2\";s:3:\"url\";s:42:\"https://wordpress.org/plugins/wpbenchmark/\";s:7:\"package\";s:54:\"https://downloads.wordpress.org/plugin/wpbenchmark.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/wpbenchmark/assets/icon-256x256.gif?rev=2669206\";s:2:\"1x\";s:64:\"https://ps.w.org/wpbenchmark/assets/icon-128x128.gif?rev=2669206\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/wpbenchmark/assets/banner-1544x500.jpg?rev=2715948\";s:2:\"1x\";s:66:\"https://ps.w.org/wpbenchmark/assets/banner-772x250.jpg?rev=2715948\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.0\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"5.6\";s:16:\"requires_plugins\";a:0:{}}s:31:\"wp-all-export/wp-all-export.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:27:\"w.org/plugins/wp-all-export\";s:4:\"slug\";s:13:\"wp-all-export\";s:6:\"plugin\";s:31:\"wp-all-export/wp-all-export.php\";s:11:\"new_version\";s:5:\"1.4.4\";s:3:\"url\";s:44:\"https://wordpress.org/plugins/wp-all-export/\";s:7:\"package\";s:62:\"https://downloads.wordpress.org/plugin/wp-all-export.1.4.4.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:66:\"https://ps.w.org/wp-all-export/assets/icon-256x256.png?rev=2570162\";s:2:\"1x\";s:66:\"https://ps.w.org/wp-all-export/assets/icon-128x128.png?rev=2570162\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/wp-all-export/assets/banner-1544x500.png?rev=2570162\";s:2:\"1x\";s:68:\"https://ps.w.org/wp-all-export/assets/banner-772x250.png?rev=2570162\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.0\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:35:\"insert-headers-and-footers/ihaf.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:40:\"w.org/plugins/insert-headers-and-footers\";s:4:\"slug\";s:26:\"insert-headers-and-footers\";s:6:\"plugin\";s:35:\"insert-headers-and-footers/ihaf.php\";s:11:\"new_version\";s:6:\"2.1.12\";s:3:\"url\";s:57:\"https://wordpress.org/plugins/insert-headers-and-footers/\";s:7:\"package\";s:76:\"https://downloads.wordpress.org/plugin/insert-headers-and-footers.2.1.12.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:79:\"https://ps.w.org/insert-headers-and-footers/assets/icon-256x256.png?rev=2758516\";s:2:\"1x\";s:79:\"https://ps.w.org/insert-headers-and-footers/assets/icon-128x128.png?rev=2758516\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:82:\"https://ps.w.org/insert-headers-and-footers/assets/banner-1544x500.png?rev=2758516\";s:2:\"1x\";s:81:\"https://ps.w.org/insert-headers-and-footers/assets/banner-772x250.png?rev=2758516\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.6\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"5.5\";s:16:\"requires_plugins\";a:0:{}}s:31:\"wp-statistics/wp-statistics.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:27:\"w.org/plugins/wp-statistics\";s:4:\"slug\";s:13:\"wp-statistics\";s:6:\"plugin\";s:31:\"wp-statistics/wp-statistics.php\";s:11:\"new_version\";s:6:\"14.7.1\";s:3:\"url\";s:44:\"https://wordpress.org/plugins/wp-statistics/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/wp-statistics.14.7.1.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:58:\"https://ps.w.org/wp-statistics/assets/icon.svg?rev=3081064\";s:3:\"svg\";s:58:\"https://ps.w.org/wp-statistics/assets/icon.svg?rev=3081064\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/wp-statistics/assets/banner-1544x500.png?rev=3089645\";s:2:\"1x\";s:68:\"https://ps.w.org/wp-statistics/assets/banner-772x250.png?rev=3089645\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.0\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"5.6\";s:16:\"requires_plugins\";a:0:{}}s:17:\"zapier/zapier.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:20:\"w.org/plugins/zapier\";s:4:\"slug\";s:6:\"zapier\";s:6:\"plugin\";s:17:\"zapier/zapier.php\";s:11:\"new_version\";s:5:\"1.4.0\";s:3:\"url\";s:37:\"https://wordpress.org/plugins/zapier/\";s:7:\"package\";s:55:\"https://downloads.wordpress.org/plugin/zapier.1.4.0.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:51:\"https://ps.w.org/zapier/assets/icon.svg?rev=3043272\";s:3:\"svg\";s:51:\"https://ps.w.org/zapier/assets/icon.svg?rev=3043272\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:62:\"https://ps.w.org/zapier/assets/banner-1544x500.jpg?rev=3068703\";s:2:\"1x\";s:61:\"https://ps.w.org/zapier/assets/banner-772x250.jpg?rev=3068703\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.5\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}}s:12:\"translations\";a:0:{}s:9:\"no_update\";a:13:{s:31:\"event-tickets/event-tickets.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:27:\"w.org/plugins/event-tickets\";s:4:\"slug\";s:13:\"event-tickets\";s:6:\"plugin\";s:31:\"event-tickets/event-tickets.php\";s:11:\"new_version\";s:6:\"5.10.0\";s:3:\"url\";s:44:\"https://wordpress.org/plugins/event-tickets/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/event-tickets.5.10.0.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:58:\"https://ps.w.org/event-tickets/assets/icon.svg?rev=2259340\";s:3:\"svg\";s:58:\"https://ps.w.org/event-tickets/assets/icon.svg?rev=2259340\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/event-tickets/assets/banner-1544x500.png?rev=2257626\";s:2:\"1x\";s:68:\"https://ps.w.org/event-tickets/assets/banner-772x250.png?rev=2257626\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.3\";}s:46:\"gravity-forms-custom-post-types/gfcptaddon.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:45:\"w.org/plugins/gravity-forms-custom-post-types\";s:4:\"slug\";s:31:\"gravity-forms-custom-post-types\";s:6:\"plugin\";s:46:\"gravity-forms-custom-post-types/gfcptaddon.php\";s:11:\"new_version\";s:6:\"3.1.29\";s:3:\"url\";s:62:\"https://wordpress.org/plugins/gravity-forms-custom-post-types/\";s:7:\"package\";s:74:\"https://downloads.wordpress.org/plugin/gravity-forms-custom-post-types.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:84:\"https://ps.w.org/gravity-forms-custom-post-types/assets/icon-256x256.png?rev=2542252\";s:2:\"1x\";s:84:\"https://ps.w.org/gravity-forms-custom-post-types/assets/icon-256x256.png?rev=2542252\";}s:7:\"banners\";a:1:{s:2:\"1x\";s:86:\"https://ps.w.org/gravity-forms-custom-post-types/assets/banner-772x250.jpg?rev=2542252\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:5:\"3.0.1\";}s:9:\"hello.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:25:\"w.org/plugins/hello-dolly\";s:4:\"slug\";s:11:\"hello-dolly\";s:6:\"plugin\";s:9:\"hello.php\";s:11:\"new_version\";s:5:\"1.7.2\";s:3:\"url\";s:42:\"https://wordpress.org/plugins/hello-dolly/\";s:7:\"package\";s:60:\"https://downloads.wordpress.org/plugin/hello-dolly.1.7.3.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/hello-dolly/assets/icon-256x256.jpg?rev=2052855\";s:2:\"1x\";s:64:\"https://ps.w.org/hello-dolly/assets/icon-128x128.jpg?rev=2052855\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/hello-dolly/assets/banner-1544x500.jpg?rev=2645582\";s:2:\"1x\";s:66:\"https://ps.w.org/hello-dolly/assets/banner-772x250.jpg?rev=2052855\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.6\";}s:19:\"jetpack/jetpack.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:21:\"w.org/plugins/jetpack\";s:4:\"slug\";s:7:\"jetpack\";s:6:\"plugin\";s:19:\"jetpack/jetpack.php\";s:11:\"new_version\";s:6:\"13.4.3\";s:3:\"url\";s:38:\"https://wordpress.org/plugins/jetpack/\";s:7:\"package\";s:57:\"https://downloads.wordpress.org/plugin/jetpack.13.4.3.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:52:\"https://ps.w.org/jetpack/assets/icon.svg?rev=2819237\";s:3:\"svg\";s:52:\"https://ps.w.org/jetpack/assets/icon.svg?rev=2819237\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:63:\"https://ps.w.org/jetpack/assets/banner-1544x500.png?rev=2653649\";s:2:\"1x\";s:62:\"https://ps.w.org/jetpack/assets/banner-772x250.png?rev=2653649\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.4\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.0\";s:16:\"requires_plugins\";a:0:{}}s:21:\"mailpoet/mailpoet.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:22:\"w.org/plugins/mailpoet\";s:4:\"slug\";s:8:\"mailpoet\";s:6:\"plugin\";s:21:\"mailpoet/mailpoet.php\";s:11:\"new_version\";s:6:\"4.51.0\";s:3:\"url\";s:39:\"https://wordpress.org/plugins/mailpoet/\";s:7:\"package\";s:58:\"https://downloads.wordpress.org/plugin/mailpoet.4.51.0.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:61:\"https://ps.w.org/mailpoet/assets/icon-256x256.png?rev=2784430\";s:2:\"1x\";s:61:\"https://ps.w.org/mailpoet/assets/icon-128x128.png?rev=2784430\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/mailpoet/assets/banner-1544x500.png?rev=2046588\";s:2:\"1x\";s:63:\"https://ps.w.org/mailpoet/assets/banner-772x250.png?rev=2046588\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.4\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:49:\"post-meta-data-manager/post-meta-data-manager.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:36:\"w.org/plugins/post-meta-data-manager\";s:4:\"slug\";s:22:\"post-meta-data-manager\";s:6:\"plugin\";s:49:\"post-meta-data-manager/post-meta-data-manager.php\";s:11:\"new_version\";s:5:\"1.2.3\";s:3:\"url\";s:53:\"https://wordpress.org/plugins/post-meta-data-manager/\";s:7:\"package\";s:71:\"https://downloads.wordpress.org/plugin/post-meta-data-manager.1.2.3.zip\";s:5:\"icons\";a:1:{s:2:\"1x\";s:75:\"https://ps.w.org/post-meta-data-manager/assets/icon-128x128.png?rev=2589332\";}s:7:\"banners\";a:0:{}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:5:\"6.0.1\";}s:23:\"sql-buddy/sql-buddy.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:23:\"w.org/plugins/sql-buddy\";s:4:\"slug\";s:9:\"sql-buddy\";s:6:\"plugin\";s:23:\"sql-buddy/sql-buddy.php\";s:11:\"new_version\";s:5:\"1.0.0\";s:3:\"url\";s:40:\"https://wordpress.org/plugins/sql-buddy/\";s:7:\"package\";s:58:\"https://downloads.wordpress.org/plugin/sql-buddy.1.0.0.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:62:\"https://ps.w.org/sql-buddy/assets/icon-256x256.png?rev=2656583\";s:2:\"1x\";s:62:\"https://ps.w.org/sql-buddy/assets/icon-128x128.png?rev=2656583\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:65:\"https://ps.w.org/sql-buddy/assets/banner-1544x500.jpg?rev=2656583\";s:2:\"1x\";s:64:\"https://ps.w.org/sql-buddy/assets/banner-772x250.jpg?rev=2656583\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.3\";}s:43:\"the-events-calendar/the-events-calendar.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:33:\"w.org/plugins/the-events-calendar\";s:4:\"slug\";s:19:\"the-events-calendar\";s:6:\"plugin\";s:43:\"the-events-calendar/the-events-calendar.php\";s:11:\"new_version\";s:5:\"6.5.0\";s:3:\"url\";s:50:\"https://wordpress.org/plugins/the-events-calendar/\";s:7:\"package\";s:68:\"https://downloads.wordpress.org/plugin/the-events-calendar.6.5.0.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:72:\"https://ps.w.org/the-events-calendar/assets/icon-256x256.gif?rev=2516440\";s:2:\"1x\";s:72:\"https://ps.w.org/the-events-calendar/assets/icon-128x128.gif?rev=2516440\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:75:\"https://ps.w.org/the-events-calendar/assets/banner-1544x500.png?rev=2257622\";s:2:\"1x\";s:74:\"https://ps.w.org/the-events-calendar/assets/banner-772x250.png?rev=2257622\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.3\";}s:27:\"woocommerce/woocommerce.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:25:\"w.org/plugins/woocommerce\";s:4:\"slug\";s:11:\"woocommerce\";s:6:\"plugin\";s:27:\"woocommerce/woocommerce.php\";s:11:\"new_version\";s:5:\"8.9.1\";s:3:\"url\";s:42:\"https://wordpress.org/plugins/woocommerce/\";s:7:\"package\";s:60:\"https://downloads.wordpress.org/plugin/woocommerce.8.9.1.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/woocommerce/assets/icon-256x256.gif?rev=2869506\";s:2:\"1x\";s:64:\"https://ps.w.org/woocommerce/assets/icon-128x128.gif?rev=2869506\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/woocommerce/assets/banner-1544x500.png?rev=3000842\";s:2:\"1x\";s:66:\"https://ps.w.org/woocommerce/assets/banner-772x250.png?rev=3000842\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.4\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.4\";s:16:\"requires_plugins\";a:0:{}}s:27:\"wp-super-cache/wp-cache.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:28:\"w.org/plugins/wp-super-cache\";s:4:\"slug\";s:14:\"wp-super-cache\";s:6:\"plugin\";s:27:\"wp-super-cache/wp-cache.php\";s:11:\"new_version\";s:6:\"1.12.1\";s:3:\"url\";s:45:\"https://wordpress.org/plugins/wp-super-cache/\";s:7:\"package\";s:64:\"https://downloads.wordpress.org/plugin/wp-super-cache.1.12.1.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/wp-super-cache/assets/icon-256x256.png?rev=1095422\";s:2:\"1x\";s:67:\"https://ps.w.org/wp-super-cache/assets/icon-128x128.png?rev=1095422\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:70:\"https://ps.w.org/wp-super-cache/assets/banner-1544x500.png?rev=1082414\";s:2:\"1x\";s:69:\"https://ps.w.org/wp-super-cache/assets/banner-772x250.png?rev=1082414\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.4\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:3:\"7.0\";s:16:\"requires_plugins\";a:0:{}}s:33:\"duplicate-post/duplicate-post.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:28:\"w.org/plugins/duplicate-post\";s:4:\"slug\";s:14:\"duplicate-post\";s:6:\"plugin\";s:33:\"duplicate-post/duplicate-post.php\";s:11:\"new_version\";s:3:\"4.5\";s:3:\"url\";s:45:\"https://wordpress.org/plugins/duplicate-post/\";s:7:\"package\";s:61:\"https://downloads.wordpress.org/plugin/duplicate-post.4.5.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/duplicate-post/assets/icon-256x256.png?rev=2336666\";s:2:\"1x\";s:67:\"https://ps.w.org/duplicate-post/assets/icon-128x128.png?rev=2336666\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:70:\"https://ps.w.org/duplicate-post/assets/banner-1544x500.png?rev=2336666\";s:2:\"1x\";s:69:\"https://ps.w.org/duplicate-post/assets/banner-772x250.png?rev=2336666\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.3\";}s:24:\"wordpress-seo/wp-seo.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:27:\"w.org/plugins/wordpress-seo\";s:4:\"slug\";s:13:\"wordpress-seo\";s:6:\"plugin\";s:24:\"wordpress-seo/wp-seo.php\";s:11:\"new_version\";s:4:\"22.8\";s:3:\"url\";s:44:\"https://wordpress.org/plugins/wordpress-seo/\";s:7:\"package\";s:61:\"https://downloads.wordpress.org/plugin/wordpress-seo.22.8.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:58:\"https://ps.w.org/wordpress-seo/assets/icon.svg?rev=2363699\";s:3:\"svg\";s:58:\"https://ps.w.org/wordpress-seo/assets/icon.svg?rev=2363699\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/wordpress-seo/assets/banner-1544x500.png?rev=2643727\";s:2:\"1x\";s:68:\"https://ps.w.org/wordpress-seo/assets/banner-772x250.png?rev=2643727\";}s:11:\"banners_rtl\";a:2:{s:2:\"2x\";s:73:\"https://ps.w.org/wordpress-seo/assets/banner-1544x500-rtl.png?rev=2643727\";s:2:\"1x\";s:72:\"https://ps.w.org/wordpress-seo/assets/banner-772x250-rtl.png?rev=2643727\";}s:8:\"requires\";s:3:\"6.4\";s:6:\"tested\";s:5:\"6.5.3\";s:12:\"requires_php\";s:5:\"7.2.5\";s:16:\"requires_plugins\";a:0:{}}s:34:\"events-pro/events-calendar-pro.php\";O:8:\"stdClass\":8:{s:2:\"id\";s:37:\"stellarwp/plugins/events-calendar-pro\";s:6:\"plugin\";s:34:\"events-pro/events-calendar-pro.php\";s:4:\"slug\";s:19:\"events-calendar-pro\";s:11:\"new_version\";s:5:\"6.5.0\";s:3:\"url\";s:18:\"https://evnt.is/4d\";s:7:\"package\";s:296:\"https://pue.theeventscalendar.com/api/plugins/v2/download?plugin=events-calendar-pro&version=6.5.0&installed_version=6.5.0&domain=example.com&multisite=0&network_activated=0&active_sites=1&wp_version=6.3&key=97f261bb1246e1c8ad0b7ace9ec6cb7764a09730&dk=97f261bb1246e1c8ad0b7ace9ec6cb7764a09730&o=e\";s:14:\"upgrade_notice\";s:97:\"Important: Events Calendar PRO 6.5.0 is only compatible with The Events Calendar 6.5.0 and higher\";s:5:\"icons\";a:1:{s:3:\"svg\";s:84:\"https://theeventscalendar.com/content/themes/tribe-ecp/img/svg/product-icons/ECP.svg\";}}}}','no'),(209,'widget_tribe-widget-event-countdown','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(210,'widget_tribe-widget-featured-venue','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(211,'widget_tribe-widget-events-month','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(212,'widget_tribe-widget-events-week','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(213,'tec_ct1_series_relationship_table_schema_version','1.0.0','yes'),(214,'tec_ct1_events_field_schema_version','1.0.1','yes'),(215,'tec_ct1_occurrences_field_schema_version','1.0.1','yes'),(252,'recently_activated','a:0:{}','yes'),(259,'tec_timed_tribe_supports_async_process','','yes'),(262,'external_updates-events-calendar-pro','O:8:\"stdClass\":3:{s:9:\"lastCheck\";i:1717085540;s:14:\"checkedVersion\";s:5:\"6.5.0\";s:6:\"update\";O:19:\"Tribe__PUE__Utility\":13:{s:2:\"id\";i:0;s:6:\"plugin\";s:34:\"events-pro/events-calendar-pro.php\";s:4:\"slug\";s:19:\"events-calendar-pro\";s:7:\"version\";s:5:\"6.5.0\";s:8:\"homepage\";s:18:\"https://evnt.is/4d\";s:12:\"download_url\";s:296:\"https://pue.theeventscalendar.com/api/plugins/v2/download?plugin=events-calendar-pro&version=6.5.0&installed_version=6.5.0&domain=example.com&multisite=0&network_activated=0&active_sites=1&wp_version=6.3&key=97f261bb1246e1c8ad0b7ace9ec6cb7764a09730&dk=97f261bb1246e1c8ad0b7ace9ec6cb7764a09730&o=e\";s:8:\"sections\";O:8:\"stdClass\":3:{s:11:\"description\";s:204:\"Events Calendar Pro is an extension to The Events Calendar, and includes recurring events, additional frontend views and more. To see a full feature list please visit the product page (http://evnt.is/4d).\";s:12:\"installation\";s:353:\"Installing Events Calendar Pro is easy: just back up your site, download/install The Events Calendar from the WordPress.org repo, and download/install Events Calendar Pro from theeventscalendar.com. Activate them both and you\'ll be good to go! If you\'re still confused or encounter problems, check out part 1 of our new user primer (http://m.tri.be/4i).\";s:9:\"changelog\";s:514:\"<p>= [6.5.0] 2024-05-14 =</p>\r\n\r\n<ul>\r\n<li>Version - Events Calendar PRO 6.5.0 is only compatible with The Events Calendar 6.5.0 and higher.</li>\r\n<li>Fix - Upsell Notice for Event Tickets was not dismissible. [ECP-1685]</li>\r\n<li>Fix - Use Google geolocation API to get proper address data for Venues when importing Events via Google Calendars. [TEC-5007]</li>\r\n<li>Fix - Replace uses of the retired moment.js with Day.js [TEC-5011]</li>\r\n<li>Tweak - Removed filters: <code>tribe_distance_units</code></li>\r\n</ul>\";}s:14:\"upgrade_notice\";s:97:\"Important: Events Calendar PRO 6.5.0 is only compatible with The Events Calendar 6.5.0 and higher\";s:13:\"custom_update\";O:8:\"stdClass\":1:{s:5:\"icons\";O:8:\"stdClass\":1:{s:3:\"svg\";s:84:\"https://theeventscalendar.com/content/themes/tribe-ecp/img/svg/product-icons/ECP.svg\";}}s:13:\"license_error\";N;s:8:\"auth_url\";N;s:11:\"api_expired\";b:0;s:11:\"api_upgrade\";b:0;}}','no'),(263,'tribe_pue_key_notices','a:0:{}','yes'),(266,'_site_transient_update_themes','O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1717085541;s:7:\"checked\";a:4:{s:15:\"twentyseventeen\";s:3:\"3.2\";s:12:\"twentytwenty\";s:3:\"2.2\";s:16:\"twentytwentyfour\";s:3:\"1.0\";s:17:\"twentytwentythree\";s:3:\"1.1\";}s:8:\"response\";a:4:{s:15:\"twentyseventeen\";a:6:{s:5:\"theme\";s:15:\"twentyseventeen\";s:11:\"new_version\";s:3:\"3.6\";s:3:\"url\";s:45:\"https://wordpress.org/themes/twentyseventeen/\";s:7:\"package\";s:61:\"https://downloads.wordpress.org/theme/twentyseventeen.3.6.zip\";s:8:\"requires\";s:3:\"4.7\";s:12:\"requires_php\";s:5:\"5.2.4\";}s:12:\"twentytwenty\";a:6:{s:5:\"theme\";s:12:\"twentytwenty\";s:11:\"new_version\";s:3:\"2.6\";s:3:\"url\";s:42:\"https://wordpress.org/themes/twentytwenty/\";s:7:\"package\";s:58:\"https://downloads.wordpress.org/theme/twentytwenty.2.6.zip\";s:8:\"requires\";s:3:\"4.7\";s:12:\"requires_php\";s:5:\"5.2.4\";}s:16:\"twentytwentyfour\";a:6:{s:5:\"theme\";s:16:\"twentytwentyfour\";s:11:\"new_version\";s:3:\"1.1\";s:3:\"url\";s:46:\"https://wordpress.org/themes/twentytwentyfour/\";s:7:\"package\";s:62:\"https://downloads.wordpress.org/theme/twentytwentyfour.1.1.zip\";s:8:\"requires\";s:3:\"6.4\";s:12:\"requires_php\";s:3:\"7.0\";}s:17:\"twentytwentythree\";a:6:{s:5:\"theme\";s:17:\"twentytwentythree\";s:11:\"new_version\";s:3:\"1.4\";s:3:\"url\";s:47:\"https://wordpress.org/themes/twentytwentythree/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/theme/twentytwentythree.1.4.zip\";s:8:\"requires\";s:3:\"6.1\";s:12:\"requires_php\";s:3:\"5.6\";}}s:9:\"no_update\";a:0:{}s:12:\"translations\";a:0:{}}','no'),(307,'tec_timed_tec_custom_tables_v1_initialized','a:3:{s:3:\"key\";s:32:\"tec_custom_tables_v1_initialized\";s:5:\"value\";i:1;s:10:\"expiration\";i:1717169602;}','yes'),(308,'stellarwp_telemetry_last_send','','yes'),(309,'stellarwp_telemetry','a:1:{s:7:\"plugins\";a:1:{s:19:\"the-events-calendar\";a:2:{s:7:\"wp_slug\";s:43:\"the-events-calendar/the-events-calendar.php\";s:5:\"optin\";b:0;}}}','yes'),(310,'stellarwp_telemetry_the-events-calendar_show_optin','1','yes'),(311,'tec_timed_tec_custom_tables_v1_ecp_initialized','a:3:{s:3:\"key\";s:36:\"tec_custom_tables_v1_ecp_initialized\";s:5:\"value\";i:1;s:10:\"expiration\";i:1717169602;}','yes'),(328,'tec_automator_zapier_secret_key','bdf0d98b3d7cf2d7036dbc2304bbaeac25b6c356c095db5ce3d445ab49c08dd4ef652f64c362a3eba00ebde349ed6e047de6d28a85521002be75025d697447212b65e00508b8b69b92a3f116ae73ddfd617a4909f0fd0ca4cd407e5875cb447b917261f134dc62d80c081d8628cb5d22c2d721b50848f5a67509dc79515380f3','yes'),(329,'tec_automator_power_automate_secret_key','f3b71d1bc1dd476ca2e754835e4fc4e9295affa939eb3b380edcf0091520dc9d1d5346960702cd35cbfa15fbb93bfc087e02769f373b40eb1888fee08bddbd62ee39cf4e208a4fccf2e1ce0943e31d14bcb71cbaa3d6c7ad870efddc59b3676c57a71dc1bcb12ba61c6581ffe924427ec0621e9e490fb9d3903f667bb4dac662','yes'),(338,'tribe_events_cat_children','a:2:{i:6;a:1:{i:0;i:7;}i:7;a:1:{i:0;i:8;}}','yes'),(339,'tribe_events_calendar_options','a:10:{s:8:\"did_init\";b:1;s:19:\"tribeEventsTemplate\";s:0:\"\";s:16:\"tribeEnableViews\";a:3:{i:0;s:4:\"list\";i:1;s:5:\"month\";i:2;s:3:\"day\";}s:10:\"viewOption\";s:4:\"list\";s:14:\"schema-version\";s:5:\"6.6.0\";s:21:\"previous_ecp_versions\";a:1:{i:0;s:1:\"0\";}s:18:\"latest_ecp_version\";s:5:\"6.6.0\";s:18:\"dateWithYearFormat\";s:6:\"F j, Y\";s:24:\"recurrenceMaxMonthsAfter\";i:60;s:22:\"google_maps_js_api_key\";s:39:\"AIzaSyDNsicAsP6-VuGtAb1O9riI3oc_NOb7IOU\";}','yes'),(342,'_transient_doing_cron','1717085521.6219971179962158203125','yes'),(345,'_site_transient_update_core','O:8:\"stdClass\":4:{s:7:\"updates\";a:4:{i:0;O:8:\"stdClass\":10:{s:8:\"response\";s:7:\"upgrade\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.5.3.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.5.3.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-6.5.3-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-6.5.3-new-bundled.zip\";s:7:\"partial\";s:0:\"\";s:8:\"rollback\";s:0:\"\";}s:7:\"current\";s:5:\"6.5.3\";s:7:\"version\";s:5:\"6.5.3\";s:11:\"php_version\";s:5:\"7.0.0\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"6.4\";s:15:\"partial_version\";s:0:\"\";}i:1;O:8:\"stdClass\":11:{s:8:\"response\";s:10:\"autoupdate\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.5.3.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.5.3.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-6.5.3-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-6.5.3-new-bundled.zip\";s:7:\"partial\";s:0:\"\";s:8:\"rollback\";s:0:\"\";}s:7:\"current\";s:5:\"6.5.3\";s:7:\"version\";s:5:\"6.5.3\";s:11:\"php_version\";s:5:\"7.0.0\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"6.4\";s:15:\"partial_version\";s:0:\"\";s:9:\"new_files\";s:1:\"1\";}i:2;O:8:\"stdClass\":11:{s:8:\"response\";s:10:\"autoupdate\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.4.4.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.4.4.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-6.4.4-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-6.4.4-new-bundled.zip\";s:7:\"partial\";s:0:\"\";s:8:\"rollback\";s:0:\"\";}s:7:\"current\";s:5:\"6.4.4\";s:7:\"version\";s:5:\"6.4.4\";s:11:\"php_version\";s:5:\"7.0.0\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"6.4\";s:15:\"partial_version\";s:0:\"\";s:9:\"new_files\";s:1:\"1\";}i:3;O:8:\"stdClass\":11:{s:8:\"response\";s:10:\"autoupdate\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.3.4.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.3.4.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-6.3.4-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-6.3.4-new-bundled.zip\";s:7:\"partial\";s:69:\"https://downloads.wordpress.org/release/wordpress-6.3.4-partial-1.zip\";s:8:\"rollback\";s:70:\"https://downloads.wordpress.org/release/wordpress-6.3.4-rollback-1.zip\";}s:7:\"current\";s:5:\"6.3.4\";s:7:\"version\";s:5:\"6.3.4\";s:11:\"php_version\";s:5:\"7.0.0\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"6.4\";s:15:\"partial_version\";s:5:\"6.3.1\";s:9:\"new_files\";s:0:\"\";}}s:12:\"last_checked\";i:1717085542;s:15:\"version_checked\";s:5:\"6.3.1\";s:12:\"translations\";a:0:{}}','no'),(346,'admin_email_lifespan','1736368961','yes'),(347,'wp_attachment_pages_enabled','1','yes');
/*!40000 ALTER TABLE `wp_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_postmeta`
--

DROP TABLE IF EXISTS `wp_postmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_postmeta`
--

LOCK TABLES `wp_postmeta` WRITE;
/*!40000 ALTER TABLE `wp_postmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_postmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_posts`
--

DROP TABLE IF EXISTS `wp_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext NOT NULL,
  `post_title` text NOT NULL,
  `post_excerpt` text NOT NULL,
  `post_status` varchar(20) NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) NOT NULL DEFAULT 'open',
  `post_password` varchar(255) NOT NULL DEFAULT '',
  `post_name` varchar(200) NOT NULL DEFAULT '',
  `to_ping` text NOT NULL,
  `pinged` text NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `guid` varchar(255) NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `post_type` varchar(20) NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_posts`
--

LOCK TABLES `wp_posts` WRITE;
/*!40000 ALTER TABLE `wp_posts` DISABLE KEYS */;
INSERT INTO `wp_posts` VALUES (1,1,'2022-12-02 06:59:36','0000-00-00 00:00:00','','Auto Draft','','auto-draft','open','open','','','','','2022-12-02 06:59:36','0000-00-00 00:00:00','',0,'http://localhost:8888/?p=1',0,'post','',0);
/*!40000 ALTER TABLE `wp_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_tec_events`
--

DROP TABLE IF EXISTS `wp_tec_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_tec_events` (
  `event_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `start_date` varchar(19) NOT NULL,
  `end_date` varchar(19) DEFAULT NULL,
  `timezone` varchar(30) NOT NULL DEFAULT 'UTC',
  `start_date_utc` varchar(19) NOT NULL,
  `end_date_utc` varchar(19) DEFAULT NULL,
  `duration` mediumint(30) DEFAULT 7200,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `hash` varchar(40) NOT NULL,
  `rset` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_tec_events`
--

LOCK TABLES `wp_tec_events` WRITE;
/*!40000 ALTER TABLE `wp_tec_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_tec_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_tec_occurrences`
--

DROP TABLE IF EXISTS `wp_tec_occurrences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_tec_occurrences` (
  `occurrence_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `post_id` bigint(20) unsigned NOT NULL,
  `start_date` varchar(19) NOT NULL,
  `start_date_utc` varchar(19) NOT NULL,
  `end_date` varchar(19) NOT NULL,
  `end_date_utc` varchar(19) NOT NULL,
  `duration` mediumint(30) DEFAULT 7200,
  `hash` varchar(40) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `has_recurrence` tinyint(1) DEFAULT 0,
  `sequence` bigint(20) unsigned DEFAULT 0,
  `is_rdate` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`occurrence_id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `wp_tec_occurrences_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `wp_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `wp_tec_occurrences_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `wp_tec_events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `wp_tec_occurrences_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `wp_tec_events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_tec_occurrences`
--

LOCK TABLES `wp_tec_occurrences` WRITE;
/*!40000 ALTER TABLE `wp_tec_occurrences` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_tec_occurrences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_tec_series_relationships`
--

DROP TABLE IF EXISTS `wp_tec_series_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_tec_series_relationships` (
  `relationship_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `series_post_id` bigint(20) unsigned NOT NULL,
  `event_id` bigint(20) unsigned NOT NULL,
  `event_post_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`relationship_id`),
  KEY `series_post_id` (`series_post_id`),
  KEY `event_post_id` (`event_post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_tec_series_relationships`
--

LOCK TABLES `wp_tec_series_relationships` WRITE;
/*!40000 ALTER TABLE `wp_tec_series_relationships` DISABLE KEYS */;
INSERT INTO `wp_tec_series_relationships` VALUES (1,5,4,4),(2,7,6,6);
/*!40000 ALTER TABLE `wp_tec_series_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_term_relationships`
--

DROP TABLE IF EXISTS `wp_term_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_term_relationships`
--

LOCK TABLES `wp_term_relationships` WRITE;
/*!40000 ALTER TABLE `wp_term_relationships` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_term_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_term_taxonomy`
--

DROP TABLE IF EXISTS `wp_term_taxonomy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `taxonomy` varchar(32) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_term_taxonomy`
--

LOCK TABLES `wp_term_taxonomy` WRITE;
/*!40000 ALTER TABLE `wp_term_taxonomy` DISABLE KEYS */;
INSERT INTO `wp_term_taxonomy` VALUES (1,1,'category','',0,0);
/*!40000 ALTER TABLE `wp_term_taxonomy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_termmeta`
--

DROP TABLE IF EXISTS `wp_termmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_termmeta`
--

LOCK TABLES `wp_termmeta` WRITE;
/*!40000 ALTER TABLE `wp_termmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_termmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_terms`
--

DROP TABLE IF EXISTS `wp_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `slug` varchar(200) NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_terms`
--

LOCK TABLES `wp_terms` WRITE;
/*!40000 ALTER TABLE `wp_terms` DISABLE KEYS */;
INSERT INTO `wp_terms` VALUES (1,'Uncategorized','uncategorized',0);
/*!40000 ALTER TABLE `wp_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_usermeta`
--

DROP TABLE IF EXISTS `wp_usermeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_usermeta`
--

LOCK TABLES `wp_usermeta` WRITE;
/*!40000 ALTER TABLE `wp_usermeta` DISABLE KEYS */;
INSERT INTO `wp_usermeta` VALUES (1,1,'nickname','admin'),(2,1,'first_name',''),(3,1,'last_name',''),(4,1,'description',''),(5,1,'rich_editing','true'),(6,1,'syntax_highlighting','true'),(7,1,'comment_shortcuts','false'),(8,1,'admin_color','fresh'),(9,1,'use_ssl','0'),(10,1,'show_admin_bar_front','true'),(11,1,'locale',''),(12,1,'wp_capabilities','a:1:{s:13:\"administrator\";b:1;}'),(13,1,'wp_user_level','10'),(14,1,'dismissed_wp_pointers',''),(15,1,'show_welcome_panel','1'),(16,1,'session_tokens','a:1:{s:64:\"ab304bd4cd8b5d8f2792e6f438b274e37f9d50a9665a5df0eba575275f47c140\";a:4:{s:10:\"expiration\";i:1670411067;s:2:\"ip\";s:10:\"172.19.0.1\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Safari/605.1.15\";s:5:\"login\";i:1670238267;}}'),(17,1,'wp_user-settings','mfold=o'),(18,1,'wp_user-settings-time','1669993170'),(19,1,'wp_dashboard_quick_press_last_post_id','1'),(20,1,'community-events-location','a:1:{s:2:\"ip\";s:10:\"172.19.0.0\";}'),(37,2,'session_tokens','a:1:{s:64:\"2cdf01cd22a230009d298bbb40b090dfcf04d43e98d8358befe2af2d99732bf5\";a:4:{s:10:\"expiration\";i:1717256002;s:2:\"ip\";s:10:\"172.28.0.6\";s:2:\"ua\";s:18:\"Symfony BrowserKit\";s:5:\"login\";i:1717083202;}}'),(54,3,'session_tokens','a:1:{s:64:\"b8ea179cb2f4bea8bb31129bd7ea6d9b98398ed22a721ed7eab9c35ee15a3985\";a:4:{s:10:\"expiration\";i:1717257891;s:2:\"ip\";s:10:\"172.28.0.6\";s:2:\"ua\";s:18:\"Symfony BrowserKit\";s:5:\"login\";i:1717085091;}}');
/*!40000 ALTER TABLE `wp_usermeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_users`
--

DROP TABLE IF EXISTS `wp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) NOT NULL DEFAULT '',
  `user_pass` varchar(255) NOT NULL DEFAULT '',
  `user_nicename` varchar(50) NOT NULL DEFAULT '',
  `user_email` varchar(100) NOT NULL DEFAULT '',
  `user_url` varchar(100) NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT 0,
  `display_name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_users`
--

LOCK TABLES `wp_users` WRITE;
/*!40000 ALTER TABLE `wp_users` DISABLE KEYS */;
INSERT INTO `wp_users` VALUES (1,'admin','$P$Bbi7rKX5wWlVNoZRWKY8n6gduwL0rE0','admin','admin@wordpress.test','http://example.com','2020-08-19 12:05:40','',0,'admin');
/*!40000 ALTER TABLE `wp_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_api_keys`
--

DROP TABLE IF EXISTS `wp_woocommerce_api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_api_keys` (
  `key_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `description` longtext DEFAULT NULL,
  `permissions` varchar(10) NOT NULL,
  `consumer_key` char(64) NOT NULL,
  `consumer_secret` char(43) NOT NULL,
  `nonces` longtext DEFAULT NULL,
  `truncated_key` char(7) NOT NULL,
  `last_access` datetime DEFAULT NULL,
  PRIMARY KEY (`key_id`),
  KEY `consumer_key` (`consumer_key`),
  KEY `consumer_secret` (`consumer_secret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_api_keys`
--

LOCK TABLES `wp_woocommerce_api_keys` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_api_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_attribute_taxonomies`
--

DROP TABLE IF EXISTS `wp_woocommerce_attribute_taxonomies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_attribute_taxonomies` (
  `attribute_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `attribute_name` varchar(200) NOT NULL,
  `attribute_label` longtext DEFAULT NULL,
  `attribute_type` varchar(200) NOT NULL,
  `attribute_orderby` varchar(200) NOT NULL,
  `attribute_public` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`attribute_id`),
  KEY `attribute_name` (`attribute_name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_attribute_taxonomies`
--

LOCK TABLES `wp_woocommerce_attribute_taxonomies` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_attribute_taxonomies` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_attribute_taxonomies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_downloadable_product_permissions`
--

DROP TABLE IF EXISTS `wp_woocommerce_downloadable_product_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_downloadable_product_permissions` (
  `permission_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `download_id` varchar(32) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL DEFAULT 0,
  `order_key` varchar(200) NOT NULL,
  `user_email` varchar(200) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `downloads_remaining` varchar(9) DEFAULT NULL,
  `access_granted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access_expires` datetime DEFAULT NULL,
  `download_count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`permission_id`),
  KEY `download_order_key_product` (`product_id`,`order_id`,`order_key`(191),`download_id`),
  KEY `download_order_product` (`download_id`,`order_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_downloadable_product_permissions`
--

LOCK TABLES `wp_woocommerce_downloadable_product_permissions` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_downloadable_product_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_downloadable_product_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_order_itemmeta`
--

DROP TABLE IF EXISTS `wp_woocommerce_order_itemmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_order_itemmeta` (
  `meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint(20) NOT NULL,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_order_itemmeta`
--

LOCK TABLES `wp_woocommerce_order_itemmeta` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_order_itemmeta` DISABLE KEYS */;
INSERT INTO `wp_woocommerce_order_itemmeta` VALUES (1,1,'_qty','4'),(2,1,'_tax_class',''),(3,1,'_product_id','17'),(4,1,'_variation_id','0'),(5,1,'_line_subtotal','0'),(6,1,'_line_total','0'),(7,1,'_line_subtotal_tax','0'),(8,1,'_line_tax','0'),(9,1,'_line_tax_data','a:2:{s:5:\"total\";a:0:{}s:8:\"subtotal\";a:0:{}}'),(10,1,'_tribe_wooticket_attendee_optout','');
/*!40000 ALTER TABLE `wp_woocommerce_order_itemmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_order_items`
--

DROP TABLE IF EXISTS `wp_woocommerce_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_order_items` (
  `order_item_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_item_name` longtext NOT NULL,
  `order_item_type` varchar(200) NOT NULL DEFAULT '',
  `order_id` bigint(20) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_order_items`
--

LOCK TABLES `wp_woocommerce_order_items` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_order_items` DISABLE KEYS */;
INSERT INTO `wp_woocommerce_order_items` VALUES (1,'Event 002 Woo Ticket 001','line_item',19);
/*!40000 ALTER TABLE `wp_woocommerce_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_payment_tokenmeta`
--

DROP TABLE IF EXISTS `wp_woocommerce_payment_tokenmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_payment_tokenmeta` (
  `meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `payment_token_id` bigint(20) NOT NULL,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `payment_token_id` (`payment_token_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_payment_tokenmeta`
--

LOCK TABLES `wp_woocommerce_payment_tokenmeta` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_payment_tokenmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_payment_tokenmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_payment_tokens`
--

DROP TABLE IF EXISTS `wp_woocommerce_payment_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_payment_tokens` (
  `token_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `gateway_id` varchar(255) NOT NULL,
  `token` text NOT NULL,
  `user_id` bigint(20) NOT NULL DEFAULT 0,
  `type` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`token_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_payment_tokens`
--

LOCK TABLES `wp_woocommerce_payment_tokens` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_payment_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_payment_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_sessions`
--

DROP TABLE IF EXISTS `wp_woocommerce_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_sessions` (
  `session_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `session_key` char(32) NOT NULL,
  `session_value` longtext NOT NULL,
  `session_expiry` bigint(20) NOT NULL,
  PRIMARY KEY (`session_key`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_sessions`
--

LOCK TABLES `wp_woocommerce_sessions` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_sessions` DISABLE KEYS */;
INSERT INTO `wp_woocommerce_sessions` VALUES (7,'1','a:4:{s:21:\"removed_cart_contents\";s:6:\"a:0:{}\";s:14:\"shipping_total\";N;s:21:\"chosen_payment_method\";s:0:\"\";s:8:\"customer\";s:439:\"a:14:{s:8:\"postcode\";s:5:\"75003\";s:4:\"city\";s:5:\"Paris\";s:9:\"address_1\";s:19:\"100, rue de turenne\";s:9:\"address_2\";s:0:\"\";s:5:\"state\";s:0:\"\";s:7:\"country\";s:2:\"FR\";s:17:\"shipping_postcode\";s:5:\"75003\";s:13:\"shipping_city\";s:5:\"Paris\";s:18:\"shipping_address_1\";s:19:\"100, rue de turenne\";s:18:\"shipping_address_2\";s:0:\"\";s:14:\"shipping_state\";s:0:\"\";s:16:\"shipping_country\";s:2:\"FR\";s:13:\"is_vat_exempt\";b:0;s:19:\"calculated_shipping\";b:1;}\";}',1471707176);
/*!40000 ALTER TABLE `wp_woocommerce_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_shipping_zone_locations`
--

DROP TABLE IF EXISTS `wp_woocommerce_shipping_zone_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_shipping_zone_locations` (
  `location_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `zone_id` bigint(20) NOT NULL,
  `location_code` varchar(255) NOT NULL,
  `location_type` varchar(40) NOT NULL,
  PRIMARY KEY (`location_id`),
  KEY `location_id` (`location_id`),
  KEY `location_type` (`location_type`),
  KEY `location_type_code` (`location_type`,`location_code`(90))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_shipping_zone_locations`
--

LOCK TABLES `wp_woocommerce_shipping_zone_locations` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zone_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zone_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_shipping_zone_methods`
--

DROP TABLE IF EXISTS `wp_woocommerce_shipping_zone_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_shipping_zone_methods` (
  `zone_id` bigint(20) NOT NULL,
  `instance_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `method_id` varchar(255) NOT NULL,
  `method_order` bigint(20) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_shipping_zone_methods`
--

LOCK TABLES `wp_woocommerce_shipping_zone_methods` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zone_methods` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zone_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_shipping_zones`
--

DROP TABLE IF EXISTS `wp_woocommerce_shipping_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_shipping_zones` (
  `zone_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `zone_name` varchar(255) NOT NULL,
  `zone_order` bigint(20) NOT NULL,
  PRIMARY KEY (`zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_shipping_zones`
--

LOCK TABLES `wp_woocommerce_shipping_zones` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_tax_rate_locations`
--

DROP TABLE IF EXISTS `wp_woocommerce_tax_rate_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_tax_rate_locations` (
  `location_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `location_code` varchar(255) NOT NULL,
  `tax_rate_id` bigint(20) NOT NULL,
  `location_type` varchar(40) NOT NULL,
  PRIMARY KEY (`location_id`),
  KEY `tax_rate_id` (`tax_rate_id`),
  KEY `location_type` (`location_type`),
  KEY `location_type_code` (`location_type`,`location_code`(90))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_tax_rate_locations`
--

LOCK TABLES `wp_woocommerce_tax_rate_locations` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_tax_rate_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_tax_rate_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_tax_rates`
--

DROP TABLE IF EXISTS `wp_woocommerce_tax_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_woocommerce_tax_rates` (
  `tax_rate_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tax_rate_country` varchar(200) NOT NULL DEFAULT '',
  `tax_rate_state` varchar(200) NOT NULL DEFAULT '',
  `tax_rate` varchar(200) NOT NULL DEFAULT '',
  `tax_rate_name` varchar(200) NOT NULL DEFAULT '',
  `tax_rate_priority` bigint(20) NOT NULL,
  `tax_rate_compound` int(1) NOT NULL DEFAULT 0,
  `tax_rate_shipping` int(1) NOT NULL DEFAULT 1,
  `tax_rate_order` bigint(20) NOT NULL,
  `tax_rate_class` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`tax_rate_id`),
  KEY `tax_rate_country` (`tax_rate_country`(191)),
  KEY `tax_rate_state` (`tax_rate_state`(191)),
  KEY `tax_rate_class` (`tax_rate_class`(191)),
  KEY `tax_rate_priority` (`tax_rate_priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_tax_rates`
--

LOCK TABLES `wp_woocommerce_tax_rates` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_tax_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_tax_rates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-12-23 12:55:03
