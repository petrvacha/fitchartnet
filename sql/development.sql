SET NAMES utf8mb4;
SET time_zone = '-01:00';

USE `test`;

CREATE TABLE `gender` (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `role` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `privacy` (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `log_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `mark` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `facebook_id` bigint(20) DEFAULT NULL,
  `firstname` varchar(30) DEFAULT NULL,
  `surname` varchar(30) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `gender_id` tinyint(1) DEFAULT NULL,
  `role_id` tinyint(2) DEFAULT NULL,
  `privacy_id` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `last_action` datetime DEFAULT NULL,
  `token` varchar(50) DEFAULT NULL,
  `facebook_access_token` varchar(50) DEFAULT NULL,
  `api_token` varchar(6) DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `state` varchar(20) NOT NULL,
  `profile_photo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facebook_id` (`facebook_id`),
  KEY `gender_id` (`gender_id`),
  KEY `role_id` (`role_id`),
  KEY `privacy_id` (`privacy_id`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`gender_id`) REFERENCES `gender` (`id`),
  CONSTRAINT `user_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`),
  CONSTRAINT `user_ibfk_3` FOREIGN KEY (`privacy_id`) REFERENCES `privacy` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_type_id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `description` varchar(255) DEFAULT NULL, -- Opraveno na varchar(255)
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `log_type_id` (`log_type_id`),
  CONSTRAINT `activity_ibfk_1` FOREIGN KEY (`log_type_id`) REFERENCES `log_type` (`id`) -- Opravený cizí klíč
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `activity_id` (`activity_id`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`), -- Opravený cizí klíč
  CONSTRAINT `activity_log_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activity` (`id`) -- Opravený cizí klíč
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `challenge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `final_value` int(11) NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `activity_id` int(11) NOT NULL,
  `challenge_photo` varchar(50) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `challenge_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activity` (`id`), -- Opravený cizí klíč
  CONSTRAINT `challenge_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) -- Opravený cizí klíč
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `challenge_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `challenge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invited_by` int(11) NOT NULL,
  `invited_at` datetime NOT NULL,
  `color` varchar(7) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `challenge_id` (`challenge_id`),
  KEY `user_id` (`user_id`),
  KEY `invited_by` (`invited_by`),
  CONSTRAINT `challenge_user_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`id`), -- Opravený cizí klíč
  CONSTRAINT `challenge_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`), -- Opravený cizí klíč
  CONSTRAINT `challenge_user_ibfk_3` FOREIGN KEY (`invited_by`) REFERENCES `user` (`id`) -- Opravený cizí klíč
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `weight` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `value` int NOT NULL,
  `datetime` datetime NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
);

CREATE TABLE `notification` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  `message` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
);

CREATE TABLE `friend` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `user_id2` int(11) NOT NULL,
  `created_at` datetime NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  FOREIGN KEY (`user_id2`) REFERENCES `user` (`id`)
);

CREATE TABLE `friendship_request` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `approved` tinyint(1) NULL,
  `to_user_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  FOREIGN KEY (`to_user_id`) REFERENCES `user` (`id`),
  FOREIGN KEY (`from_user_id`) REFERENCES `user` (`id`)
);

USE `test`;

INSERT INTO `log_type` (`id`, `name`, `description`, `mark`) VALUES
(1,	'count',	'',	''),
(2,	'time',	'in seconds',	's');

INSERT INTO `activity` (`id`, `log_type_id`, `name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1,	1,	'push up',	NULL,	1,	'2015-08-23 17:51:45',	'2015-08-23 17:51:45'),
(2,	1,	'pull up',	NULL,	1,	'2015-10-21 20:47:08',	'2015-10-21 20:47:08'),
(3,	1,	'squat',	NULL,	1,	'2015-10-21 20:47:52',	'2015-10-21 20:47:52'),
(4,	2,	'plank',	NULL,	1,	'2015-10-21 20:49:40',	'2015-10-21 20:49:40'),
(5,	2,	'handstand',	NULL,	1,	'2015-10-21 20:51:51',	'2015-10-21 20:51:51'),
(6,	2,	'headstand',	NULL,	1,	'2015-10-21 20:52:19',	'2015-10-21 20:52:19'),
(7,	1,	'sit up',	NULL,	1,	'2015-10-21 20:53:44',	'2015-10-21 20:53:44'),
(8,	2,	'wall squat',	NULL,	1,	'2015-10-21 21:02:10',	'2015-10-21 21:02:10'),
(9,	1,	'raising legs',	NULL,	1,	'2016-01-25 21:19:47',	'2016-01-25 21:19:47'),
(10,	1,	'kettlebell swing',	NULL,	1,	'2016-02-03 10:03:18',	'2016-02-03 10:03:18'),
(11,	2,	'heat',	NULL,	1,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(13,	1,	'CORE - side kick',	NULL,	1,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(14,	1,	'burpee',	NULL,	1,	'2017-06-28 12:20:46',	'2017-06-28 12:20:46'),
(15,	1,	'bench dip',	NULL,	1,	'2020-01-03 13:25:42',	'2020-01-03 13:25:42'),
(16,	1,	'rope skipping',	NULL,	1,	'2020-03-30 23:15:04',	'2020-03-30 23:15:04'),
(17,	1,	'mountain climber',	NULL,	1,	'2021-01-15 09:59:59',	'2021-01-15 09:59:59'),
(18,	1,	'bulgarian squat',	NULL,	1,	'2021-02-02 22:36:05',	'2021-02-02 22:36:05');

INSERT INTO `gender` (`id`, `name`) VALUES
(1,	'male'),
(2,	'female');

INSERT INTO `role` (`id`, `name`, `description`, `active`) VALUES
(1,	'superadmin',	'',	1),
(2,	'admin',	'',	1),
(3,	'moderator',	'',	1),
(4,	'user',	'',	1),
(5,	'spectator',	'',	1);

INSERT INTO `privacy` (`id`, `name`, `description`, `active`) VALUES
(1,	'strict private',	'',	1),
(2,	'only friends',	'',	1),
(3,	'friends and groups',	';)',	1),
(4,	'public in system',	'',	1),
(5,	'public for all',	'',	1);

--- user:test, password:123456
INSERT INTO `user` (`id`, `facebook_id`, `firstname`, `surname`, `email`, `username`, `gender_id`, `role_id`, `privacy_id`, `created_at`, `updated_at`, `last_action`, `token`, `facebook_access_token`, `api_token`, `bio`, `active`, `password`, `state`, `profile_photo`) VALUES
(1, NULL, NULL, NULL, 'test@test.com', 'test', NULL, 4, 3, '2024-12-23 13:00:55', '2024-12-23 13:00:55', NULL, 'e6b163ea799166eb3ade68e2065fba55bac95099', NULL, '537ec3', NULL, 0, '$2y$10$ahBK33jMrUVmFqx/aRa8ruOxswLBhu42K0ZmLLKQZ48ba5w3jxZaG', 'new', NULL);