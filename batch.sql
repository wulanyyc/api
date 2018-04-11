ALTER TABLE `biaoye`.`product_list` 
CHANGE COLUMN `img` `img` VARCHAR(255) NULL DEFAULT '' ;

ALTER TABLE `biaoye`.`product_list` 
DROP COLUMN `deleteflag`;


ALTER TABLE `biaoye`.`agent` 
CHANGE COLUMN `status` `status` TINYINT(1) NULL DEFAULT '0' COMMENT '0: 审核中  1: 有效  2: 审核失败  3: 禁用 ' ;





