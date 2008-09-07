ALTER TABLE `orders` ADD `subscription` tinyint default 0;
ALTER TABLE `orders` ADD `last_order` datetime default '0001-01-01 00:00:00';
ALTER TABLE `orders` ADD `subscription_schedule` varchar(32) default '';
ALTER TABLE `orders` ADD `subscription_order_id` int(11) default 0;
