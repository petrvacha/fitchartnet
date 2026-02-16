CREATE TABLE `group` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `telegram_group_id` varchar(255) DEFAULT NULL,
  `bot_token` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  FOREIGN KEY (`admin_user_id`) REFERENCES `user` (`id`)
);

CREATE TABLE `group_user` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invited_by` int(11) NOT NULL,
  `invited_at` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  UNIQUE KEY `unique_group_user` (`group_id`, `user_id`),
  FOREIGN KEY (`group_id`) REFERENCES `group` (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  FOREIGN KEY (`invited_by`) REFERENCES `user` (`id`)
);
