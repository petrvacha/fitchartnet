CREATE TABLE `notification` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  `message` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
);