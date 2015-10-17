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