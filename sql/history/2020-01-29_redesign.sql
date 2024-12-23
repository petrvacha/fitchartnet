ALTER TABLE `user`
CHANGE `active` `active` tinyint(1) NULL AFTER `bio`,
CHANGE `profile_photo` `profile_photo` varchar(20) COLLATE 'utf8_unicode_ci' NULL AFTER `state`;