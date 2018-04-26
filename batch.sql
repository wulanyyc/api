

ALTER TABLE `biaoye`.`customer` 
ADD COLUMN `money` FLOAT NULL DEFAULT 0 AFTER `room_id`;


ALTER TABLE `biaoye`.`customer_order` 
ADD COLUMN `pay_wallet` FLOAT NULL DEFAULT 0 AFTER `pay_money`;


ALTER TABLE `biaoye`.`customer_pay` 
CHANGE COLUMN `discount_money` `wallet_money` FLOAT NOT NULL DEFAULT '0' COMMENT '钱包支付' ;


ALTER TABLE `biaoye`.`customer` 
ADD COLUMN `sex` TINYINT(1) NULL DEFAULT 0 COMMENT '0: 男  1: 女' AFTER `room_id`;



ALTER TABLE `biaoye`.`agent_inventory_records` 
ADD COLUMN `date` INT NULL AFTER `memo`;


