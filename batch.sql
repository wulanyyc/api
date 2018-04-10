ALTER TABLE `biaoye`.`product_list` 
CHANGE COLUMN `desc` `title` VARCHAR(45) NOT NULL ,
ADD COLUMN `slogan` VARCHAR(45) NULL AFTER `title`;

ALTER TABLE `biaoye`.`product_list` 
CHANGE COLUMN `slogan` `slogan` VARCHAR(45) NULL DEFAULT '' ,
CHANGE COLUMN `unit` `unit` VARCHAR(45) NULL DEFAULT '' ;

ALTER TABLE `biaoye`.`customer` 
CHANGE COLUMN `nick` `nick` VARCHAR(100) NULL DEFAULT '' ,
CHANGE COLUMN `headimgurl` `headimgurl` TINYTEXT NULL ,
CHANGE COLUMN `invite_code` `invite_code` VARCHAR(45) NULL DEFAULT '' COMMENT '邀请码' ;




