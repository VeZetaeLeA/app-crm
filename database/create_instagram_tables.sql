CREATE TABLE IF NOT EXISTS `instagram_calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `week_label` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `status` varchar(50) DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `instagram_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calendar_id` int(11) NOT NULL,
  `day_of_week` varchar(20) NOT NULL,
  `publish_date` date NOT NULL,
  `publish_time` time NOT NULL,
  `strategic_pilar` varchar(100) NOT NULL,
  `post_format` varchar(50) NOT NULL,
  `internal_title` varchar(255) NOT NULL,
  `copy_text` text NOT NULL,
  `cta_text` varchar(255) NOT NULL,
  `hashtags` text NOT NULL,
  `visual_prompt` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `calendar_id` (`calendar_id`),
  CONSTRAINT `instagram_posts_ibfk_1` FOREIGN KEY (`calendar_id`) REFERENCES `instagram_calendar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
