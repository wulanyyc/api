CREATE TABLE `notify_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  `message` tinytext NOT NULL,
  `date` int(11) NOT NULL,
  `terminal` tinyint(1) DEFAULT '0' COMMENT '0: app 1: 商城',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


CREATE TABLE `agent_money_list` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint(20) unsigned NOT NULL,
  `money` float NOT NULL,
  `operator` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0: 销售收入   1: 提现  2: 奖励   3:后台操作',
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `date` int(11) NOT NULL,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;