ALTER TABLE `bd_hacademico`.`disciplina` 
DROP COLUMN `ementa`,
CHANGE COLUMN `chs` `chs` INT(11) NOT NULL ,
CHANGE COLUMN `chs_ead` `chs_ead` INT(11) NOT NULL;

ALTER TABLE `bd_hacademico`.`grade` 
ADD COLUMN `ementa` TEXT NULL DEFAULT NULL AFTER `modulo`,
ADD COLUMN `cod_sigaa` VARCHAR(45) NULL DEFAULT NULL AFTER `ementa`;
