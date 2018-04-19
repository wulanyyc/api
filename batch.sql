CREATE TABLE `school_product_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category` int(11) unsigned NOT NULL,
  `sub_category` int(11) unsigned NOT NULL,
  `name` varchar(45) NOT NULL,
  `price` float NOT NULL,
  `market_price` float unsigned NOT NULL,
  `title` varchar(45) NOT NULL,
  `slogan` varchar(45) NOT NULL DEFAULT '',
  `brand` varchar(45) NOT NULL DEFAULT '' COMMENT '品牌',
  `img` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) DEFAULT '0' COMMENT '1: 上线  2: 下线  3: 删除 ',
  `sale_num` int(11) DEFAULT '0',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  KEY `category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


ALTER TABLE `biaoye`.`product_list` 
DROP INDEX `status` ;


 CREATE TABLE `coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1: 系统券  2: 用户券 3: 私人券 4: 活动券',
  `money` float NOT NULL DEFAULT '0',
  `day` int(11) NOT NULL DEFAULT '0' COMMENT '有效期',
  `desc` varchar(45) DEFAULT NULL,
  `money_limit` varchar(45) DEFAULT '0',
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8


CREATE TABLE `customer_coupon_use` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `cid` varchar(45) NOT NULL,
  `use_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1: 未使用  2: 已使用',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer` (`customer_id`),
  KEY `coupon_id` (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;