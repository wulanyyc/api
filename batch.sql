ALTER TABLE `biaoye`.`product_list` 
CHANGE COLUMN `slogan` `slogan` VARCHAR(45) NOT NULL DEFAULT '' ,
CHANGE COLUMN `unit` `weight` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '规格' ,
CHANGE COLUMN `img` `img` VARCHAR(255) NOT NULL DEFAULT '' ,
ADD COLUMN `market_price` FLOAT NOT NULL AFTER `price`,
ADD COLUMN `brand` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '品牌' AFTER `slogan`,
ADD COLUMN `valid_date` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '保质期' AFTER `brand`,
ADD COLUMN `place` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '产地' AFTER `valid_date`,
ADD COLUMN `province` VARCHAR(45) NOT NULL COMMENT '省份' AFTER `place`,
ADD COLUMN `package` VARCHAR(45) NOT NULL COMMENT '包装方式' AFTER `province`;



CREATE TABLE `agent_order_list` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `agent_id` bigint(20) unsigned NOT NULL,
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '0: 失败  1: 成功',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
