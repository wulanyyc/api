

ALTER TABLE `biaoye`.`customer_order` 
CHANGE COLUMN `product_price` `product_price` FLOAT UNSIGNED NOT NULL DEFAULT '0' ,
CHANGE COLUMN `pay_money` `pay_money` FLOAT UNSIGNED NOT NULL ,
CHANGE COLUMN `express_time` `express_time` TIMESTAMP NOT NULL ,
ADD COLUMN `salary` FLOAT UNSIGNED NOT NULL DEFAULT 0 AFTER `express_time`;


ALTER TABLE `biaoye`.`customer_order` 
CHANGE COLUMN `express_time` `express_time` DATETIME NOT NULL ;


