ALTER TABLE `bd_hacademico`.`disciplina` 
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ,
ADD COLUMN `cod_sigaa` VARCHAR(100) NULL DEFAULT NULL AFTER `ementa`,
CHANGE COLUMN `chs` `chs` INT(11) NOT NULL ,
CHANGE COLUMN `chs_ead` `chs_ead` INT(11) NOT NULL 
