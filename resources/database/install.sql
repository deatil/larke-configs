DROP TABLE IF EXISTS `pre__configs`;
CREATE TABLE `pre__configs` (
  `id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `key` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
