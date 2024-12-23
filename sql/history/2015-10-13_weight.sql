CREATE TABLE `weight` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `value` int NOT NULL,
  `datetime` datetime NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
);