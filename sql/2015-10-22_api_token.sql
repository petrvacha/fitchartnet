ALTER TABLE `user`
ADD `api_token` varchar(6) COLLATE 'utf8_unicode_ci' NULL AFTER `token`;