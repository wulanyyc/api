


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
