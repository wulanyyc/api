ALTER TABLE `biaoye`.`customer_order` 
CHANGE COLUMN `express_date` `express_time` TIMESTAMP NULL ;


ALTER TABLE `biaoye`.`customer_order` 
CHANGE COLUMN `express_fee` `express_fee` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
CHANGE COLUMN `express_time` `express_time` TIMESTAMP NOT NULL ,
CHANGE COLUMN `date` `date` INT(11) UNSIGNED NULL ;


ALTER TABLE `biaoye`.`customer_order` 
CHANGE COLUMN `status` `status` TINYINT(1) NULL DEFAULT '1' COMMENT '1: 待支付  2: 已支付  3: 已抢单 4: 已完成  5: 已取消' ;


ALTER TABLE `biaoye`.`customer_cart` 
DROP COLUMN `product_price`;


ALTER TABLE `biaoye`.`customer_order` 
DROP COLUMN `rec_address`,
DROP COLUMN `rec_phone`,
DROP COLUMN `rec_name`,
CHANGE COLUMN `express_time` `express_time` TIMESTAMP NOT NULL ;
