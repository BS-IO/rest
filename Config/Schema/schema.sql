DROP TABLE IF EXISTS `api_applications`;

CREATE TABLE `api_applications` (
	`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
	`name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
	`secret` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,	PRIMARY KEY  (`id`)) 	DEFAULT CHARSET=utf8,
	COLLATE=utf8_unicode_ci,
	ENGINE=InnoDB;



