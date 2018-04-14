ALTER TABLE `biaoye`.`agent_inventory_records` 
CHANGE COLUMN `memo` `memo` VARCHAR(100) NULL DEFAULT '' AFTER `agent_id`,
CHANGE COLUMN `product_list` `product_id` INT UNSIGNED NOT NULL ,
CHANGE COLUMN `operator_flag` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0: 进行中  1: 已完成  2: 撤销' ,
CHANGE COLUMN `operator_id` `agent_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '操作人' ;


ALTER TABLE `biaoye`.`agent_inventory_records` 
ADD COLUMN `batch_id` INT UNSIGNED NOT NULL AFTER `agent_id`;


ALTER TABLE `biaoye`.`company_inventory_records` 
CHANGE COLUMN `operator_id` `agent_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '操作人' AFTER `product_id`,
CHANGE COLUMN `operator_flag` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0: 进行中  1: 已完成  2: 撤销' AFTER `agent_id`,
CHANGE COLUMN `product_list` `product_id` INT UNSIGNED NOT NULL ;


ALTER TABLE `biaoye`.`company_inventory_records` 
ADD COLUMN `batch_id` INT UNSIGNED NOT NULL AFTER `status`;
