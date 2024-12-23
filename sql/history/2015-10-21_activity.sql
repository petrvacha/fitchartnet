ALTER TABLE `log_type`
CHANGE `description` `description` varchar(255) COLLATE 'utf8_general_ci' NULL AFTER `name`,
ADD `mark` varchar(10) COLLATE 'utf8_general_ci' NULL;