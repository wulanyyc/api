CREATE TABLE `biaoye`.`product_category` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `parent_id` INT NULL DEFAULT 0,
  `status` TINYINT(1) NULL DEFAULT 0 COMMENT '0: 有效  1: 删除',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC));


ALTER TABLE `biaoye`.`product_list` 
CHANGE COLUMN `category` `category` INT NOT NULL ,
ADD COLUMN `sub_category` INT NOT NULL AFTER `category`;


ALTER TABLE `biaoye`.`customer_address` 
ADD COLUMN `sex` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: 男 1: 女' AFTER `rec_room`;
