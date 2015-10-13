ALTER TABLE `user`
ADD `facebook_id` bigint(20) NULL AFTER `id`,
ADD `facebook_access_token` varchar(50) COLLATE 'utf8_unicode_ci' NULL AFTER `token`;